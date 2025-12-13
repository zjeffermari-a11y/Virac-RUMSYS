# SMS Template Improvements

## Overview
I've improved the SMS templates to be more professional, informative, and user-friendly while maintaining the local language (Bikolano/Tagalog mix) that's appropriate for the Virac Public Market context.

## Improvements Made

### 1. **Bill Statement Templates** (Wet & Dry Sections)
**Before:**
```
Bill Statement:

Mayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. 

An kabuuan na babayadan: P{{total_due}}. Salamat!
```

**After:**
```
Virac Public Market - Bill Statement

Kumusta, {{ vendor_name }}!
Stall: {{stall_number}}

MGA BAYADAN:
{{bill_details}}

TOTAL AMOUNT: P{{total_due}}
Due Date: {{due_date}}

Pakisettle po bago mag-due date para maiwasan ang penalties.

Salamat po!
```

**Improvements:**
- ✅ Added clear header with market name
- ✅ Added stall number for easy identification
- ✅ Better structure with clear sections
- ✅ Added due date prominently
- ✅ Added friendly reminder about penalties
- ✅ More professional tone while remaining friendly

### 2. **Payment Reminder Template**
**Before:**
```
Mayad na aga, {{vendor_name}}. Reminder: Ini an saimong mga bayadan na dai pa nababayadan: 

{{ upcoming_bill_details }}

Salamat!
```

**After:**
```
Virac Public Market - Payment Reminder

Kumusta, {{ vendor_name }}!
Stall: {{stall_number}}

REMINDER: Mayroon po kayong mga bayadan na malapit nang mag-due:

{{ upcoming_bill_details }}

Total: P{{total_due}}
Earliest Due: {{due_date}}

Pakisettle po bago mag-due date. Salamat!
```

**Improvements:**
- ✅ Clear header identifying it as a reminder
- ✅ Added stall number
- ✅ Shows total amount and earliest due date
- ✅ More urgent but still friendly tone
- ✅ Better formatting for readability

### 3. **Overdue Alert Template**
**Before:**
```
Mayad na aga, {{ vendor_name }}. OVERDUE: An saimong bayadan para sa {{overdue_items}} lampas na sa due date. 

An bagong total: P{{new_total_due}}. Salamat!
```

**After:**
```
Virac Public Market - OVERDUE ALERT

Kumusta, {{ vendor_name }}!
Stall: {{stall_number}}

⚠️ OVERDUE BILLS:
{{ overdue_bill_details }}

TOTAL DUE (with penalties): P{{new_total_due}}

Ang inyong bayadan para sa {{overdue_items}} ay lampas na sa due date. Pakisettle po agad para maiwasan ang disconnection.

Salamat po!
```

**Improvements:**
- ✅ Clear "OVERDUE ALERT" header for urgency
- ✅ Added stall number
- ✅ Shows detailed overdue bill breakdown
- ✅ Emphasizes total with penalties
- ✅ Clear warning about disconnection
- ✅ More urgent but professional tone

## Key Features of New Templates

1. **Clear Identification**: All templates include market name and stall number
2. **Better Structure**: Organized sections make information easy to scan
3. **Complete Information**: Includes all relevant details (amounts, dates, items)
4. **Professional Tone**: Friendly but professional, appropriate for business communication
5. **Action-Oriented**: Clear calls to action (pay before due date, avoid penalties/disconnection)
6. **Bilingual Approach**: Mix of Tagalog and Bikolano that's commonly used in the region

## Available Variables

All templates use these variables:
- `{{vendor_name}}` - Vendor's name
- `{{stall_number}}` - Stall/table number
- `{{bill_details}}` - Detailed breakdown of all bills
- `{{total_due}}` - Total amount due
- `{{due_date}}` - Earliest due date
- `{{upcoming_bill_details}}` - Bills that are due soon
- `{{overdue_bill_details}}` - Bills that are overdue
- `{{overdue_items}}` - List of overdue utility types
- `{{new_total_due}}` - Total including penalties (for overdue)

## Next Steps

To apply these templates:
1. Run the seeder: `php artisan db:seed --class=SmsNotificationSettingsSeeder`
2. Or update manually through the admin interface in "Billing Statement SMS Notification Settings"
3. Test the templates using the "Test SMS" feature in the admin panel

