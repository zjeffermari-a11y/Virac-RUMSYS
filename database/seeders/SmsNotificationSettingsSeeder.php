<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmsNotificationSettingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('sms_notification_settings')->upsert([
            [
                'id' => 1,
                'name' => 'bill_statement_wet_section',
                'message_template' => 'Virac Public Market - Bill Statement\n\nKumusta, {{ vendor_name }}!\nStall: {{stall_number}}\nBill Month: {{bill_month}}\n\nRENT:\n{{rent_details}}\n\nWATER:\n{{water_details}}\n\nELECTRICITY:\n{{electricity_details}}\n\nTOTAL AMOUNT: P{{total_due}}\n\nPara sa mas malinaw na detalye, bisitahin ang: {{website_url}}\nO pumunta sa Market Operations Office.\n\nSalamat po!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
            [
                'id' => 2,
                'name' => 'bill_statement_dry_section',
                'message_template' => 'Virac Public Market - Bill Statement\n\nKumusta, {{ vendor_name }}!\nStall: {{stall_number}}\nBill Month: {{bill_month}}\n\nRENT:\n{{rent_details}}\n\nWATER:\n{{water_details}}\n\nELECTRICITY:\n{{electricity_details}}\n\nTOTAL AMOUNT: P{{total_due}}\n\nPara sa mas malinaw na detalye, bisitahin ang: {{website_url}}\nO pumunta sa Market Operations Office.\n\nSalamat po!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
            [
                'id' => 3,
                'name' => 'payment_reminder_template',
                'message_template' => 'Virac Public Market - Payment Reminder\n\nKumusta, {{ vendor_name }}!\nStall: {{stall_number}}\n\nREMINDER: Mayroon po kayong mga bayadan na malapit nang mag-due:\n\n{{ upcoming_bill_details }}\n\nTotal: P{{total_due}}\nEarliest Due: {{due_date}}\n\nPakisettle po bago mag-due date. Salamat!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
            [
                'id' => 4,
                'name' => 'overdue_alert_template',
                'message_template' => 'Virac Public Market - OVERDUE ALERT\n\nKumusta, {{ vendor_name }}!\nStall: {{stall_number}}\n\n⚠️ OVERDUE BILLS:\n{{ overdue_bill_details }}\n\nTOTAL DUE (with penalties): P{{new_total_due}}\n\nAng inyong bayadan para sa {{overdue_items}} ay lampas na sa due date. Pakisettle po agad para maiwasan ang disconnection.\n\nSalamat po!',
                'enabled' => 1,
                'created_at' => '2025-08-28 09:33:07',
                'updated_at' => '2025-10-16 00:54:19',
            ],
        ], ['id'], ['name', 'message_template', 'enabled', 'updated_at']);
    }
}