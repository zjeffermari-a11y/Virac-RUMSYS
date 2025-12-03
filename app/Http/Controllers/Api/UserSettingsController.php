<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class UserSettingsController extends Controller
{
    public function getRoleContacts()
    {
        $roles = ['Admin', 'Meter Reader Clerk'];
        $contacts = DB::table('users as u')
            ->join('roles as r', 'u.role_id', '=', 'r.id')
            ->whereIn('r.name', $roles)
            ->select('u.id', 'r.name as role_name', 'u.contact_number')
            ->get();

        return response()->json($contacts);
    }

    public function updateRoleContacts(Request $request)
    {
        $validated = $request->validate([
            'contacts' => 'required|array',
            'contacts.*.id' => 'required|integer|exists:users,id',
            'contacts.*.contact_number' => 'nullable|string|max:20',
        ]);

        foreach ($validated['contacts'] as $contact) {
            DB::table('users')
                ->where('id', $contact['id'])
                ->update(['contact_number' => $contact['contact_number']]);
        }

        AuditLogger::log(
            'Updated Role Contacts',
            'User Settings',
            'Success',
            ['changes' => $validated['contacts']]
        );

        return response()->json(['message' => 'Contact numbers updated successfully.']);
    }
}