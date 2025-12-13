<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User; 
use App\Services\SmsService;
use App\Services\AuditLogger; 

class NotificationTemplateController extends Controller
{
    /**
     * Get the path to the default message templates JSON file.
     *
     * @return string
     */
    private function getDefaultsPath(): string
    {
        return config_path('message_templates.json');
    }

    /**
     * Display the current notification templates, merging DB overrides with JSON defaults.
     */
    public function index()
    {
        $path = $this->getDefaultsPath();

        if (!File::exists($path)) {
            return response()->json(['message' => 'Default templates file not found.'], 404);
        }

        // Step 1: Load the default templates from the JSON file
        $defaultTemplates = json_decode(File::get($path), true);

        // Step 2: Load the customized templates from the database
        $customTemplatesFromDb = DB::table('sms_notification_settings')->get();

        // Step 3: Merge the two, with database values overwriting the defaults
        $finalTemplates = $this->mergeTemplates($defaultTemplates, $customTemplatesFromDb);

        return response()->json($finalTemplates);
    }

    /**
     * Update the notification templates in the database.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'bill_statement.wet_section' => 'required|string',
            'bill_statement.dry_section' => 'required|string',
            'payment_reminder.template' => 'required|string',
            'overdue_alert.template' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                // We will flatten the nested array to easily loop and save to the database
                $templatesToSave = [
                    'bill_statement_wet_section' => $validatedData['bill_statement']['wet_section'],
                    'bill_statement_dry_section' => $validatedData['bill_statement']['dry_section'],
                    'payment_reminder_template' => $validatedData['payment_reminder']['template'],
                    'overdue_alert_template' => $validatedData['overdue_alert']['template'],
                ];

                $changes = [];
                foreach ($templatesToSave as $name => $message) {
                    // Get old value before update
                    $oldTemplate = DB::table('sms_notification_settings')
                        ->where('name', $name)
                        ->value('message_template');
                    
                    // Use updateOrInsert:
                    // - If a template with this 'name' exists, it will be updated.
                    // - If not, a new one will be inserted.
                    DB::table('sms_notification_settings')->updateOrInsert(
                        ['name' => $name], // Condition to find the record
                        [
                            'message_template' => $message, // Values to update or insert
                            'updated_at' => now(),
                        ]
                    );
                    
                    if ($oldTemplate && $oldTemplate != $message) {
                        $changes[$name] = [
                            'old' => $oldTemplate,
                            'new' => $message
                        ];
                    } elseif (!$oldTemplate) {
                        $changes[$name] = ['action' => 'created', 'template' => $message];
                    }
                }
                
                if (!empty($changes)) {
                    AuditLogger::log(
                        'Updated SMS Notification Templates',
                        'Notification Templates',
                        'Success',
                        ['templates_updated' => $changes]
                    );
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to save templates to the database.'], 500);
        }

        return response()->json(['message' => 'Notification templates updated successfully!']);
    }

    public function getCredits(SmsService $smsService)
    {
        $result = $smsService->getCredits();
        return response()->json($result);
    }

    public function sendTestSms(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'template_name' => 'required|string', // e.g., 'overdue_alert_template'
        ]);

        $vendor = User::with(['stall', 'billings' => function ($query) {
            $query->where('status', 'unpaid')->latest('period_end');
        }])->find($validated['user_id']);

        if (!$vendor || !$vendor->stall) {
            return response()->json(['message' => 'Vendor or stall not found.'], 404);
        }

        $recipientNumber = $vendor->getSemaphoreReadyContactNumber();
        if (!$recipientNumber) {
            return response()->json(['message' => 'Vendor does not have a valid contact number.'], 400);
        }

        $templateRecord = DB::table('sms_notification_settings')
            ->where('name', $validated['template_name'])
            ->first();

        if (!$templateRecord) {
            return response()->json(['message' => 'SMS template not found.'], 404);
        }

        $messageTemplate = $templateRecord->message_template;

         $normalizedTemplate = preg_replace_callback(
        '/{{\s*(.*?)\s*}}/',
        function ($matches) {
            return '{{' . $matches[1] . '}}';
        },
        $messageTemplate
        );


        // --- DYNAMIC DATA REPLACEMENT ---
        // This logic can be expanded based on the template's needs
        $unpaidBills = $vendor->billings;
        $totalDue = $unpaidBills->sum('amount');
        $unpaidItems = $unpaidBills->pluck('utility_type')->implode(', ');
        $latestBill = $unpaidBills->first();

        $replacements = [
            '{{vendor_name}}'        => $vendor->name,
            '{{stall_number}}'       => $vendor->stall->table_number,
            '{{total_due}}'          => '₱' . number_format($totalDue, 2),
            '{{due_date}}'           => $latestBill ? $latestBill->due_date->format('M d, Y') : 'N/A',
            '{{unpaid_items}}'       => $unpaidItems,
            '{{overdue_items}}'      => $unpaidItems,
            '{{new_total_due}}'      => '₱' . number_format($totalDue, 2),
            '{{disconnection_date}}' => $latestBill && $latestBill->disconnection_date ? $latestBill->disconnection_date->format('M d, Y') : 'N/A',
            '{{rent_amount}}'        => '₱' . number_format($unpaidBills->where('utility_type', 'Rent')->sum('amount'), 2),
            '{{water_amount}}'       => '₱' . number_format($unpaidBills->where('utility_type', 'Water')->sum('amount'), 2),
            '{{electricity_amount}}' => '₱' . number_format($unpaidBills->where('utility_type', 'Electricity')->sum('amount'), 2),
        ];

        $finalMessage = str_replace(array_keys($replacements), array_values($replacements), $normalizedTemplate);

        $result = $smsService->send($recipientNumber, $finalMessage);

        if ($result['success']) {
            return response()->json(['message' => 'Test SMS sent successfully!']);
        } else {
            return response()->json(['message' => 'Failed to send SMS.', 'details' => $result], 500);
        }
    }
    /**
     * Helper function to merge database customizations over JSON defaults.
     *
     * @param array $defaults The templates from the JSON file.
     * @param \Illuminate\Support\Collection $customs The templates from the database.
     * @return array The final merged templates.
     */
    private function mergeTemplates(array $defaults, $customs): array
    {
        foreach ($customs as $custom) {
            // This maps the database 'name' column to the nested array structure
            // for the frontend.
            switch ($custom->name) {
                case 'bill_statement_wet_section':
                    $defaults['bill_statement']['wet_section'] = $custom->message_template;
                    break;
                case 'bill_statement_dry_section':
                    $defaults['bill_statement']['dry_section'] = $custom->message_template;
                    break;
                case 'payment_reminder_template':
                    $defaults['payment_reminder']['template'] = $custom->message_template;
                    break;
                case 'overdue_alert_template':
                    $defaults['overdue_alert']['template'] = $custom->message_template;
                    break;
            }
        }
        return $defaults;
    }

    /**
     * Get all sent SMS messages (bill statements, payment reminders, overdue alerts, effectivity date changes)
     */
    public function getSentMessages(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 50); // Increased default for better scrolling
            
            // Get filters from request
            $messageType = $request->input('type');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $recipientSearch = $request->input('recipient');
            
            // Filter by message type based on title
            $messageTypes = [
                'Bill Statement',
                'Payment Reminder',
                'Overdue Bill Alert',
                'Rate Change Notification',
                'Schedule Change Notification',
                'Billing Setting Change Notification',
                'Rental Rate Change Notification',
                'Policy Change Notification',
                'Announcement',
                'SMS Notification',
            ];
            
            // Log for debugging
            \Log::info('getSentMessages called', [
                'page' => $page,
                'filters' => [
                    'type' => $messageType,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'recipient' => $recipientSearch
                ]
            ]);
        
        // Build base query - if recipient filter is needed, join users from the start
        if ($recipientSearch) {
            $baseQuery = DB::table('notifications as n')
                ->join('users as recipient', 'n.recipient_id', '=', 'recipient.id')
                ->where('n.channel', 'sms')
                ->where('n.status', 'sent')
                ->whereIn('n.title', $messageTypes)
                ->where(function($query) use ($recipientSearch) {
                    $query->where('recipient.name', 'like', "%{$recipientSearch}%")
                          ->orWhere('recipient.contact_number', 'like', "%{$recipientSearch}%");
                });
        } else {
            $baseQuery = DB::table('notifications as n')
                ->where('n.channel', 'sms')
                ->where('n.status', 'sent')
                ->whereIn('n.title', $messageTypes);
        }
        
        // Apply filters
        if ($messageType) {
            $baseQuery->where('n.title', $messageType);
        }
        
        if ($dateFrom) {
            $baseQuery->where(function($query) use ($dateFrom) {
                $query->whereDate('n.sent_at', '>=', $dateFrom)
                      ->orWhere(function($q) use ($dateFrom) {
                          $q->whereNull('n.sent_at')
                            ->whereDate('n.created_at', '>=', $dateFrom);
                      });
            });
        }
        
        if ($dateTo) {
            $baseQuery->where(function($query) use ($dateTo) {
                $query->whereDate('n.sent_at', '<=', $dateTo)
                      ->orWhere(function($q) use ($dateTo) {
                          $q->whereNull('n.sent_at')
                            ->whereDate('n.created_at', '<=', $dateTo);
                      });
            });
        }
        
        // Get notification IDs with filters applied
        $notificationIds = $baseQuery
            ->select('n.id')
            ->orderBy('n.sent_at', 'desc')
            ->orderBy('n.created_at', 'desc')
            ->pluck('id');
        
        $total = $notificationIds->count();
        
        if ($total === 0) {
            return response()->json([
                'data' => [],
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => 0,
                'last_page' => 0,
            ]);
        }
        
        // Get paginated IDs
        $paginatedIds = $notificationIds->slice(($page - 1) * $perPage, $perPage)->values();
        
        // Now join with users only for the paginated results
        $messages = DB::table('notifications as n')
            ->join('users as recipient', 'n.recipient_id', '=', 'recipient.id')
            ->leftJoin('users as sender', 'n.sender_id', '=', 'sender.id')
            ->whereIn('n.id', $paginatedIds)
            ->select(
                'n.id',
                'n.title',
                'n.message',
                'n.sent_at',
                'n.created_at',
                'recipient.name as recipient_name',
                'recipient.contact_number as recipient_contact',
                'sender.name as sender_name'
            )
            ->orderBy('n.sent_at', 'desc')
            ->orderBy('n.created_at', 'desc')
            ->get();
        
        // Parse message JSON and format data
        $formattedMessages = $messages->map(function ($message) {
            $messageData = json_decode($message->message, true);
            $messageText = is_array($messageData) && isset($messageData['text']) 
                ? $messageData['text'] 
                : (is_string($message->message) ? $message->message : '');
            
            // Truncate long messages for display
            $displayMessage = strlen($messageText) > 150 
                ? substr($messageText, 0, 150) . '...' 
                : $messageText;
            
            return [
                'id' => $message->id,
                'date_time' => $message->sent_at ?? $message->created_at,
                'recipient_name' => $message->recipient_name ?? 'N/A',
                'recipient_contact' => $message->recipient_contact ?? 'N/A',
                'type' => $message->title,
                'message' => $messageText,
                'display_message' => $displayMessage,
                'sender_name' => $message->sender_name ?? 'System',
            ];
        });
        
            \Log::info('getSentMessages result', [
                'total' => $total,
                'messages_count' => $formattedMessages->count(),
                'page' => $page
            ]);
            
            return response()->json([
                'data' => $formattedMessages,
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getSentMessages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch sent messages',
                'message' => $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Debug endpoint to check SMS notifications in database
     */
    public function debugSentMessages(Request $request)
    {
        // Check total SMS notifications
        $totalSms = DB::table('notifications')
            ->where('channel', 'sms')
            ->count();
        
        // Check sent SMS notifications
        $sentSms = DB::table('notifications')
            ->where('channel', 'sms')
            ->where('status', 'sent')
            ->count();
        
        // Check by title
        $byTitle = DB::table('notifications')
            ->where('channel', 'sms')
            ->where('status', 'sent')
            ->select('title', DB::raw('count(*) as count'))
            ->groupBy('title')
            ->get();
        
        // Get recent SMS notifications (last 10)
        $recent = DB::table('notifications as n')
            ->join('users as recipient', 'n.recipient_id', '=', 'recipient.id')
            ->where('n.channel', 'sms')
            ->where('n.status', 'sent')
            ->select(
                'n.id',
                'n.title',
                'n.sent_at',
                'n.created_at',
                'recipient.name as recipient_name'
            )
            ->orderBy('n.sent_at', 'desc')
            ->orderBy('n.created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Check if notifications table exists and has correct structure
        $tableExists = DB::getSchemaBuilder()->hasTable('notifications');
        $hasChannelColumn = $tableExists ? DB::getSchemaBuilder()->hasColumn('notifications', 'channel') : false;
        $hasStatusColumn = $tableExists ? DB::getSchemaBuilder()->hasColumn('notifications', 'status') : false;
        
        return response()->json([
            'debug_info' => [
                'table_exists' => $tableExists,
                'has_channel_column' => $hasChannelColumn,
                'has_status_column' => $hasStatusColumn,
                'total_sms_notifications' => $totalSms,
                'sent_sms_notifications' => $sentSms,
                'by_title' => $byTitle,
                'recent_messages' => $recent,
            ]
        ]);
    }

    /**
     * Test endpoint to manually create a test SMS notification entry
     * This helps verify that storage and retrieval are working
     */
    public function testSmsStorage(Request $request)
    {
        try {
            $adminUser = User::whereHas('role', function($query) {
                $query->where('name', 'Admin');
            })->first();
            
            $testMessage = "TEST SMS: This is a test message to verify SMS storage is working. Sent at " . now()->toDateTimeString();
            
            $notificationId = DB::table('notifications')->insertGetId([
                'recipient_id' => auth()->id() ?? 1,
                'sender_id' => $adminUser ? $adminUser->id : null,
                'channel' => 'sms',
                'title' => 'Test SMS',
                'message' => json_encode([
                    'text' => $testMessage,
                    'type' => 'test',
                ]),
                'status' => 'sent',
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Verify it can be retrieved
            $retrieved = DB::table('notifications')
                ->where('id', $notificationId)
                ->where('channel', 'sms')
                ->where('status', 'sent')
                ->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Test SMS notification created and verified',
                'notification_id' => $notificationId,
                'retrieved' => $retrieved ? 'Yes' : 'No',
                'data' => $retrieved
            ]);
        } catch (\Exception $e) {
            \Log::error('Test SMS storage failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}