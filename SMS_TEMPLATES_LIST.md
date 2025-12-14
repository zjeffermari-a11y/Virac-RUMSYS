# SMS Templates List

This document lists all SMS templates used in the Virac-RUMSYS system for sending notifications to vendors.

## Template Structure

SMS templates are stored in two places:
1. **Default Templates**: `config/message_templates.json` (default fallback)
2. **Custom Templates**: `sms_notification_settings` database table (can override defaults)

The system merges both, with database values taking precedence over defaults.

---

## 1. Bill Statement Template

**Template Name:** `bill_statement`

**Used By:**
- `SendBillingStatements` command (monthly billing statements)
- Scheduled to send on a specific day of the month

**Variants:**
- **Wet Section**: For vendors in sections with "wet" in the name
- **Dry Section**: For vendors in sections without "wet" in the name

**Default Templates:**

**Wet Section:**
```
Your bill statement: Rent - ₱{{rent_amount}}, Water - ₱{{water_amount}}, Electricity - ₱{{electricity_amount}}. Total due: ₱{{total_due}} on {{due_date}}.
```

**Dry Section:**
```
Your bill statement: Rent - ₱{{rent_amount}}, Water - ₱{{water_amount}}. Total due: ₱{{total_due}} on {{due_date}}.
```

**Available Variables:**
- `{{vendor_name}}` - Vendor's name
- `{{stall_number}}` - Stall/table number
- `{{rent_amount}}` - Rent amount due
- `{{water_amount}}` - Water amount due
- `{{electricity_amount}}` - Electricity amount due (wet sections only)
- `{{total_due}}` - Total amount due
- `{{due_date}}` - Due date for payment
- `{{bill_details}}` - Formatted list of bills
- `{{disconnection_date}}` - Disconnection date (if applicable)

**Database Override Names:**
- `bill_statement_wet_section`
- `bill_statement_dry_section`

---

## 2. Payment Reminder Template

**Template Name:** `payment_reminder`

**Used By:**
- `SendPaymentReminders` command
- Sent to vendors with upcoming payments

**Default Template:**
```
Reminder: The following payments are due today: {{unpaid_items}}. Thank you.
```

**Available Variables:**
- `{{vendor_name}}` - Vendor's name
- `{{stall_number}}` - Stall/table number
- `{{unpaid_items}}` - List of unpaid items
- `{{upcoming_bill_details}}` - Formatted upcoming bills
- `{{total_due}}` - Total amount due
- `{{due_date}}` - Due date for payment

**Database Override Name:**
- `payment_reminder_template`

---

## 3. Overdue Alert Template

**Template Name:** `overdue_alert`

**Used By:**
- `SendOverdueAlerts` command
- Sent to vendors with overdue payments

**Default Template:**
```
OVERDUE: Your payment for {{overdue_items}} is past due. Your new total with penalties is ₱{{new_total_due}}. Disconnection is on {{disconnection_date}}.
```

**Available Variables:**
- `{{vendor_name}}` - Vendor's name
- `{{stall_number}}` - Stall/table number
- `{{overdue_items}}` - List of overdue items
- `{{new_total_due}}` - Total amount due including penalties
- `{{disconnection_date}}` - Disconnection date
- `{{total_due}}` - Total amount due

**Database Override Name:**
- `overdue_alert_template`

---

## Template Variables Reference

All templates support the following common variables:

| Variable | Description | Example |
|----------|-------------|---------|
| `{{vendor_name}}` | Full name of the vendor | "Juan Dela Cruz" |
| `{{stall_number}}` | Stall/table number | "MS-04" |
| `{{rent_amount}}` | Rent amount due | "₱3,780.00" |
| `{{water_amount}}` | Water amount due | "₱150.00" |
| `{{electricity_amount}}` | Electricity amount due | "₱1,500.00" |
| `{{total_due}}` | Total amount due | "₱5,430.00" |
| `{{new_total_due}}` | Total with penalties (overdue) | "₱5,650.00" |
| `{{due_date}}` | Payment due date | "Dec 31, 2025" |
| `{{disconnection_date}}` | Disconnection date | "Jan 15, 2026" |
| `{{bill_details}}` | Formatted bill list | "Rent (due Dec 31): P3,780.00, Water (due Dec 31): P150.00" |
| `{{unpaid_items}}` | List of unpaid items | "Rent, Water, Electricity" |
| `{{overdue_items}}` | List of overdue items | "Rent, Water" |
| `{{upcoming_bill_details}}` | Formatted upcoming bills | "Rent (due Jan 31): P3,780.00" |

---

## How Templates Are Used

### 1. Bill Statement SMS
- **Command**: `php artisan sms:send-billing-statements`
- **Schedule**: Configurable day of month (default: 1st)
- **Template**: `bill_statement`
- **Section Detection**: Automatically uses wet_section or dry_section based on vendor's stall section

### 2. Payment Reminder SMS
- **Command**: `php artisan sms:send-payment-reminders`
- **Schedule**: Daily (configurable)
- **Template**: `payment_reminder`
- **Recipients**: Vendors with bills due today

### 3. Overdue Alert SMS
- **Command**: `php artisan sms:send-overdue-alerts`
- **Schedule**: Daily (configurable)
- **Template**: `overdue_alert`
- **Recipients**: Vendors with overdue bills

---

## Customizing Templates

Templates can be customized through:
1. **Superadmin Portal**: Notification Templates section
2. **Database**: Direct update to `sms_notification_settings` table
3. **Config File**: Edit `config/message_templates.json` (for defaults)

### Database Structure

```sql
CREATE TABLE `sms_notification_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `message_template` text NOT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### Template Names in Database

- `bill_statement_wet_section` - Overrides wet section bill statement
- `bill_statement_dry_section` - Overrides dry section bill statement
- `payment_reminder_template` - Overrides payment reminder
- `overdue_alert_template` - Overrides overdue alert

---

## Template Processing

1. **Load Defaults**: System loads from `config/message_templates.json`
2. **Load Custom**: System loads from `sms_notification_settings` table
3. **Merge**: Database values override defaults
4. **Variable Replacement**: Variables are replaced with actual data
5. **Send**: SMS is sent via Semaphore API

---

## Notes

- Templates support Bikolano language (as seen in database examples)
- Variables are case-insensitive (e.g., `{{vendor_name}}` = `{{VENDOR_NAME}}`)
- Whitespace in variables is normalized (e.g., `{{ vendor_name }}` = `{{vendor_name}}`)
- Templates can be enabled/disabled via the `enabled` field in the database
- Section detection is automatic based on section name containing "wet"

---

## Example Custom Templates (From Database)

**Bill Statement (Wet/Dry Section):**
```
Bill Statement:

Mayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. 

An kabuuan na babayadan: P{{total_due}}. Salamat!
```

**Payment Reminder:**
```
Mayad na aga, {{vendor_name}}. Reminder: Ini an saimong mga bayadan na dai pa nababayadan: 

{{ upcoming_bill_details }}

Salamat!
```

**Overdue Alert:**
```
Mayad na aga, {{ vendor_name }}. OVERDUE: An saimong bayadan para sa {{overdue_items}} lampas na sa due date. 

An bagong total: P{{new_total_due}}. Salamat!
```

---

---

## 4. Effectivity Date SMS Templates

**Note:** These templates are **dynamically generated** in code (not stored in database or JSON files). They are sent when effectivity dates are applied for rate changes, schedule changes, and billing setting changes.

### 4.1. Utility Rate Change Notification

**Template Name:** `rate_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendRateChangeNotification()`
- `ApplyPendingRateChanges` command (when effectivity date arrives)
- Triggered when utility rates (Water/Electricity) are changed

**Template Structure:**
```
RATE CHANGE: {utility_type} rate updated.
Old rate: ₱{old_rate}/{unit}
Old rate: ₱{old_rate}/{unit} (Monthly: ₱{old_monthly_rate})
New rate: ₱{new_rate}/{unit}
New rate: ₱{new_rate}/{unit} (Monthly: ₱{new_monthly_rate})
Effective: {current_date}

Your current month bill: ₱{current_bill_amount}

- Virac Public Market
```

**Variables:**
- `{utility_type}` - Water or Electricity
- `{old_rate}` - Previous rate per unit
- `{new_rate}` - New rate per unit
- `{old_monthly_rate}` - Previous monthly rate (if applicable)
- `{new_monthly_rate}` - New monthly rate (if applicable)
- `{unit}` - "kWh" for Electricity, "day" for Water
- `{current_date}` - Current date (e.g., "December 15, 2025")
- `{current_bill_amount}` - Recalculated bill amount with new rate

**Example:**
```
RATE CHANGE: Electricity rate updated.
Old rate: ₱31.00/kWh (Monthly: ₱0.00)
New rate: ₱35.00/kWh (Monthly: ₱0.00)
Effective: December 15, 2025

Your current month bill: ₱1,750.00

- Virac Public Market
```

---

### 4.2. Rental Rate Change Notification

**Template Name:** `rental_rate_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendRentalRateChangeNotification()`
- Triggered when stall rental rates are changed

**Template Structure:**
```
RENTAL RATE CHANGE: Stall {stall_number} rate updated.
Old rate: ₱{old_daily_rate}/day
Old rate: ₱{old_daily_rate}/day (Monthly: ₱{old_monthly_rate})
New rate: ₱{new_daily_rate}/day
New rate: ₱{new_daily_rate}/day (Monthly: ₱{new_monthly_rate})
Effective: {current_date}

Your current month bill: ₱{current_bill_amount}
Discounted amount: ₱{discounted_amount}

- Virac Public Market
```

**Variables:**
- `{stall_number}` - Stall/table number (e.g., "MS-04")
- `{old_daily_rate}` - Previous daily rate
- `{new_daily_rate}` - New daily rate
- `{old_monthly_rate}` - Previous monthly rate (if applicable)
- `{new_monthly_rate}` - New monthly rate (if applicable)
- `{current_date}` - Current date
- `{current_bill_amount}` - Recalculated bill amount with new rate
- `{discounted_amount}` - Discounted amount (if payment is on or before 15th and discount rate exists)

**Example:**
```
RENTAL RATE CHANGE: Stall MS-04 rate updated.
Old rate: ₱126.00/day (Monthly: ₱3,780.00)
New rate: ₱130.00/day (Monthly: ₱3,900.00)
Effective: December 15, 2025

Your current month bill: ₱3,900.00
Discounted amount: ₱195.00

- Virac Public Market
```

---

### 4.3. Schedule Change Notification

**Template Name:** `schedule_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendScheduleChangeNotification()`
- Triggered when due dates, disconnection dates, or meter reading schedules are changed

**Template Structure:**

**For Disconnection Date:**
```
DISCONNECTION DATE CHANGE: {utility_type} disconnection date updated.
New disconnection date: Day {new_day} of each month
Effective: {current_date}

- Virac Public Market
```

**For Due Date:**
```
DUE DATE CHANGE: {utility_type} due date updated.
New due date: Day {new_day} of each month
Effective: {current_date}

- Virac Public Market
```

**For Meter Reading Schedule:**
```
METER READING SCHEDULE CHANGE: {utility_type} meter reading schedule updated.
New schedule: Day {new_day} of each month
Effective: {current_date}

- Virac Public Market
```

**Variables:**
- `{utility_type}` - Water, Electricity, or Rent
- `{new_day}` - New day of the month (1-31)
- `{current_date}` - Current date

**Example:**
```
DUE DATE CHANGE: Rent due date updated.
New due date: Day 5 of each month
Effective: December 15, 2025

- Virac Public Market
```

---

### 4.4. Billing Setting Change Notification

**Template Name:** `billing_setting_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendBillingSettingChangeNotification()`
- Triggered when billing settings (discount rate, surcharge rate, penalty rate, monthly interest rate) are changed

**Template Structure:**
```
BILLING SETTING CHANGE: {setting_display} for {utility_type} updated.
Old value: {old_value}%
New value: {new_value}%
Effective: {current_date}

- Virac Public Market
```

**Variables:**
- `{setting_display}` - User-friendly setting name:
  - "Surcharge Rate" (for `surcharge_rate`)
  - "Monthly Interest Rate" (for `monthly_interest_rate`)
  - "Penalty Rate" (for `penalty_rate`)
  - "Discount Rate" (for `discount_rate`)
- `{utility_type}` - Water, Electricity, or Rent
- `{old_value}` - Previous percentage value (multiplied by 100)
- `{new_value}` - New percentage value (multiplied by 100)
- `{current_date}` - Current date

**Example:**
```
BILLING SETTING CHANGE: Discount Rate for Rent updated.
Old value: 5.00%
New value: 7.00%
Effective: December 15, 2025

- Virac Public Market
```

---

## Effectivity Date SMS Notes

1. **Dynamic Generation**: These templates are **not stored** in the database or JSON files. They are built programmatically in `ChangeNotificationService.php`.

2. **Effectivity Date Logic**: 
   - When a change is made with an effectivity date in the future, the change is stored in history tables (`rate_histories`, `billing_setting_histories`, `schedule_histories`)
   - The `ApplyPendingRateChanges` command runs daily (via scheduler) to check for pending changes
   - When the effectivity date arrives, the change is applied and SMS notifications are sent

3. **Automatic Bill Regeneration**: When effectivity date changes are applied:
   - Current month bills are deleted and regenerated with new rates/settings
   - Outstanding balances are automatically updated
   - SMS notifications include the new bill amount

4. **Recipients**:
   - **Rate Changes**: All vendors using the utility + Staff + Meter Reader Clerks (for Electricity)
   - **Rental Rate Changes**: Specific stall vendor + Staff
   - **Schedule Changes**: All affected vendors + Staff + Meter Reader Clerks (for meter reading/disconnection)
   - **Billing Setting Changes**: All affected vendors + Staff

5. **SMS Storage**: All effectivity date SMS messages are stored in the `notifications` table with:
   - `channel`: 'sms'
   - `type`: 'change_notification', 'rental_rate_change', etc.
   - `title`: Automatically generated based on change type

---

## Related Files

- `config/message_templates.json` - Default templates (bill statement, payment reminder, overdue alert)
- `app/Services/SmsService.php` - Template processing and sending
- `app/Services/ChangeNotificationService.php` - **Effectivity date SMS message builders**
- `app/Http/Controllers/Api/NotificationTemplateController.php` - Template management API
- `app/Console/Commands/SendBillingStatements.php` - Bill statement sender
- `app/Console/Commands/SendPaymentReminders.php` - Payment reminder sender
- `app/Console/Commands/SendOverdueAlerts.php` - Overdue alert sender
- `app/Console/Commands/ApplyPendingRateChanges.php` - **Applies effectivity date changes and sends SMS**
