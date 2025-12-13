<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index()
    {
        return Announcement::latest()->get();
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    /**
     * Get vendors list for announcement recipient selection
     */
    public function getVendorsForSelection()
    {
        $vendors = User::select('users.id', 'users.name')
            ->with(['stall:id,vendor_id,table_number', 'stall.section:id,name'])
            ->whereHas('role', function ($query) {
                $query->where('name', 'Vendor');
            })
            ->whereHas('stall') // Only vendors with assigned stalls
            ->active()
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'stall_number' => optional($vendor->stall)->table_number ?? 'N/A',
                    'section' => optional(optional($vendor->stall)->section)->name ?? 'Unassigned',
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json($vendors);
    }

    public function active(Request $request)
    {
        // Get all active announcements
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->get();

        // If user is authenticated and is a vendor, filter announcements appropriately
        $user = $request->user();
        if ($user && $user->isVendor()) {
            // Load relationships
            $user->load('stall', 'section');
            
            $userSection = $user->section;
            // Check if user is in Wet Section (handle variations: "Wet Section", "Wet")
            $isWetSection = $userSection && in_array($userSection->name, ['Wet Section', 'Wet']);
            
            // Get user's stall ID if they have one
            $userStallId = $user->stall ? $user->stall->id : null;
            
            $announcements = $announcements->filter(function($announcement) use ($isWetSection, $userStallId) {
                // If it's a water-related announcement and user is not in Wet Section, exclude it
                // This excludes Dry Section, Semi-Wet, Semi-Dry, Dry Goods vendors
                if ($announcement->related_utility === 'Water' && !$isWetSection) {
                    return false;
                }
                
                // If it's a rent announcement, only show it to the vendor who owns that specific stall
                if ($announcement->related_utility === 'Rent' && $announcement->related_stall_id) {
                    return $announcement->related_stall_id == $userStallId;
                }
                
                return true;
            });
        }

        // Exclude dismissed announcements if user is authenticated
        if ($user) {
            $dismissedIds = DB::table('dismissed_announcements')
                ->where('user_id', $user->id)
                ->pluck('announcement_id')
                ->toArray();
            
            $announcements = $announcements->reject(function($announcement) use ($dismissedIds) {
                return in_array($announcement->id, $dismissedIds);
            });
        }

        return $announcements->values();
    }

    /**
     * Get all announcements for the current user (including dismissed ones)
     * This is used for the notifications page
     * Optimized for performance with limits and efficient filtering
     */
    public function allForUser(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get dismissed announcement IDs first (single optimized query)
        $dismissedIds = DB::table('dismissed_announcements')
            ->where('user_id', $user->id)
            ->pluck('announcement_id')
            ->toArray();

        // Get recent active announcements with limit (most recent 50 for performance)
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->limit(50)
            ->get();

        // Pre-load user relationships once if vendor
        $userSection = null;
        $userStallId = null;
        $userRole = null;
        
        if ($user->isVendor()) {
            $user->load('stall', 'section');
            $userSection = $user->section;
            $userStallId = $user->stall ? $user->stall->id : null;
        } else {
            $userRole = $user->role ? $user->role->name : null;
        }

        // Filter announcements efficiently
        $announcements = $announcements->filter(function($announcement) use ($user, $userSection, $userStallId, $userRole) {
            if ($user->isVendor()) {
                $isWetSection = $userSection && in_array($userSection->name, ['Wet Section', 'Wet']);
                
                // Filter water-related announcements
                if ($announcement->related_utility === 'Water' && !$isWetSection) {
                    return false;
                }
                
                // Filter rent announcements
                if ($announcement->related_utility === 'Rent' && $announcement->related_stall_id) {
                    return $announcement->related_stall_id == $userStallId;
                }
                
                return true;
            } else {
                // For staff/admin, filter based on recipients
                $recipients = $announcement->recipients ?? [];
                
                // If staff is selected in recipients, show to staff/admin
                if (!empty($recipients['staff'])) {
                    return in_array($userRole, ['Staff', 'Meter Reader Clerk', 'Admin']);
                }
                
                // If no recipients specified, show to all (legacy behavior)
                if (empty($recipients)) {
                    return true;
                }
                
                // If only sections are specified, don't show to staff unless staff is also selected
                return false;
            }
        });

        // Add dismissed flag efficiently
        $announcements = $announcements->map(function($announcement) use ($dismissedIds) {
            $announcement->is_dismissed = in_array($announcement->id, $dismissedIds);
            return $announcement;
        });

        return response()->json($announcements->values());
    }

    /**
     * Mark an announcement as dismissed for the current user
     */
    public function dismiss(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if already dismissed
        $exists = DB::table('dismissed_announcements')
            ->where('user_id', $user->id)
            ->where('announcement_id', $announcement->id)
            ->exists();

        if (!$exists) {
            DB::table('dismissed_announcements')->insert([
                'user_id' => $user->id,
                'announcement_id' => $announcement->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Announcement dismissed successfully']);
    }

    public function store(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'recipients' => 'nullable|array',
            'recipients.staff' => 'boolean',
            'recipients.meter_reader_clerk' => 'boolean',
            'recipients.all_sections' => 'boolean',
            'recipients.sections' => 'nullable|array',
            'recipients.vendor_ids' => 'nullable|array',
            'recipients.vendor_ids.*' => 'integer|exists:users,id',
        ]);

        // Publish Immediately feature removed - always create as inactive (draft)
        $validated['is_active'] = false;

        $announcement = Announcement::create($validated);

        // Announcements are always created as drafts - no automatic sending
        // Admin must manually activate them later

        return response()->json($announcement, 201);
    }

    public function update(Request $request, Announcement $announcement, SmsService $smsService)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_active' => 'boolean', // Still allow updating is_active manually (for activation)
            'recipients' => 'nullable|array',
            'recipients.staff' => 'boolean',
            'recipients.meter_reader_clerk' => 'boolean',
            'recipients.all_sections' => 'boolean',
            'recipients.sections' => 'nullable|array',
            'recipients.vendor_ids' => 'nullable|array',
            'recipients.vendor_ids.*' => 'integer|exists:users,id',
        ]);

        $wasActive = $announcement->is_active;
        $announcement->update($validated);

        // Send SMS and create in-app notifications if announcement was just activated
        // When is_active changes from false to true, send notifications and SMS
        // This allows manual activation via the "Send" button
        if ($announcement->is_active && !$wasActive) {
            $this->sendAnnouncementSms($announcement, $smsService);
            $this->createAnnouncementNotifications($announcement);
        }

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }

    /**
     * Send announcement SMS to selected recipients
     */
    private function sendAnnouncementSms(Announcement $announcement, SmsService $smsService)
    {
        try {
            $recipients = collect();
            $recipientSettings = $announcement->recipients ?? [];

            // Log for debugging template announcements
            Log::info("Sending SMS for announcement", [
                'announcement_id' => $announcement->id,
                'announcement_type' => $announcement->announcement_type,
                'related_utility' => $announcement->related_utility,
                'has_recipient_settings' => !empty($recipientSettings),
                'recipient_settings' => $recipientSettings
            ]);

            // Get specific vendors if selected
            if (!empty($recipientSettings['vendor_ids']) && is_array($recipientSettings['vendor_ids'])) {
                $specificVendors = User::vendors()
                    ->active()
                    ->whereNotNull('contact_number')
                    ->whereIn('id', $recipientSettings['vendor_ids'])
                    ->get();
                $recipients = $recipients->merge($specificVendors);
            }

            // Get vendors based on section settings (only if specific vendors not selected or in addition to them)
            if (!empty($recipientSettings['all_sections']) || !empty($recipientSettings['sections'])) {
                $vendorsQuery = User::vendors()
                    ->active()
                    ->whereNotNull('contact_number');

                // Exclude specific vendors if they were already added
                if (!empty($recipientSettings['vendor_ids']) && is_array($recipientSettings['vendor_ids'])) {
                    $vendorsQuery->whereNotIn('id', $recipientSettings['vendor_ids']);
                }

                // If "All Sections" is selected, get all vendors
                // Otherwise, filter by specific sections
                if (empty($recipientSettings['all_sections']) && !empty($recipientSettings['sections']) && is_array($recipientSettings['sections'])) {
                    Log::info("Filtering vendors by sections", [
                        'sections' => $recipientSettings['sections'],
                        'announcement_id' => $announcement->id
                    ]);
                    
                    // Use stall.section relationship since vendors are related to sections through stalls
                    $vendorsQuery->whereHas('stall.section', function($query) use ($recipientSettings) {
                        $query->whereIn('name', $recipientSettings['sections']);
                    });
                }

                $vendors = $vendorsQuery->get();
                Log::info("Found vendors for selected sections", [
                    'vendor_count' => $vendors->count(),
                    'sections' => $recipientSettings['sections'] ?? 'all_sections',
                    'announcement_id' => $announcement->id
                ]);
                
                $recipients = $recipients->merge($vendors);
            }

            // Legacy support: if no recipients specified, use old logic based on announcement type
            // This handles template-generated announcements that don't have recipients set
            $hasAnyRecipientSettings = !empty($recipientSettings) && (
                !empty($recipientSettings['vendor_ids']) ||
                !empty($recipientSettings['all_sections']) ||
                !empty($recipientSettings['sections']) ||
                !empty($recipientSettings['staff']) ||
                !empty($recipientSettings['meter_reader_clerk'])
            );
            
            if ($recipients->isEmpty() && !$hasAnyRecipientSettings) {
                Log::info("Using legacy recipient logic for template announcement", [
                    'announcement_id' => $announcement->id,
                    'related_utility' => $announcement->related_utility,
                    'related_stall_id' => $announcement->related_stall_id
                ]);
                
                // For rent announcements, only send to the specific vendor who owns the stall
                if ($announcement->related_utility === 'Rent' && $announcement->related_stall_id) {
                    $stall = \App\Models\Stall::with('vendor')->find($announcement->related_stall_id);
                    if ($stall && $stall->vendor && $stall->vendor->contact_number) {
                        $recipients = collect([$stall->vendor]);
                        Log::info("Found rental announcement vendor", ['vendor_id' => $stall->vendor->id]);
                    }
                } else {
                    // Get all vendors with contact numbers
                    $vendorsQuery = User::vendors()
                        ->active()
                        ->whereNotNull('contact_number');

                    // Filter vendors by section if this is a water-related announcement
                    if ($announcement->related_utility === 'Water') {
                        // Only send to vendors in Wet Section (exclude Dry Section, Semi-Wet, Semi-Dry, Dry Goods)
                        $vendorsQuery->whereHas('stall.section', function($query) {
                            $query->whereIn('name', ['Wet Section', 'Wet']);
                        });
                    }

                    $recipients = $vendorsQuery->get();
                    Log::info("Found vendors for legacy logic", [
                        'utility' => $announcement->related_utility,
                        'vendor_count' => $recipients->count()
                    ]);
                }
            }

            // Add staff if selected OR if no recipients were specified (legacy behavior for template announcements)
            if (!empty($recipientSettings['staff']) || !$hasAnyRecipientSettings) {
                $staff = User::whereHas('role', function($query) {
                    $query->where('name', 'Staff');
                })
                ->active()
                ->whereNotNull('contact_number')
                ->get();

                $recipients = $recipients->merge($staff);
            }

            // Add Meter Reader Clerk if selected
            if (!empty($recipientSettings['meter_reader_clerk'])) {
                $meterReaderClerks = User::whereHas('role', function($query) {
                    $query->where('name', 'Meter Reader Clerk');
                })
                ->active()
                ->whereNotNull('contact_number')
                ->get();

                $recipients = $recipients->merge($meterReaderClerks);
            }
            
            Log::info("Sending announcement SMS to {$recipients->count()} recipients");

            $message = "ANNOUNCEMENT: {$announcement->title}\n\n{$announcement->content}\n\n- Virac Public Market";

            $successCount = 0;
            $failCount = 0;

            foreach ($recipients as $user) {
                $contactNumber = $user->getSemaphoreReadyContactNumber();
                if ($contactNumber) {
                    $result = $smsService->send($contactNumber, $message);
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failCount++;
                        Log::warning("Failed to send announcement SMS to user {$user->id}: {$result['message']}");
                    }
                } else {
                    $failCount++;
                    Log::warning("Skipping user {$user->id} - no valid contact number");
                }
            }

            Log::info("Announcement SMS sent: {$successCount} successful, {$failCount} failed");
        } catch (\Exception $e) {
            Log::error("Error sending announcement SMS: " . $e->getMessage());
        }
    }

    /**
     * Create in-app notifications for selected recipients when announcement is created/activated
     */
    private function createAnnouncementNotifications(Announcement $announcement)
    {
        try {
            $recipients = collect();
            $recipientSettings = $announcement->recipients ?? [];

            // Get specific vendors if selected
            if (!empty($recipientSettings['vendor_ids']) && is_array($recipientSettings['vendor_ids'])) {
                $specificVendors = User::vendors()
                    ->active()
                    ->whereIn('id', $recipientSettings['vendor_ids'])
                    ->get();
                $recipients = $recipients->merge($specificVendors);
            }

            // Get vendors based on section settings (only if specific vendors not selected or in addition to them)
            if (!empty($recipientSettings['all_sections']) || !empty($recipientSettings['sections'])) {
                $vendorsQuery = User::vendors()
                    ->active();

                // Exclude specific vendors if they were already added
                if (!empty($recipientSettings['vendor_ids']) && is_array($recipientSettings['vendor_ids'])) {
                    $vendorsQuery->whereNotIn('id', $recipientSettings['vendor_ids']);
                }

                // If "All Sections" is selected, get all vendors
                // Otherwise, filter by specific sections
                if (empty($recipientSettings['all_sections']) && !empty($recipientSettings['sections']) && is_array($recipientSettings['sections'])) {
                    // Use stall.section relationship since vendors are related to sections through stalls
                    $vendorsQuery->whereHas('stall.section', function($query) use ($recipientSettings) {
                        $query->whereIn('name', $recipientSettings['sections']);
                    });
                }

                $vendors = $vendorsQuery->get();
                $recipients = $recipients->merge($vendors);
            }

            // Legacy support: if no recipients specified, use old logic based on announcement type
            // This handles template-generated announcements that don't have recipients set
            $hasRecipientSettings = !empty($recipientSettings) && (
                !empty($recipientSettings['vendor_ids']) ||
                !empty($recipientSettings['all_sections']) ||
                !empty($recipientSettings['sections']) ||
                !empty($recipientSettings['staff']) ||
                !empty($recipientSettings['meter_reader_clerk'])
            );
            
            if ($recipients->isEmpty() && !$hasRecipientSettings) {
                // For rent announcements, only send to the specific vendor who owns the stall
                if ($announcement->related_utility === 'Rent' && $announcement->related_stall_id) {
                    $stall = \App\Models\Stall::with('vendor')->find($announcement->related_stall_id);
                    if ($stall && $stall->vendor) {
                        $recipients = collect([$stall->vendor]);
                    }
                } else {
                    // Get all vendors
                    $vendorsQuery = User::vendors()
                        ->active();

                    // Filter vendors by section if this is a water-related announcement
                    if ($announcement->related_utility === 'Water') {
                        // Only send to vendors in Wet Section (exclude Dry Section, Semi-Wet, Semi-Dry, Dry Goods)
                        $vendorsQuery->whereHas('stall.section', function($query) {
                            $query->whereIn('name', ['Wet Section', 'Wet']);
                        });
                    }

                    $recipients = $vendorsQuery->get();
                }
            }

            // Add staff if selected OR if no recipients were specified (legacy behavior for template announcements)
            if (!empty($recipientSettings['staff']) || !$hasRecipientSettings) {
                $staff = User::whereHas('role', function($query) {
                    $query->where('name', 'Staff');
                })
                ->active()
                ->get();

                $recipients = $recipients->merge($staff);
            }

            // Add Meter Reader Clerk if selected
            if (!empty($recipientSettings['meter_reader_clerk'])) {
                $meterReaderClerks = User::whereHas('role', function($query) {
                    $query->where('name', 'Meter Reader Clerk');
                })
                ->active()
                ->get();

                $recipients = $recipients->merge($meterReaderClerks);
            }
            
            Log::info("Creating in-app notifications for announcement to {$recipients->count()} recipients");

            $adminUser = User::whereHas('role', function($query) {
                $query->where('name', 'Admin');
            })->first();

            $senderId = $adminUser ? $adminUser->id : null;
            $now = now();

            // Prepare notification data
            $notificationData = [];
            foreach ($recipients as $user) {
                $notificationData[] = [
                    'recipient_id' => $user->id,
                    'sender_id' => $senderId,
                    'channel' => 'in_app',
                    'title' => $announcement->title,
                    'message' => json_encode([
                        'text' => $announcement->content,
                        'type' => 'announcement',
                        'announcement_id' => $announcement->id,
                    ]),
                    'status' => 'sent',
                    'sent_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insert notifications in batches for better performance
            if (!empty($notificationData)) {
                DB::table('notifications')->insert($notificationData);
                Log::info("Created {$recipients->count()} in-app notifications for announcement");
            }
        } catch (\Exception $e) {
            Log::error("Error creating announcement notifications: " . $e->getMessage());
        }
    }
}
