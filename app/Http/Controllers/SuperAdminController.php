<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\DashboardController as DashboardController;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Stall;
use App\Models\Section;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\ReadingEditRequest;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\RentalRateController;
use App\Http\Controllers\Api\UtilityRateController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BillingSettingsController;
use App\Http\Controllers\Api\NotificationTemplateController;
use App\Http\Controllers\Api\ReadingEditRequestController;
use App\Http\Controllers\Api\SystemUserController;
use App\Http\Controllers\Api\UserSettingsController;

class SuperAdminController extends Controller
{
    /**
     * Display the super admin dashboard.
     * This method now prepares and passes all initial data for the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Instantiate all the necessary controllers
        $dashboardApiController = new DashboardController();
        $rentalRateController = new RentalRateController();
        $utilityRateController = new UtilityRateController();
        $scheduleController = new ScheduleController();
        $billingSettingsController = new BillingSettingsController();
        $notificationTemplateController = new NotificationTemplateController();
        $readingEditRequestController = new ReadingEditRequestController();
        $systemUserController = new SystemUserController();
        $userSettingsController = new UserSettingsController();
        
        // Create a single Request object to be passed to the controller methods
        $request = new Request();

        // Fetch all the data, passing the $request object where needed
        $kpis = $dashboardApiController->getKpis($request)->getData(true);
        $vendorDistribution = $dashboardApiController->getVendorDistribution()->getData(true);
        $collectionTrends = $dashboardApiController->getCollectionTrends($request)->getData(true);
        $utilityConsumption = $dashboardApiController->getUtilityConsumption($request)->getData(true);
        $topPerformers = $dashboardApiController->getTopPerformingVendors($request)->getData(true);
        $vendorsNeedingSupport = $dashboardApiController->getVendorsNeedingSupport($request)->getData(true);
        
        $years = Billing::select(DB::raw('YEAR(period_start) as year'))
            ->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years->push(now()->year);
        }
        $sections = Section::all(['name', 'id']);

        // Fetch data for all other sections, passing the $request object
        $rentalRates = $rentalRateController->index($request)->getData(true);
        $utilityRates = $utilityRateController->index($request)->getData(true);
        $utilityRateHistory = $utilityRateController->history($request)->getData(true);
        $meterReadingSchedule = $scheduleController->show($request)->getData(true);
        $meterReadingHistory = $scheduleController->history($request)->getData(true);
        $billingDates = $scheduleController->getBillingDates($request)->getData(true);
        $billingDatesHistory = $scheduleController->getBillingDatesHistory($request)->getData(true);
        $billingSettings = $billingSettingsController->index($request)->getData(true);
        $billingSettingsHistory = $billingSettingsController->history($request)->getData(true);
        $notificationTemplates = $notificationTemplateController->index($request)->getData(true);
        $editRequests = $readingEditRequestController->index($request)->getData(true);
        
        // Fetch all system users for client-side pagination
        $systemUsers = $systemUserController->index($request)->getData(true);
        
        $smsSettings = $userSettingsController->getRoleContacts()->getData(true);
        $smsSchedules = $scheduleController->getSmsSchedules($request)->getData(true);
        $smsScheduleHistory = $scheduleController->getSmsScheduleHistory($request)->getData(true);



        $initialState = [
            'kpis' => $kpis,
            'vendorDistribution' => $vendorDistribution,
            'collectionTrends' => $collectionTrends,
            'utilityConsumption' => $utilityConsumption,
            'vendorPulse' => [
                'topPerformers' => $topPerformers,
                'needsSupport' => $vendorsNeedingSupport['data'],
            ],
            'filterData' => [
                'years' => $years,
                'sections' => $sections,
            ],
            // Add all the new data to the initial state
            'rentalRates' => $rentalRates,
            'utilityRates' => $utilityRates,
            'utilityRateHistory' => $utilityRateHistory,
            'meterReadingSchedule' => $meterReadingSchedule,
            'meterReadingHistory' => $meterReadingHistory,
            'billingDates' => $billingDates,
            'billingDatesHistory' => $billingDatesHistory,
            'billingSettings' => $billingSettings,
            'billingSettingsHistory' => $billingSettingsHistory,
            'notificationTemplates' => $notificationTemplates,
            'editRequests' => $editRequests,
            'systemUsers' => $systemUsers,
            'smsSettings' => $smsSettings,
            'smsSchedules' => $smsSchedules,
            'smsScheduleHistory' => $smsScheduleHistory,
        ];

        return view('superadmin_portal.superadmin', compact('initialState'));
    }
}