<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User; 
use App\Services\SmsService; 

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

                foreach ($templatesToSave as $name => $message) {
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
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to save templates to the database.'], 500);
        }

        return response()->json(['message' => 'Notification templates updated successfully!']);
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
}