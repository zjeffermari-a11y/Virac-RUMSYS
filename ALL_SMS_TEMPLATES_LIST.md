# Complete List of All SMS Templates in Virac-RUMSYS

This document provides a comprehensive list of **ALL** SMS templates used in the Virac-RUMSYS system, including both configurable templates and dynamically generated ones.

---

## Template Categories

1. **Configurable Templates** (stored in database/JSON, can be customized)
2. **Dynamic Templates** (generated programmatically, not stored)

---

## PART 1: CONFIGURABLE SMS TEMPLATES

These templates are stored in:
- **Default location**: `config/message_templates.json`
- **Custom overrides**: `sms_notification_settings` database table

---

### 1. Bill Statement Template

**Template Name:** `bill_statement`

**Database Override Names:**
- `bill_statement_wet_section` (for wet sections)
- `bill_statement_dry_section` (for dry sections)

**Variants:**

#### 1.1. Wet Section Bill Statement
**Default Template:**
```
Your bill statement: Rent - ₱{{rent_amount}}, Water - ₱{{water_amount}}, Electricity - ₱{{electricity_amount}}. Total due: ₱{{total_due}} on {{due_date}}.
```

**Used By:**
- `SendBillingStatements` command
- Scheduled monthly (configurable day, default: 1st of month)

**Available Variables:**
- `{{vendor_name}}` - Vendor's full name
- `{{stall_number}}` - Stall/table number (e.g., "MS-04")
- `{{rent_amount}}` - Rent amount due (formatted with ₱)
- `{{water_amount}}` - Water amount due (formatted with ₱)
- `{{electricity_amount}}` - Electricity amount due (formatted with ₱)
- `{{total_due}}` - Total amount due (formatted with ₱)
- `{{due_date}}` - Payment due date (e.g., "Dec 31, 2025")
- `{{bill_details}}` - Formatted list of bills with details
- `{{disconnection_date}}` - Disconnection date (if applicable)

**Example Output:**
```
Your bill statement: Rent - ₱3,780.00, Water - ₱150.00, Electricity - ₱1,500.00. Total due: ₱5,430.00 on Dec 31, 2025.
```

#### 1.2. Dry Section Bill Statement
**Default Template:**
```
Your bill statement: Rent - ₱{{rent_amount}}, Water - ₱{{water_amount}}. Total due: ₱{{total_due}} on {{due_date}}.
```

**Available Variables:** (Same as wet section, except no `{{electricity_amount}}`)

**Example Output:**
```
Your bill statement: Rent - ₱3,780.00, Water - ₱150.00. Total due: ₱3,930.00 on Dec 31, 2025.
```

**Note:** System automatically detects wet/dry section based on section name containing "wet".

---

### 2. Payment Reminder Template

**Template Name:** `payment_reminder`

**Database Override Name:** `payment_reminder_template`

**Default Template:**
```
Reminder: The following payments are due today: {{unpaid_items}}. Thank you.
```

**Used By:**
- `SendPaymentReminders` command
- Scheduled daily (configurable)
- Sent to vendors with bills due today

**Available Variables:**
- `{{vendor_name}}` - Vendor's full name
- `{{stall_number}}` - Stall/table number
- `{{unpaid_items}}` - List of unpaid items (e.g., "Rent, Water, Electricity")
- `{{upcoming_bill_details}}` - Formatted upcoming bills with amounts and due dates
- `{{total_due}}` - Total amount due (formatted with ₱)
- `{{due_date}}` - Payment due date

**Example Output:**
```
Reminder: The following payments are due today: Rent, Water, Electricity. Thank you.
```

---

### 3. Overdue Alert Template

**Template Name:** `overdue_alert`

**Database Override Name:** `overdue_alert_template`

**Default Template:**
```
OVERDUE: Your payment for {{overdue_items}} is past due. Your new total with penalties is ₱{{new_total_due}}. Disconnection is on {{disconnection_date}}.
```

**Used By:**
- `SendOverdueAlerts` command
- Scheduled daily (configurable)
- Sent to vendors with overdue bills

**Available Variables:**
- `{{vendor_name}}` - Vendor's full name
- `{{stall_number}}` - Stall/table number
- `{{overdue_items}}` - List of overdue items (e.g., "Rent, Water")
- `{{new_total_due}}` - Total amount due including penalties (formatted with ₱)
- `{{disconnection_date}}` - Disconnection date
- `{{total_due}}` - Total amount due (without penalties)

**Example Output:**
```
OVERDUE: Your payment for Rent, Water is past due. Your new total with penalties is ₱5,650.00. Disconnection is on Jan 15, 2026.
```

---

## PART 2: DYNAMIC SMS TEMPLATES

These templates are **NOT stored** in database or JSON files. They are **dynamically generated** in code (`ChangeNotificationService.php`) when effectivity dates arrive or changes are applied immediately.

---

### 4. Utility Rate Change Notification

**Template Name:** `rate_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendRateChangeNotification()`
- `ApplyPendingRateChanges` command (when effectivity date arrives)
- Triggered when Water or Electricity rates are changed

**Template Structure:**
```
RATE CHANGE: {utility_type} rate inupdate.
Bagong rate: ₱{new_rate}/{unit}
Epektibo sa: {effectivity_date}

Bayadan sa bulan na ini: ₱{current_bill_amount}

- Virac Public Market
```

**Variables:**
- `{utility_type}` - "Water" or "Electricity"
- `{new_rate}` - New rate per unit (formatted to 2 decimals)
- `{unit}` - "kWh" for Electricity, "day" for Water
- `{effectivity_date}` - **Chosen effectivity date** set by admin (e.g., "December 15, 2025" or "January 1, 2026")
  - Can be today's date or a future date
  - Displayed as "Epektibo sa: {effectivity_date}"
- `{current_bill_amount}` - Recalculated bill amount with new rate:
  - **Water**: `days_in_current_month × new_rate`
  - **Electricity**: `consumption × new_rate` (from current month bill if available, otherwise previous month)

**Important Notes:**
- SMS is sent **IMMEDIATELY** when admin sets/adjusts the effectivity date (not when the date arrives)
- The SMS shows the **CHOSEN effectivity date**, not the current date
- If admin chooses a future date, SMS is still sent immediately but shows the future date

**Example - Electricity (Effective Today):**
```
RATE CHANGE: Electricity rate inupdate.
Bagong rate: ₱35.00/kWh
Epektibo sa: December 15, 2025

Bayadan sa bulan na ini: ₱1,750.00

- Virac Public Market
```

**Example - Water (Future Effectivity Date):**
```
RATE CHANGE: Water rate inupdate.
Bagong rate: ₱6.00/day
Epektibo sa: January 1, 2026

Bayadan sa bulan na ini: ₱186.00

- Virac Public Market
```
*(Calculation: 31 days × ₱6.00 = ₱186.00)*

**Note:** Even if the effectivity date is in the future (January 1, 2026), the SMS is sent **immediately** when the admin sets the effectivity date, showing the chosen future date.

**Recipients:**
- All vendors using the utility (Water: wet section vendors only; Electricity: all vendors)
- Staff members
- Meter Reader Clerks (for Electricity only)

---

### 5. Rental Rate Change Notification

**Template Name:** `rental_rate_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendRentalRateChangeNotification()`
- Triggered when stall rental rates are changed

**Template Structure:**
```
RENTAL RATE CHANGE: Stall {stall_number} rate inupdate.
Bagong rate: ₱{new_daily_rate}/day
Epektibo sa: {effectivity_date}

Bayadan sa bulan na ini: ₱{current_bill_amount}
[Discounted na bayadan: ₱{original_amount} - ₱{discount_amount} = ₱{final_amount}]
[Only shown if payment is on or before 15th and discount rate exists]

- Virac Public Market
```

**Variables:**
- `{stall_number}` - Stall/table number (e.g., "MS-04")
- `{new_daily_rate}` - New daily rate (formatted to 2 decimals)
- `{effectivity_date}` - **Chosen effectivity date** set by admin (e.g., "December 15, 2025" or "January 1, 2026")
  - Can be today's date or a future date
  - Displayed as "Epektibo sa: {effectivity_date}"
- `{current_bill_amount}` - Recalculated bill amount with new monthly rate
- `{original_amount}` - Original bill amount before discount
- `{discount_amount}` - Discount amount calculated
- `{final_amount}` - Final amount after discount

**Important Notes:**
- SMS is sent **IMMEDIATELY** when admin sets/adjusts the effectivity date
- The SMS shows the **CHOSEN effectivity date**, not the current date

**Example - Without Discount (Effective Today):**
```
RENTAL RATE CHANGE: Stall MS-04 rate inupdate.
Bagong rate: ₱130.00/day
Epektibo sa: December 15, 2025

Bayadan sa bulan na ini: ₱3,900.00

- Virac Public Market
```

**Example - With Discount (Future Effectivity Date):**
```
RENTAL RATE CHANGE: Stall MS-04 rate inupdate.
Bagong rate: ₱130.00/day
Epektibo sa: January 1, 2026

Bayadan sa bulan na ini: ₱3,900.00
Discounted na bayadan: ₱3,900.00 - ₱195.00 = ₱3,705.00

- Virac Public Market
```

**Note:** SMS is sent immediately when admin sets the effectivity date, even if it's in the future.

**Recipients:**
- Specific stall vendor
- Staff members

---

### 6. Schedule Change Notification

**Template Name:** `schedule_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendScheduleChangeNotification()`
- Triggered when due dates, disconnection dates, or meter reading schedules are changed

**Template Variants:**

#### 6.1. Disconnection Date Change
**Template Structure:**
```
BAGONG DISCONNECTION DATE ISKEDYUL: {utility_type} disconnection date inupdate.
BAGONG DISCONNECTION DATE: Ika-{new_day} aldaw nin bulan
EPEKTIBO: {current_date}

- Virac Public Market
```

**Example:**
```
BAGONG DISCONNECTION DATE ISKEDYUL: Electricity disconnection date inupdate.
BAGONG DISCONNECTION DATE: Ika-15 aldaw nin bulan
EPEKTIBO: December 15, 2025

- Virac Public Market
```

#### 6.2. Due Date Change
**Template Structure:**
```
BAGONG DUE DATE ISKEDYUL: {utility_type} due date inupdate.
Bagong due date: Ika-{new_day} aldaw nin bulan
EPEKTIBO: {current_date}

- Virac Public Market
```

**Example:**
```
BAGONG DUE DATE ISKEDYUL: Rent due date inupdate.
Bagong due date: Ika-5 aldaw nin bulan
EPEKTIBO: December 15, 2025

- Virac Public Market
```

#### 6.3. Meter Reading Schedule Change
**Template Structure:**
```
BAGONG METER READING ISKEDYuL: {utility_type} meter reading schedule updated.
BAGONG ISKEDYUL: Ika-{new_day} aldaw nin bulan
EPEKTIBO: {current_date}

- Virac Public Market
```

**Example:**
```
BAGONG METER READING ISKEDYL: Electricity meter reading schedule updated.
BAGONG ISKEDYL: Ika-25 aldaw nin bulan
EPEKTIBO: December 15, 2025

- Virac Public Market
```

**Variables (all variants):**
- `{utility_type}` - "Water", "Electricity", or "Rent"
- `{new_day}` - New day of the month (1-31)
- `{effectivity_date}` - **Chosen effectivity date** set by admin (e.g., "December 15, 2025" or "January 1, 2026")
  - Can be today's date or a future date
  - Displayed as "EPEKTIBO: {effectivity_date}"

**Important Notes:**
- SMS is sent **IMMEDIATELY** when admin sets/adjusts the effectivity date
- The SMS shows the **CHOSEN effectivity date**, not the current date

**Recipients:**
- All affected vendors (Water: wet section only; Others: all vendors)
- Staff members
- Meter Reader Clerks (for meter reading/disconnection schedules)

---

### 7. Billing Setting Change Notification

**Template Name:** `billing_setting_change_notification` (dynamic)

**Used By:**
- `ChangeNotificationService::sendBillingSettingChangeNotification()`
- Triggered when billing settings are changed:
  - Discount Rate
  - Surcharge Rate
  - Penalty Rate
  - Monthly Interest Rate

**Template Structure:**
```
BAGONG BILLING SETTING : {setting_display} para sa {utility_type} inupdate.
Lumang value: {old_value}%
Bagong value: {new_value}%
Epektibo: {current_date}

- Virac Public Market
```

**Variables:**
- `{setting_display}` - User-friendly setting name:
  - "Surcharge Rate" (for `surcharge_rate`)
  - "Monthly Interest Rate" (for `monthly_interest_rate`)
  - "Penalty Rate" (for `penalty_rate`)
  - "Discount Rate" (for `discount_rate`)
- `{utility_type}` - "Water", "Electricity", or "Rent"
- `{old_value}` - Previous percentage value (multiplied by 100, formatted to 2 decimals)
- `{new_value}` - New percentage value (multiplied by 100, formatted to 2 decimals)
- `{effectivity_date}` - **Chosen effectivity date** set by admin (e.g., "December 15, 2025" or "January 1, 2026")
  - Can be today's date or a future date
  - Displayed as "Epektibo: {effectivity_date}"

**Important Notes:**
- SMS is sent **IMMEDIATELY** when admin sets/adjusts the effectivity date
- The SMS shows the **CHOSEN effectivity date**, not the current date

**Example - Discount Rate:**
```
BAGONG BILLING SETTING : Discount Rate para sa Rent inupdated.
Lumang value: 5.00%
Bagong value: 7.00%
Epektibo: December 15, 2025

- Virac Public Market
```

**Example - Penalty Rate:**
```
BAGONG BILLING SETTING : Penalty Rate para sa Electricity inupdated.
Lumang value: 2.00%
Bagong value: 3.00%
Epektibo: December 15, 2025

- Virac Public Market
```

**Recipients:**
- All affected vendors (Water: wet section only; Others: all vendors)
- Staff members

---

## SUMMARY TABLE

| # | Template Name | Type | Storage | Customizable | Command/Service |
|---|---------------|------|---------|--------------|-----------------|
| 1 | Bill Statement (Wet) | Configurable | DB/JSON | Yes | `SendBillingStatements` |
| 2 | Bill Statement (Dry) | Configurable | DB/JSON | Yes | `SendBillingStatements` |
| 3 | Payment Reminder | Configurable | DB/JSON | Yes | `SendPaymentReminders` |
| 4 | Overdue Alert | Configurable | DB/JSON | Yes | `SendOverdueAlerts` |
| 5 | Utility Rate Change | Dynamic | Code | No | `ChangeNotificationService` |
| 6 | Rental Rate Change | Dynamic | Code | No | `ChangeNotificationService` |
| 7 | Schedule Change (Disconnection) | Dynamic | Code | No | `ChangeNotificationService` |
| 8 | Schedule Change (Due Date) | Dynamic | Code | No | `ChangeNotificationService` |
| 9 | Schedule Change (Meter Reading) | Dynamic | Code | No | `ChangeNotificationService` |
| 10 | Billing Setting Change | Dynamic | Code | No | `ChangeNotificationService` |

---

## TEMPLATE VARIABLES REFERENCE

### Common Variables (Available in Configurable Templates)

| Variable | Description | Example Value |
|----------|-------------|---------------|
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

### Dynamic Template Variables

| Variable | Description | Used In |
|----------|-------------|---------|
| `{utility_type}` | Water, Electricity, or Rent | Rate/Schedule/Billing Setting changes |
| `{new_rate}` | New rate per unit | Rate changes |
| `{unit}` | "kWh" or "day" | Rate changes |
| `{new_daily_rate}` | New daily rental rate | Rental rate changes |
| `{stall_number}` | Stall/table number | Rental rate changes |
| `{new_day}` | New day of month (1-31) | Schedule changes |
| `{setting_display}` | User-friendly setting name | Billing setting changes |
| `{old_value}` | Previous percentage value | Billing setting changes |
| `{new_value}` | New percentage value | Billing setting changes |
| `{effectivity_date}` | **Chosen effectivity date** (can be today or future) | All dynamic templates |
| `{current_bill_amount}` | Recalculated bill amount | Rate changes |

---

## CUSTOMIZATION NOTES

### Configurable Templates
- Can be customized via **Superadmin Portal** → Notification Templates section
- Can be edited directly in `sms_notification_settings` database table
- Default templates can be modified in `config/message_templates.json`
- Database values override JSON defaults
- Templates can be enabled/disabled via `enabled` field in database

### Dynamic Templates
- **Cannot be customized** - they are hardcoded in `ChangeNotificationService.php`
- Format is fixed to ensure consistency
- Variables are replaced programmatically
- All dynamic templates end with "- Virac Public Market"

---

## EXAMPLE CUSTOM TEMPLATES (Bikolano Language)

These examples show how templates can be customized in the database:

### Custom Bill Statement:
```
Bill Statement:

Mayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. 

An kabuuan na babayadan: P{{total_due}}. Salamat!
```

### Custom Payment Reminder:
```
Mayad na aga, {{vendor_name}}. Reminder: Ini an saimong mga bayadan na dai pa nababayadan: 

{{ upcoming_bill_details }}

Salamat!
```

### Custom Overdue Alert:
```
Mayad na aga, {{ vendor_name }}. OVERDUE: An saimong bayadan para sa {{overdue_items}} lampas na sa due date. 

An bagong total: P{{new_total_due}}. Salamat!
```

---

## RELATED FILES

- `config/message_templates.json` - Default configurable templates
- `app/Services/SmsService.php` - Template processing and sending
- `app/Services/ChangeNotificationService.php` - Dynamic template builders
- `app/Http/Controllers/Api/NotificationTemplateController.php` - Template management API
- `app/Console/Commands/SendBillingStatements.php` - Bill statement sender
- `app/Console/Commands/SendPaymentReminders.php` - Payment reminder sender
- `app/Console/Commands/SendOverdueAlerts.php` - Overdue alert sender
- `app/Console/Commands/ApplyPendingRateChanges.php` - Applies effectivity date changes and sends SMS

---

## NOTES

1. **Variable Format**: Variables use double curly braces `{{variable}}` in configurable templates, single curly braces `{variable}` in dynamic templates
2. **Case Insensitivity**: Variables are case-insensitive (e.g., `{{vendor_name}}` = `{{VENDOR_NAME}}`)
3. **Whitespace Normalization**: Whitespace in variables is normalized (e.g., `{{ vendor_name }}` = `{{vendor_name}}`)
4. **Section Detection**: Wet/dry section detection is automatic based on section name containing "wet"
5. **Effectivity Dates - IMPORTANT**:
   - When admin makes a change (rate, schedule, billing setting), they can choose an effectivity date (today or future)
   - **SMS is sent IMMEDIATELY** when the admin sets/adjusts the effectivity date (not when the date arrives)
   - The SMS shows the **CHOSEN effectivity date**, not the current date
   - If admin chooses a future date, SMS is still sent immediately but displays the future date
   - The actual change is applied when the effectivity date arrives (via `ApplyPendingRateChanges` command)
6. **Language Support**: Templates support both English and Bikolano languages
7. **SMS Storage**: All SMS messages are stored in the `notifications` table with metadata

---

*Last Updated: Based on system codebase analysis*
