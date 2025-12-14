<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReadingEditRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogger;

class ReadingEditRequestController extends Controller
{
    public function index(?Request $request = null)
    {
        // Fetch all requests with relationships, ordered by the newest first, and paginate them.
        $requests = ReadingEditRequest::with(['utilityReading.stall', 'requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(20);

        // Transform the data to include stall information and ensure processed_at is included
        $requests->getCollection()->transform(function ($request) {
            $request->stall_number = $request->utilityReading && $request->utilityReading->stall 
                ? $request->utilityReading->stall->table_number 
                : 'N/A';
            return $request;
        });

        return response()->json($requests);
    }

    /**
     * Update the status of the specified resource in storage (Approve/Reject).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReadingEditRequest  $readingEditRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, ReadingEditRequest $readingEditRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $readingEditRequest->status = $validated['status'];
        $readingEditRequest->approved_by = Auth::id(); // Set the approver ID
        $readingEditRequest->processed_at = now(); // Set the processed date
        $readingEditRequest->save();

        try {
            $meterReader = User::find($readingEditRequest->requested_by);
            if ($meterReader) {
                $stallNumber = $readingEditRequest->utilityReading->stall->table_number;
                $status = $validated['status'];
                $notificationTitle = "Edit Request {$status}";
                $notificationMessage = "Your edit request for stall {$stallNumber} has been {$status}.";

                // In-App Notification for Meter Reader
                DB::table('notifications')->insert([
                    'recipient_id' => $meterReader->id,
                    'sender_id' => Auth::id(),
                    'channel' => 'in_app',
                    'title' => $notificationTitle,
                    'message' => json_encode(['text' => $notificationMessage, 'request_id' => $readingEditRequest->id]),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // SMS Notification for Meter Reader
                $meterReaderContact = $meterReader->getSemaphoreReadyContactNumber();
                if ($meterReaderContact) {
                    $smsMessage = "RUMSYS Update: Your edit request for stall {$stallNumber} has been {$status}.";
                    $smsService = new SmsService();
                    $smsService->send($meterReaderContact, $smsMessage);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for edit request status update: " . $e->getMessage());
        }

        AuditLogger::log(
            'Updated Edit Request Status',
            'Utility Readings',
            'Success',
            ['request_id' => $readingEditRequest->id, 'status' => $validated['status'], 'stall_number' => $readingEditRequest->utilityReading->stall->table_number]
        );

        return response()->json([
            'message' => 'Request status updated successfully.',
            'request' => [
                'id' => $readingEditRequest->id,
                'status' => $readingEditRequest->status,
                'processed_at' => $readingEditRequest->processed_at,
            ]
        ]);
    }
    

// In app/Http/Controllers/Api/ReadingEditRequestController.php

public function store(Request $request)
{
    $request->validate([
        'utility_reading_id' => 'required|exists:utility_readings,id',
        'reason' => 'required|string|max:255',
    ]);

    $editRequest = ReadingEditRequest::create([
        'reading_id' => $request->utility_reading_id,
        'requested_by' => Auth::id(),
        'reason' => $request->reason,
        'status' => 'pending',
    ]);

    // Find the admin user to be the recipient
    $admin = User::whereHas('role', function ($query) {
        $query->where('name', 'Admin');
    })->first();

    if ($admin) {
        // This code now correctly inserts only ONE notification record.
        DB::table('notifications')->insert([
            'recipient_id' => $admin->id,
            'sender_id' => Auth::id(),
            'channel' => 'in_app', // Set channel to 'in_app' to distinguish from SMS
            'title' => 'New Meter Reading Edit Request',
            'message' => json_encode([
                'request_id' => $editRequest->id,
                'reason' => $editRequest->reason,
                'text' => 'New edit request from ' . Auth::user()->name
            ]),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $adminContact = $admin->getSemaphoreReadyContactNumber();
            if ($adminContact) {
                $senderName = Auth::user()->name;
                $stallNumber = $editRequest->utilityReading->stall->table_number;
                $smsMessage = "RUMSYS: New edit request from {$senderName} for stall {$stallNumber}. Reason: {$editRequest->reason}";
                
                $smsService = new SmsService();
                $smsService->send($adminContact, $smsMessage);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send SMS for new edit request: ' . $e->getMessage());
        }
    }

    AuditLogger::log(
        'Requested Reading Edit',
        'Utility Readings',
        'Success',
        ['request_id' => $editRequest->id, 'reading_id' => $request->utility_reading_id, 'reason' => $request->reason]
    );

    return response()->json($editRequest, 201);
}
}