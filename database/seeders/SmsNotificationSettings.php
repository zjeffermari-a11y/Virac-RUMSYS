<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmsNotificationSettingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('sms_notification_settings')->insert([
            [
                'id' => 1,
                'name' => 'bill_statement_wet_section',
                'message_template' => 'Bill Statement:\n\nMayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. \n\nAn kabuuan na babayadan: P{{total_due}}. Salamat!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
            [
                'id' => 2,
                'name' => 'bill_statement_dry_section',
                'message_template' => 'Bill Statement:\n\nMayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. \n\nAn kabuuan na babayadan: P{{total_due}}. Salamat!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
            [
                'id' => 3,
                'name' => 'payment_reminder_template',
                'message_template' => 'Mayad na aga, {{vendor_name}}. Reminder: Ini an saimong mga bayadan na dai pa nababayadan: \n\n{{ upcoming_bill_details }}\n\nSalamat!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
            [
                'id' => 4,
                'name' => 'overdue_alert_template',
                'message_template' => 'Mayad na aga, {{ vendor_name }}. OVERDUE: An saimong bayadan para sa {{overdue_items}} lampas na sa due date. \n\nAn bagong total: P{{new_total_due}}. Salamat!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
        ]);
    }
}