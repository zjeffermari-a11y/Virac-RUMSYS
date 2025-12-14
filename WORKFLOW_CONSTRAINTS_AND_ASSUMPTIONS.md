# Workflow Constraints and Assumptions

This document outlines the constraints (system limitations) and assumptions (expected conditions) related to workflows in the Virac Public Market - Rent and Utility Management System.

---

## 🔒 WORKFLOW CONSTRAINTS

Constraints define the system's limitations - conditions that restrict what the system can do or how workflows operate.

### 1. **Billing Workflow Constraints**

#### Bill Status Constraints
- **Constraint**: Bills can only have two statuses: `'paid'` or `'unpaid'` (binary state)
- **Impact**: No partial payment, pending, or cancelled states
- **Location**: `app/Models/Billing.php`, `app/Http/Controllers/StaffPortalController.php`

#### Payment Processing Constraints
- **Constraint**: Payments can only be recorded for bills with status `'unpaid'`
- **Impact**: Cannot record payment for already paid bills (prevents duplicate payments)
- **Location**: `app/Http/Controllers/Api/StaffController.php:markAsPaid()`

#### Bill Generation Constraints
- **Constraint**: Rent bills are generated for the **current month**, utility bills for the **previous month**
- **Impact**: Cannot generate bills for arbitrary periods
- **Location**: `app/Console/Commands/GenerateNewVendorBills.php:processSingleStall()`

#### Water Bill Constraints
- **Constraint**: Water bills are only generated for stalls in the "Wet Section"
- **Impact**: Other sections cannot have water bills
- **Location**: `app/Console/Commands/GenerateNewVendorBills.php:119`

#### Electricity Bill Constraints
- **Constraint**: Initial electricity bills are created with amount `0` until meter reading is entered
- **Impact**: Electricity bills require meter reading before actual amount is calculated
- **Location**: `app/Console/Commands/GenerateNewVendorBills.php:135-140`

---

### 2. **Payment Calculation Workflow Constraints**

#### Discount Application Constraints
- **Constraint**: Early payment discount only applies if:
  1. Payment date is **on or before the 15th** of the month
  2. Payment is for the **same month** as the bill period
  3. Bill type is **Rent** (not utilities)
  4. Discount rate is configured and greater than 0
- **Impact**: Discount window is limited to first 15 days of billing month
- **Location**: `app/Http/Controllers/StaffPortalController.php:225-234`

#### Penalty Calculation Constraints
- **Constraint**: Different penalty rules for Rent vs Utilities:
  - **Rent**: Surcharge + Monthly Interest (calculated by months overdue)
  - **Utilities**: Fixed penalty rate (percentage of original amount)
- **Impact**: Cannot apply same penalty logic to all bill types
- **Location**: `app/Http/Controllers/StaffPortalController.php:214-224`

#### Payment Amount Constraints
- **Constraint**: Payment amount is calculated based on:
  - Original amount
  - Payment date relative to due date
  - Bill type (Rent vs Utilities)
  - Billing settings configuration
- **Impact**: Amount cannot be manually overridden; must follow calculation rules
- **Location**: `app/Models/Billing.php:getCurrentAmountDueAttribute()`

---

### 3. **Authentication & Authorization Workflow Constraints**

#### Password Change Constraints
- **Constraint**: Vendors **must** change password on first login before accessing any other routes
- **Impact**: Vendors cannot use system until password is changed
- **Location**: `app/Http/Middleware/ForcePasswordChange.php`

#### Role-Based Access Constraints
- **Constraint**: Users can only access routes matching their role (strict role matching)
- **Impact**: No role hierarchy or inheritance; exact role match required
- **Location**: `app/Http/Middleware/CheckRole.php`

#### Authentication Constraints
- **Constraint**: System uses username-based authentication (not email)
- **Impact**: Email field exists but is not used for login
- **Location**: `app/Http/Controllers/Auth/LoginController.php`

---

### 4. **SMS Notification Workflow Constraints**

#### Contact Number Constraints
- **Constraint**: SMS reminders are only sent to vendors with:
  1. Active status
  2. Assigned stall
  3. Non-null contact number
- **Impact**: Vendors without contact numbers cannot receive SMS reminders
- **Location**: `app/Console/Commands/SendPaymentReminders.php:41-50`

#### Reminder Schedule Constraints
- **Constraint**: Reminder days must be:
  - Positive integers
  - Between 1 and 365 days
  - Configured in schedules table
- **Impact**: Reminder schedule is limited to valid day ranges
- **Location**: `app/Console/Commands/SendPaymentReminders.php:30`

#### Bill Status Constraints for Reminders
- **Constraint**: Reminders only sent for bills with status `'unpaid'`
- **Impact**: Paid bills do not trigger reminders
- **Location**: `app/Console/Commands/SendPaymentReminders.php:45`

---

### 5. **Data Integrity Workflow Constraints**

#### Transaction Constraints
- **Constraint**: Payment recording must be wrapped in database transactions
- **Impact**: Partial payment updates are prevented (all-or-nothing)
- **Location**: `app/Http/Controllers/StaffPortalController.php:196`

#### Bill Uniqueness Constraints
- **Constraint**: Only one bill per stall per utility type per period (enforced by `updateOrCreate`)
- **Impact**: Prevents duplicate bills for same period
- **Location**: `app/Console/Commands/GenerateNewVendorBills.php:112-114`

#### Payment Recording Constraints
- **Constraint**: Payment record must be created when bill status changes to 'paid'
- **Impact**: Cannot mark bill as paid without creating payment record
- **Location**: `app/Http/Controllers/Api/StaffController.php:373-379`

---

### 6. **Audit Trail Workflow Constraints**

#### Audit Logging Constraints
- **Constraint**: All critical operations must be logged to audit_trails table
- **Impact**: System requires audit logging for compliance
- **Location**: `app/Services/AuditLogger.php`

#### Audit Data Constraints
- **Constraint**: Audit logs must include: user_id, role_id, action, module, result, timestamp
- **Impact**: Incomplete audit logs may cause compliance issues
- **Location**: `app/Services/AuditLogger.php`

---

## 📋 WORKFLOW ASSUMPTIONS

Assumptions describe expected conditions that are taken as true for planning purposes - conditions that could change but are expected to remain stable.

### 1. **Billing Workflow Assumptions**

#### Billing Settings Assumptions
- **Assumption**: Billing settings exist in database for all utility types (Rent, Water, Electricity)
- **Rationale**: Payment calculations depend on settings; system assumes they are configured
- **Risk**: If settings missing, calculations may fail or use default values

#### Schedule Configuration Assumptions
- **Assumption**: Schedules table contains entries for:
  - Due dates for each utility type
  - Disconnection dates for each utility type
  - SMS reminder schedules
- **Rationale**: Bill generation and reminders depend on schedule configuration
- **Risk**: Missing schedules may cause null due dates or failed reminders

#### Rate Configuration Assumptions
- **Assumption**: Rates are configured in database for Water and Electricity
- **Rationale**: Utility bill calculations require rate values
- **Risk**: Missing rates may result in zero-amount bills

---

### 2. **Payment Workflow Assumptions**

#### Payment Date Assumptions
- **Assumption**: Payment dates are accurate and reflect actual payment time
- **Rationale**: Penalty and discount calculations depend on payment date accuracy
- **Risk**: Incorrect dates may result in wrong amounts

#### Billing Period Assumptions
- **Assumption**: Bills are generated monthly on a consistent schedule
- **Rationale**: System assumes monthly billing cycle
- **Risk**: Irregular generation may cause gaps or overlaps

#### Payment Processing Assumptions
- **Assumption**: Staff members accurately record payment amounts and dates
- **Rationale**: System trusts manual payment entry
- **Risk**: Human error may cause incorrect payment records

---

### 3. **User Workflow Assumptions**

#### Vendor Contact Assumptions
- **Assumption**: Vendors have valid, active contact numbers for SMS notifications
- **Rationale**: SMS reminders assume contact numbers are available and correct
- **Risk**: Invalid numbers or changed numbers may prevent notifications

#### User Behavior Assumptions
- **Assumption**: Users understand the payment workflow and discount/penalty rules
- **Rationale**: System assumes users can navigate payment process
- **Risk**: Confusion may lead to incorrect payments or complaints

#### Password Security Assumptions
- **Assumption**: Vendors will choose secure passwords when forced to change
- **Rationale**: System enforces password change but not password strength
- **Risk**: Weak passwords may compromise security

---

### 4. **System Operation Assumptions**

#### Scheduled Task Assumptions
- **Assumption**: Cron jobs run daily and scheduled commands execute successfully
- **Rationale**: Bill generation and reminders depend on scheduled execution
- **Risk**: Failed cron jobs may cause missed bill generation or reminders

#### Database Availability Assumptions
- **Assumption**: Database is available and responsive during bill generation
- **Rationale**: System assumes database access for all operations
- **Risk**: Database downtime may cause failed operations

#### SMS Service Assumptions
- **Assumption**: SMS gateway service is available and has sufficient credits
- **Rationale**: Reminders assume SMS service is operational
- **Risk**: Service outages may prevent notifications

---

### 5. **Data Quality Assumptions**

#### Meter Reading Assumptions
- **Assumption**: Meter readers enter accurate and timely readings monthly
- **Rationale**: Electricity bill calculations depend on meter readings
- **Risk**: Delayed or incorrect readings may cause billing errors

#### Stall Assignment Assumptions
- **Assumption**: Stalls are properly assigned to vendors before bill generation
- **Rationale**: Bill generation assumes vendor-stall relationships exist
- **Risk**: Unassigned stalls may not generate bills

#### Historical Data Assumptions
- **Assumption**: Previous month's utility readings are available for current month billing
- **Rationale**: Utility bills for previous month assume readings were entered
- **Risk**: Missing readings may cause incorrect bill amounts

---

### 6. **Business Process Assumptions**

#### Payment Window Assumptions
- **Assumption**: Early payment discount period (1st-15th) aligns with business needs
- **Rationale**: System assumes this discount window is appropriate
- **Risk**: Business rules may change, requiring code updates

#### Penalty Calculation Assumptions
- **Assumption**: Monthly interest calculation for Rent (using `floatDiffInMonths`) is acceptable
- **Rationale**: System assumes this calculation method is correct
- **Risk**: Calculation method may not match business requirements exactly

#### Disconnection Process Assumptions
- **Assumption**: Disconnection dates are calculated but actual disconnection is manual
- **Rationale**: System calculates dates but doesn't enforce disconnection
- **Risk**: Disconnection may not occur automatically as expected

---

## 📊 Summary

### Constraints Summary
- **Total Workflow Constraints**: 20+
- **Categories**: Billing (6), Payment (4), Authentication (3), SMS (3), Data Integrity (3), Audit (2)

### Assumptions Summary
- **Total Workflow Assumptions**: 15+
- **Categories**: Billing (3), Payment (3), User (3), System (3), Data Quality (3), Business Process (3)

---

## 🔄 Key Differences

| Aspect | Constraints | Assumptions |
|--------|------------|-------------|
| **Nature** | Hard limitations that cannot be changed without code modification | Expected conditions that may change |
| **Enforcement** | Enforced by system logic and validation | Expected but not guaranteed |
| **Flexibility** | Fixed and rigid | May vary in practice |
| **Impact if Violated** | System will fail or prevent operation | System may work incorrectly or produce wrong results |
| **Examples** | "Bills can only be paid if status is 'unpaid'" | "Billing settings exist in database" |

---

*Document Created: 2025-01-27*
*Based on System Review and Codebase Analysis*

