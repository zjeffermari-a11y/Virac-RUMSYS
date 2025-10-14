<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('dashboard')->group(function () {
    Route::get('/kpis', [DashboardController::class, 'getKpis']);
    Route::get('/vendor-distribution', [DashboardController::class, 'getVendorDistribution']);
    Route::get('/collection-trends', [DashboardController::class, 'getCollectionTrends']);
    Route::get('/utility-consumption', [DashboardController::class, 'getUtilityConsumption']);
    Route::get('/filter-years', [DashboardController::class, 'getFilterYears']);
    Route::get('/top-performers', [DashboardController::class, 'getTopPerformingVendors']);
    Route::get('/vendors-needing-support', [DashboardController::class, 'getVendorsNeedingSupport']);
    Route::get('/all-data', [DashboardController::class, 'getAllDashboardData']);
});

Route::prefix('staff')->group(function () {
    Route::get('/vendors', [StaffController::class, 'getVendors']);
    Route::get('/sections', [StaffController::class, 'getSections']);
    Route::put('/vendors/{id}', [StaffController::class, 'updateVendor']);
    Route::get('/bill-management', [StaffController::class, 'getBillManagementData']);
    Route::get('/vendors/{user}/payment-history', [StaffController::class, 'getPaymentHistory']);
    Route::get('/vendors/{user}/outstanding-bills', [StaffController::class, 'getOutstandingBills']);
    Route::get('/billings/{billing}/breakdown', [StaffController::class, 'getBillBreakdown']);
    Route::get('/vendors/{user}/payment-history-filtered', [StaffController::class, 'getFilteredPaymentHistory']);
    Route::post('/bills/{billingId}/pay', [StaffController::class, 'markAsPaid']);
    Route::get('/vendor/{user}/dashboard-data', [StaffController::class, 'getVendorDashboardData']);
    Route::get('/vendors/{user}/payment-years', [StaffController::class, 'getPaymentYears']);
    Route::get('/reports/monthly', [StaffController::class, 'getMonthlyReport']);
    Route::get('/unassigned-vendors', [StaffController::class, 'getUnassignedVendors']);
    Route::get('/available-stalls', [StaffController::class, 'getAvailableStalls']);
    Route::post('/assign-stall', [StaffController::class, 'assignStall']);
});

use App\Http\Controllers\Api\RentalRateController;

// Routes for managing market stall rental rates
Route::get('/rental-rates', [RentalRateController::class, 'index']);
Route::post('/rental-rates', [RentalRateController::class, 'store']);
Route::put('/rental-rates/batch-update', [RentalRateController::class, 'batchUpdate']);
Route::put('/rental-rates/{stall}', [RentalRateController::class, 'update']);
Route::delete('/rental-rates/{stall}', [RentalRateController::class, 'destroy']);
Route::get('/sections/{sectionName}/next-table-number', [RentalRateController::class, 'getNextTableNumber']);


use App\Http\Controllers\Api\UtilityRateController;

// Route to fetch all utility rates (Electricity and Water)
Route::get('/utility-rates', [UtilityRateController::class, 'index']);

// Route to update a specific utility rate
Route::put('/utility-rates/batch-update', [UtilityRateController::class, 'batchUpdate']);
Route::put('/utility-rates/{id}', [UtilityRateController::class, 'update']);



// History Logs
Route::get('/utility-rate-history', [UtilityRateController::class, 'history']);



//Meter Reading Schedule
use App\Http\Controllers\Api\ScheduleController;

// Routes for managing the meter reading schedule
Route::get('/schedules/meter-reading', [ScheduleController::class, 'show']);
Route::put('/schedules/meter-reading/{schedule}', [ScheduleController::class, 'update']);
Route::get('/schedules/meter-reading/history', [ScheduleController::class, 'history']);

// Eoutes for Due Datae and Disconnection Date Schedule

Route::get('/schedules/billing-dates', [ScheduleController::class, 'getBillingDates']);
Route::put('/schedules/billing-dates', [ScheduleController::class, 'updateBillingDates']);
Route::get('/schedules/billing-dates/history', [ScheduleController::class, 'getBillingDatesHistory']);

Route::get('/schedules/sms', [ScheduleController::class, 'getSmsSchedules']);
Route::put('/schedules/sms', [ScheduleController::class, 'updateSmsSchedules']);
Route::get('/schedules/sms/history', [ScheduleController::class, 'getSmsScheduleHistory']);


//Notficication template
use App\Http\Controllers\Api\NotificationTemplateController;

Route::get('/notification-templates', [NotificationTemplateController::class, 'index']);
Route::post('/notification-templates', [NotificationTemplateController::class, 'update']);



use App\Http\Controllers\Api\ReadingEditRequestController;
use App\Http\Controllers\Api\UserSettingsController;
use App\Http\Controllers\NotificationController;

// Routes for managing reading edit requests
Route::get('/reading-edit-requests', [ReadingEditRequestController::class, 'index']);
Route::put('/reading-edit-requests/{request}/status', [ReadingEditRequestController::class, 'updateStatus']);

Route::get('/notifications/unread', [NotificationController::class, 'unread']);

// Routes for managing SMS contact numbers for key roles
Route::get('/user-settings/role-contacts', [UserSettingsController::class, 'getRoleContacts']);
Route::put('/user-settings/role-contacts', [UserSettingsController::class, 'updateRoleContacts']);

use App\Http\Controllers\Api\AuditTrailController;

// route for fetching audit trail logs
Route::get('/audit-trails', [AuditTrailController::class, 'index']);

use App\Http\Controllers\Api\BillingSettingsController;

Route::prefix('billing-settings')->group(function () {
    Route::get('/', [BillingSettingsController::class, 'index']);
    Route::put('/', [BillingSettingsController::class, 'update']);
    Route::get('/history', [BillingSettingsController::class, 'history']);
});

Route::prefix('api/admin')->middleware(['auth:sanctum', 'role:Admin'])->group(function () {
    Route::get('/sections', [\App\Http\Controllers\Api\SectionController::class, 'index']);
});