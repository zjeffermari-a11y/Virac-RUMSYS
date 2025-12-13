-- ============================================
-- Payment Status Diagnostic Queries
-- ============================================
-- Run these queries to check payment status and outstanding balance logic
-- ============================================

-- 1. Check all paid bills with their payment dates
-- This shows which bills are paid and when they were paid
SELECT 
    b.id AS billing_id,
    b.stall_id,
    b.utility_type,
    b.period_start,
    b.period_end,
    b.status,
    b.amount,
    p.payment_date,
    p.amount_paid,
    CASE 
        WHEN MONTH(p.payment_date) = MONTH(CURDATE()) AND YEAR(p.payment_date) = YEAR(CURDATE()) 
        THEN 'YES - Current Month' 
        ELSE 'NO - Previous Month' 
    END AS should_be_in_outstanding
FROM billing b
INNER JOIN payments p ON b.id = p.billing_id
WHERE b.status = 'paid'
ORDER BY p.payment_date DESC;

-- 2. Check bills that SHOULD be in outstanding balance
-- (Unpaid bills OR paid bills from current month)
SELECT 
    b.id AS billing_id,
    b.stall_id,
    b.utility_type,
    b.period_start,
    b.period_end,
    b.status,
    b.amount,
    p.payment_date,
    CASE 
        WHEN b.status = 'unpaid' THEN 'Unpaid'
        WHEN p.payment_date IS NOT NULL THEN 'Paid in Current Month'
        ELSE 'Unknown'
    END AS reason_in_outstanding
FROM billing b
LEFT JOIN payments p ON b.id = p.billing_id
WHERE (
    b.status = 'unpaid'
    OR (
        b.status = 'paid' 
        AND p.payment_date IS NOT NULL
        AND YEAR(p.payment_date) = YEAR(CURDATE())
        AND MONTH(p.payment_date) = MONTH(CURDATE())
    )
)
ORDER BY b.due_date DESC;

-- 3. Check October 2025 payments specifically
-- These should NOT appear in outstanding balance if current month is after October
SELECT 
    b.id AS billing_id,
    b.stall_id,
    b.utility_type,
    b.period_start,
    b.period_end,
    b.status,
    b.amount,
    p.payment_date,
    p.amount_paid,
    CASE 
        WHEN MONTH(CURDATE()) > 10 AND YEAR(CURDATE()) >= 2025 
        THEN '⚠️ Should NOT be in outstanding balance'
        ELSE 'OK'
    END AS status_check
FROM billing b
INNER JOIN payments p ON b.id = p.billing_id
WHERE b.status = 'paid'
    AND YEAR(p.payment_date) = 2025
    AND MONTH(p.payment_date) = 10
ORDER BY p.payment_date DESC;

-- 4. Check for any inconsistencies
-- Bills marked as paid but have no payment record
SELECT 
    b.id AS billing_id,
    b.stall_id,
    b.utility_type,
    b.status,
    b.amount,
    '⚠️ Marked as paid but no payment record found' AS issue
FROM billing b
LEFT JOIN payments p ON b.id = p.billing_id
WHERE b.status = 'paid'
    AND p.id IS NULL;

-- 5. Check for payment records without corresponding paid status
-- Payments exist but billing status is not paid
SELECT 
    b.id AS billing_id,
    b.stall_id,
    b.utility_type,
    b.status,
    p.payment_date,
    p.amount_paid,
    '⚠️ Payment exists but billing status is not paid' AS issue
FROM billing b
INNER JOIN payments p ON b.id = p.billing_id
WHERE b.status != 'paid';

-- 6. Summary: Count bills by status and payment month
SELECT 
    CASE 
        WHEN b.status = 'unpaid' THEN 'Unpaid'
        WHEN YEAR(p.payment_date) = YEAR(CURDATE()) AND MONTH(p.payment_date) = MONTH(CURDATE()) 
        THEN 'Paid - Current Month'
        ELSE CONCAT('Paid - ', MONTHNAME(p.payment_date), ' ', YEAR(p.payment_date))
    END AS category,
    COUNT(*) AS count,
    SUM(b.amount) AS total_amount
FROM billing b
LEFT JOIN payments p ON b.id = p.billing_id
GROUP BY category
ORDER BY category;
