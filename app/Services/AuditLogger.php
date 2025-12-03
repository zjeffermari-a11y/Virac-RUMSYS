<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Log an action to the audit trail.
     *
     * @param string $action The action performed (e.g., 'Created User', 'Updated Settings')
     * @param string $module The module where the action occurred (e.g., 'User Management', 'Settings')
     * @param string $result The result of the action (e.g., 'Success', 'Failed')
     * @param string|array|null $details Additional details about the action (will be JSON encoded if array)
     * @return void
     */
    public static function log($action, $module, $result = 'Success', $details = null)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                // If no authenticated user (e.g., system task), we might want to log it differently or skip.
                // For now, let's log with a system user ID if available or just log a warning.
                // Assuming this is primarily for user actions.
                Log::warning("AuditLogger: No authenticated user found for action: {$action}");
                return;
            }

            if (is_array($details) || is_object($details)) {
                $details = json_encode($details);
            }

            AuditTrail::create([
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'action' => substr($action, 0, 100), // Ensure it fits
                'module' => substr($module, 0, 50),
                'result' => substr($result, 0, 50),
                'details' => $details,
            ]);

        } catch (\Exception $e) {
            Log::error("AuditLogger Error: " . $e->getMessage());
        }
    }
}
