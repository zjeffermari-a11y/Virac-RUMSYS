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
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Response;
    //--For CRON JOBS--//
    // Secured admin command routes - moved to controller with improved security
    use App\Http\Controllers\AdminCommandController;
    
    // Serve storage files (fallback if symbolic link doesn't exist)
    Route::get('/storage/{path}', function ($path) {
        $filePath = storage_path('app/public/' . $path);
        if (file_exists($filePath) && is_file($filePath)) {
            return Response::file($filePath);
        }
        abort(404);
    })->where('path', '.*')->name('storage.serve');
    
    // POST routes (secret in body, not URL) with rate limiting
    Route::post('/admin/run-command/{command}', [AdminCommandController::class, 'runCommand'])
        ->name('admin.run-command');
    
    Route::post('/admin/run-monthly-tasks', [AdminCommandController::class, 'runMonthlyTasks'])
        ->name('admin.run-monthly-tasks');
    
    // Legacy GET routes (deprecated - will be removed in future version)
    // Kept for backward compatibility but should use POST instead
    Route::get('/admin/run-command/{command}', function ($command) {
        \Log::warning('Deprecated GET route used for admin command', [
            'command' => $command,
            'ip' => request()->ip()
        ]);
        return response()->json([
                        'success' => false,
            'message' => 'This route is deprecated. Please use POST method instead.'
        ], 400);
    });
    
    Route::get('/admin/run-monthly-tasks', function () {
        \Log::warning('Deprecated GET route used for monthly tasks', [
            'ip' => request()->ip()
        ]);
            return response()->json([
                'success' => false,
            'message' => 'This route is deprecated. Please use POST method instead.'
        ], 400);
    });
    
    // Dashboard to show all available commands (optional - for convenience)
    // Note: Commands should be executed via POST with secret in request body
    Route::get('/admin/commands-dashboard', function () {
        // Security check
        if (request()->input('secret') !== env('ADMIN_SECRET')) {
            abort(403, 'Unauthorized - Invalid secret key!');
        }
        
        return response()->json([
            'message' => 'Available Commands',
            'note' => 'Use POST method to execute commands. Secret should be in request body, not URL.',
            'endpoints' => [
                'Run All Monthly Tasks' => [
                    'method' => 'POST',
                    'url' => url('/admin/run-monthly-tasks'),
                    'body' => ['secret' => 'YOUR_SECRET_KEY']
                ],
                'Generate Monthly Bills' => [
                    'method' => 'POST',
                    'url' => url('/admin/run-command/billing:generate'),
                    'body' => ['secret' => 'YOUR_SECRET_KEY']
                ],
                'Send Billing Statements' => [
                    'method' => 'POST',
                    'url' => url('/admin/run-command/sms:send-billing-statements'),
                    'body' => ['secret' => 'YOUR_SECRET_KEY']
                ],
                'Send Overdue Alerts' => [
                    'method' => 'POST',
                    'url' => url('/admin/run-command/sms:send-overdue-alerts'),
                    'body' => ['secret' => 'YOUR_SECRET_KEY']
                ],
                'Send Payment Reminders' => [
                    'method' => 'POST',
                    'url' => url('/admin/run-command/sms:send-payment-reminders'),
                    'body' => ['secret' => 'YOUR_SECRET_KEY']
                ],
            ],
            'security_note' => 'GET routes are deprecated and will return an error. Use POST with secret in request body.'
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
        Route::get('/analytics', [VendorController::class, 'analytics'])->name('api.vendor.analytics');
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
    });


    Route::get('/printing/{user}/print/{month}', [NotificationController::class, 'print'])->name('printing.print');
    Route::get('/printing/bulk-print', [NotificationController::class, 'bulkPrint'])->name('printing.bulk-print');

    //In-App and SMS Notification
    Route::middleware(['auth'])->group(function () {
        Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
        Route::get('/notifications/fetch-all', [NotificationController::class, 'fetchAll'])->name('notifications.fetchAll');
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markNotificationAsRead'])->name('notifications.markNotificationAsRead');
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
