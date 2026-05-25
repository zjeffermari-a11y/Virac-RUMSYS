<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;

class SystemUserController extends Controller
{
    /**
     * Display a paginated list of users with search and filter.
     */
    public function index(Request $request)
    {
        $query = DB::table('users as u')
            ->join('roles as r', 'u.role_id', '=', 'r.id')
            // Use a LEFT JOIN to get stall information if it exists.
            ->leftJoin('stalls as s', 'u.id', '=', 's.vendor_id')
            ->select(
                'u.id',
                'r.name as role',
                'u.name',
                'u.username',
                'u.last_login',
                'u.status',
                'u.role_id',
                'u.contact_number',
                'u.application_date',
            )
            ->where('r.name', '!=', 'Admin');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('u.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('u.username', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('u.role_id', $request->input('role'));
        }

        // Get all users for client-side pagination
        $users = $query->orderBy('u.created_at', 'desc')->get();

        // Transform the last_login to an ISO-8601 string for correct JS parsing
        $users->transform(function ($user) {
            if ($user->last_login) {
                $user->last_login = Carbon::parse($user->last_login)->toIso8601String();
            }
            return $user;
        });

        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Find the ID for the 'Vendor' role.
        $vendorRole = DB::table('roles')->where('name', 'Vendor')->first();
        $vendorRoleId = $vendorRole ? $vendorRole->id : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'role_id' => 'required|integer|exists:roles,id',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
            'contact_number' => ['nullable', 'string', 'size:11', 'regex:/^09\d{9}$/'],
            'application_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $userId = DB::table('users')->insertGetId([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'role_id' => $validated['role_id'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'contact_number' => $validated['contact_number'],
                'application_date' => $validated['application_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            AuditLogger::log(
                'Created new user',
                'System User Management',
                'Success',
                ['name' => $validated['name'], 'username' => $validated['username'], 'role_id' => $validated['role_id']]
            );

            DB::commit(); // All good, commit the transaction

            return response()->json(['message' => 'User created successfully.', 'user_id' => $userId], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Something went wrong, rollback the transaction
            \Log::error('User creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the user.'], 500);
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the ID for the 'Vendor' role.
        $vendorRole = DB::table('roles')->where('name', 'Vendor')->first();
        $vendorRoleId = $vendorRole ? $vendorRole->id : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($id)],
            'role_id' => 'required|integer|exists:roles,id',
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
            'contact_number' => ['nullable', 'string', 'size:11', 'regex:/^09\d{9}$/'],
            'application_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'role_id' => $validated['role_id'],
                'status' => $validated['status'],
                'contact_number' => $validated['contact_number'],
                'application_date' => $validated['application_date'],
                'updated_at' => now(),
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            DB::table('users')->where('id', $id)->update($updateData);

            AuditLogger::log(
                'Updated user',
                'System User Management',
                'Success',
                ['id' => $id, 'name' => $validated['name'], 'username' => $validated['username'], 'role_id' => $validated['role_id']]
            );

            DB::commit();
            return response()->json(['message' => 'User updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User update failed: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the user.'], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        if ($id == 1 || $id == auth()->id()) {
            return response()->json(['message' => 'This user cannot be deleted.'], 403);
        }
        
        $user = DB::table('users')->where('id', $id)->first();

        if ($user) {
            AuditLogger::log(
                'Deleted user',
                'System User Management',
                'Success',
                ['id' => $id, 'name' => $user->name]
            );
            DB::table('users')->where('id', $id)->delete();
            return response()->json(['message' => 'User deleted successfully.'], 200);
        }

        return response()->json(['message' => 'User not found.'], 404);
    }
}