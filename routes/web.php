    <?php

    use App\Http\Controllers\Auth\LoginController;
    use App\Http\Controllers\Auth\ForgotPasswordController;
    use App\Http\Controllers\Auth\ResetPasswordController;
    use App\Http\Controllers\VendorController;
    use App\Http\Controllers\SuperAdminController;
    use App\Http\Controllers\MeterReaderController;
    use App\Http\Controllers\NotificationController;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\DB;
    use App\Services\SmsService;
    use App\Http\Controllers\UtilityReadingController;
    use App\Http\Controllers\Api\ReadingEditRequestController;
    use App\Http\Controllers\StaffPortalController;
    use App\Http\Controllers\ReportController;
    use App\Http\Controllers\Api\SystemUserController;
    use App\Http\Controllers\Api\RoleController;
    //--For CRON JOBS--//
    use App\Console\Commands\GenerateMonthlyBills;
    use App\Console\Commands\SendBillingStatements;
    use App\Console\Commands\SendOverdueAlerts;
    use App\Console\Commands\SendPaymentReminders;
    use Illuminate\Support\Facades\Artisan;

    Route::get('/admin/run-command/{command}', function ($command) {
        // Security check
        if (request()->input('secret') !== env('ADMIN_SECRET')) {
            abort(403, 'Unauthorized - Invalid secret key!');
        }
        
        $result = ['success' => false, 'message' => ''];
        
        try {
            switch ($command) {
                case 'billing:generate':
                case 'sms:send-billing-statements':
                case 'sms:send-overdue-alerts':
                case 'sms:send-payment-reminders':
                    Artisan::call($command);
                    $output = Artisan::output();
                    $result = [
                        'success' => true,
                        'message' => "Command '{$command}' executed successfully!",
                        'output' => $output
                    ];
                    break;
                    
                default:
                    $result = [
                        'success' => false,
                        'message' => 'Unknown command: ' . $command
                    ];
            }
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
        
        return response()->json($result);
    })->name('admin.run-command');
    
    // Run multiple commands at once (for monthly tasks)
    Route::get('/admin/run-monthly-tasks', function () {
        // Security check
        if (request()->input('secret') !== env('ADMIN_SECRET')) {
            abort(403, 'Unauthorized - Invalid secret key!');
        }
        
        $results = [];
        
        try {
            // 1. Generate monthly bills
            Artisan::call('billing:generate');
            $results[] = [
                'command' => 'Generate Monthly Bills',
                'success' => true,
                'output' => Artisan::output()
            ];
            
            // Small delay to ensure bills are generated before sending statements
            sleep(2);
            
            // 2. Send billing statements
            Artisan::call('sms:send-billing-statements');
            $results[] = [
                'command' => 'Send Billing Statements',
                'success' => true,
                'message' => 'Statements sent',
                'output' => Artisan::output()
            ];
            
            // 3. Send overdue alerts (for existing unpaid bills)
            Artisan::call('sms:send-overdue-alerts');
            $results[] = [
                'command' => 'Send Overdue Alerts',
                'success' => true,
                'message' => 'Overdue alerts sent',
                'output' => Artisan::output()
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'All monthly tasks completed successfully!',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during monthly tasks: ' . $e->getMessage(),
                'results' => $results
            ]);
        }
    })->name('admin.run-monthly-tasks');
    
    // Dashboard to show all available commands (optional - for convenience)
    Route::get('/admin/commands-dashboard', function () {
        // Security check
        if (request()->input('secret') !== env('ADMIN_SECRET')) {
            abort(403, 'Unauthorized - Invalid secret key!');
        }
        
        $secret = request()->input('secret');
        $baseUrl = url('/admin/run-command');
        
        return response()->json([
            'message' => 'Available Commands',
            'commands' => [
                // ✅ FIX: These keys must match the 'case' statements in the route above
                'Run All Monthly Tasks (Recommended for Nov 1)' => url('/admin/run-monthly-tasks') . "?secret={$secret}",
                'Generate Monthly Bills Only' => "{$baseUrl}/billing:generate?secret={$secret}",
                'Send Billing Statements Only' => "{$baseUrl}/sms:send-billing-statements?secret={$secret}",
                'Send Overdue Alerts Only' => "{$baseUrl}/sms:send-overdue-alerts?secret={$secret}",
                'Send Payment Reminders' => "{$baseUrl}/sms:send-payment-reminders?secret={$secret}",
            ],
            'note' => 'Click any URL to execute that command'
        ]);
    })->name('admin.commands-dashboard');

    //--END--//

    Auth::routes(['reset' => false]);


    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::post('/reading-edit-requests', [ReadingEditRequestController::class, 'store'])->name('reading-edit-requests.store');
    Route::get('/reading-edit-requests', [ReadingEditRequestController::class, 'index'])->name('reading-edit-requests.index');
    Route::put('/reading-edit-requests/{readingEditRequest}/status', [ReadingEditRequestController::class, 'updateStatus'])->name('reading-edit-requests.updateStatus');

    Route::post('/meter-readings/statuses', [MeterReaderController::class, 'getStatuses'])->name('meter-readings.getStatuses');
    Route::get('/meter-reading-schedule', [MeterReaderController::class, 'getSchedule'])->name('meter-reading.getSchedule');

    Route::post('/utility-readings/bulk', [UtilityReadingController::class, 'storeBulk'])->name('utility-readings.storeBulk');

    Route::middleware(['auth', 'role:Vendor', 'force.password.change'])->group(function () {

        // Password change routes (accessible even when password change is required)
        Route::get('/vendor/change-password', [VendorController::class, 'showChangePasswordForm'])->name('vendor.password.form');
        Route::post('/vendor/change-password', [VendorController::class, 'updatePassword'])->name('vendor.password.update');

        // Protected vendor routes (requires password to be changed first)
        Route::middleware(['prevent.back.access'])->prefix('vendor')->group(function () {
            Route::get('/home', [VendorController::class, 'dashboard'])->name('vendor.dashboard');
            Route::get('/profile', [VendorController::class, 'dashboard'])->name('vendor.profile');
            Route::get('/payment_history', [VendorController::class, 'dashboard'])->name('vendor.payment_history');
        });
    });

    Route::middleware(['auth', 'role:Vendor'])->prefix('api/vendor')->group(function () {
        Route::get('/payments', [VendorController::class, 'paymentHistoryApi'])->name('api.vendor.payments');
        Route::get('/payment-years', [VendorController::class, 'paymentYearsApi'])->name('api.vendor.payment_years');
        Route::get('/dashboard-data', [VendorController::class, 'getDashboardData'])->name('api.vendor.dashboard_data');
    });

    Route::get('/meter', [MeterReaderController::class, 'index'])
        ->middleware(['auth', 'role:Meter Reader Clerk', 'prevent.back.access'])
        ->name('meter.dashboard');

    Route::get('/meter/archives', [MeterReaderController::class, 'archives'])
        ->middleware(['auth', 'role:Meter Reader Clerk'])
        ->name('meter.archives');

    Route::get('/staff', [StaffPortalController::class, 'index'])
        ->middleware(['auth', 'role:Staff', 'prevent.back.access'])
        ->name('staff.dashboard');

    Route::get('/staff/reports/download', [ReportController::class, 'download'])
        ->middleware(['auth', 'role:Staff'])
        ->name('staff.reports.download');

    Route::middleware(['auth', 'prevent.back.access'])->prefix('staff')->group(function () {
        Route::get('/view-as-vendor/{vendor}', [VendorController::class, 'dashboard'])
            ->name('staff.viewAsVendor')
            ->middleware('can:view,vendor');
        Route::get('/vendor/{vendor}/view-as-vendor-partial', [StaffPortalController::class, 'viewAsVendorPartial'])->name('staff.partials.viewAsVendor');
        Route::get('/vendor/{vendor}/payment-history-container', [StaffPortalController::class, 'paymentHistoryContainerPartial'])->name('staff.partials.paymentHistoryContainer');
    });

    Route::middleware(['auth', 'role:Admin', 'prevent.back.access'])->prefix('superadmin')->group(function () {
        Route::get('/{section?}', [SuperAdminController::class, 'index'])
            ->name('superadmin')
            ->where('section', '[a-zA-Z0-9_]+');

        Route::get('/rental-rates', [SuperAdminViewsController::class, 'rentalRates'])->name('superadmin.rental-rates');
        Route::get('/utility-rates', [SuperAdminViewsController::class, 'utilityRates'])->name('superadmin.utility-rates');
        Route::get('/schedules', [SuperAdminViewsController::class, 'schedules'])->name('superadmin.schedules');
        Route::get('/billing-settings', [SuperAdminViewsController::class, 'billingSettings'])->name('superadmin.billing-settings');
        Route::get('/sms-templates', [SuperAdminViewsController::class, 'smsTemplates'])->name('superadmin.sms-templates');
        Route::get('/edit-requests', [SuperAdminViewsController::class, 'editRequests'])->name('superadmin.edit-requests');
        Route::get('/system-users', [SuperAdminViewsController::class, 'systemUsers'])->name('superadmin.system-users');
    });


    Route::get('/printing/{user}/print/{month}', [NotificationController::class, 'print'])->name('printing.print');
    Route::get('/printing/bulk-print', [NotificationController::class, 'bulkPrint'])->name('printing.bulk-print');

    //In-App and SMS Notification
    Route::middleware(['auth'])->group(function () {
        Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    });

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [LoginController::class, 'login']);

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])
        ->middleware('guest')
        ->name('password.request');

    // This route HANDLES the form submission to send the SMS
    Route::post('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetSms'])
        ->middleware('guest')
        ->name('password.sms'); // We give it a new, clear name

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');

    Route::get('/db-test', function () {
        try {
            DB::connection()->getPdo();
            return "✅ Connected to MySQL! Database: " . DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            return "❌ Database connection failed: " . $e->getMessage();
        }
    });

    Route::get('/send-sms', [VendorController::class, 'sendSms']);

    Route::middleware(['auth', 'role:Admin'])->prefix('api/admin')->group(function () {
        Route::apiResource('/system-users', SystemUserController::class);
        Route::get('/roles', [RoleController::class, 'index']);
    });
