<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class AdminCommandController extends Controller
{
    /**
     * Execute a single admin command
     * 
     * Security improvements:
     * - Uses POST instead of GET (secret not in URL)
     * - Rate limiting (max 5 requests per minute per IP)
     * - IP whitelist check (optional, via env)
     * - Proper logging
     */
    public function runCommand(Request $request, $command)
    {
        // Rate limiting: max 5 requests per minute per IP
        $key = 'admin-command:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Log::warning('Admin command rate limit exceeded', [
                'ip' => $request->ip(),
                'command' => $command
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.'
            ], 429);
        }
        RateLimiter::hit($key, 60); // 60 seconds window

        // Security check - secret must be in request body, not URL
        $secret = $request->input('secret');
        if ($secret !== env('ADMIN_SECRET')) {
            Log::warning('Invalid admin command secret attempt', [
                'ip' => $request->ip(),
                'command' => $command
            ]);
            abort(403, 'Unauthorized - Invalid secret key!');
        }

        // Optional IP whitelist check
        $allowedIPs = env('ADMIN_ALLOWED_IPS', '');
        if (!empty($allowedIPs)) {
            $allowedIPsArray = array_map('trim', explode(',', $allowedIPs));
            if (!in_array($request->ip(), $allowedIPsArray)) {
                Log::warning('Admin command from unauthorized IP', [
                    'ip' => $request->ip(),
                    'command' => $command
                ]);
                abort(403, 'Unauthorized - IP not whitelisted!');
            }
        }

        $result = ['success' => false, 'message' => ''];
        
        try {
            // Whitelist of allowed commands
            $allowedCommands = [
                'billing:generate',
                'sms:send-billing-statements',
                'sms:send-overdue-alerts',
                'sms:send-payment-reminders'
            ];

            if (!in_array($command, $allowedCommands)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown or unauthorized command: ' . $command
                ], 400);
            }

            Artisan::call($command);
            $output = Artisan::output();
            
            Log::info('Admin command executed', [
                'command' => $command,
                'ip' => $request->ip()
            ]);

            $result = [
                'success' => true,
                'message' => "Command '{$command}' executed successfully!",
                'output' => $output
            ];
        } catch (\Exception $e) {
            Log::error('Admin command execution failed', [
                'command' => $command,
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            
            $result = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
        
        return response()->json($result);
    }

    /**
     * Run multiple monthly tasks at once
     */
    public function runMonthlyTasks(Request $request)
    {
        // Rate limiting: max 2 requests per hour per IP
        $key = 'admin-monthly-tasks:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 2)) {
            Log::warning('Monthly tasks rate limit exceeded', [
                'ip' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.'
            ], 429);
        }
        RateLimiter::hit($key, 3600); // 1 hour window

        // Security check
        $secret = $request->input('secret');
        if ($secret !== env('ADMIN_SECRET')) {
            Log::warning('Invalid monthly tasks secret attempt', [
                'ip' => $request->ip()
            ]);
            abort(403, 'Unauthorized - Invalid secret key!');
        }

        // Optional IP whitelist check
        $allowedIPs = env('ADMIN_ALLOWED_IPS', '');
        if (!empty($allowedIPs)) {
            $allowedIPsArray = array_map('trim', explode(',', $allowedIPs));
            if (!in_array($request->ip(), $allowedIPsArray)) {
                Log::warning('Monthly tasks from unauthorized IP', [
                    'ip' => $request->ip()
                ]);
                abort(403, 'Unauthorized - IP not whitelisted!');
            }
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

            Log::info('Monthly tasks executed', [
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'All monthly tasks completed successfully!',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Monthly tasks execution failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error during monthly tasks: ' . $e->getMessage(),
                'results' => $results
            ], 500);
        }
    }
}

