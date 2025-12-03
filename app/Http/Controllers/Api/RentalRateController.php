<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Stall; // It's better to use the model
use App\Services\AuditLogger;

class RentalRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Stall::query()
            ->join('sections as sec', 'stalls.section_id', '=', 'sec.id')
            ->select(
                'stalls.id',
                'sec.name as section',
                'stalls.table_number as tableNumber',
                'stalls.daily_rate as dailyRate',
                'stalls.monthly_rate as monthlyRate',
                'stalls.area' // ✅ FIX #1: Added 'area' to the data being fetched
            );

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('stalls.table_number', 'LIKE', "%{$searchTerm}%");
        }

        if ($request->filled('section')) {
            $sectionName = $request->input('section');
            $query->where('sec.name', $sectionName);
        }

        if ($request->filled('section')) {
            $rates = $query->orderBy('stalls.id')->paginate(15);
        } else {
            $allRates = $query->orderBy('stalls.id')->get();
            return response()->json(['data' => $allRates]);
        }

        return response()->json($rates);
    }

    /**
     * Get the next available table number for a given section.
     */
    public function getNextTableNumber($sectionName)
    {
        $section = DB::table('sections')->where('name', $sectionName)->first();

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $maxTableNumber = DB::table('stalls')
            ->where('section_id', $section->id)
            ->max(DB::raw("CAST(REGEXP_SUBSTR(table_number, '[0-9]+$') AS UNSIGNED)"));

        return response()->json(['next_table_number' => ((int)$maxTableNumber) + 1]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'section' => 'required|string|exists:sections,name',
            'tableNumber' => 'required|string|max:255',
            'dailyRate' => 'nullable|numeric|min:0',
            // 'monthlyRate' => 'nullable|numeric|min:0', // <-- REMOVE THIS LINE
            'area' => 'nullable|numeric|min:0',
        ]);

        $section = DB::table('sections')->where('name', $validatedData['section'])->first();

        $stallId = DB::table('stalls')->insertGetId([
            'section_id' => $section->id,
            'table_number' => $validatedData['tableNumber'],
            'daily_rate' => $validatedData['dailyRate'] ?? 0.00,
            // 'monthly_rate' is now calculated by the DB, so we remove it here
            'area' => $validatedData['area'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditLogger::log(
            'Created Stall',
            'Rental Rates',
            'Success',
            ['stall_id' => $stallId, 'section' => $validatedData['section'], 'table_number' => $validatedData['tableNumber']]
        );

        return response()->json(['message' => 'Stall created successfully', 'id' => $stallId], 201);
    }

    /**
     * Update multiple stall rates at once.
     */
// In RentalRateController.php -> batchUpdate()
    public function batchUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'stalls' => 'required|array',
            'stalls.*.id' => 'required|integer|exists:stalls,id',
            'stalls.*.tableNumber' => 'required|string',
            'stalls.*.dailyRate' => 'required|numeric',
            // 'stalls.*.monthlyRate' => 'required|numeric', // <-- REMOVE THIS LINE
            'stalls.*.area' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validatedData) {
            foreach ($validatedData['stalls'] as $stallData) {
                $stall = Stall::find($stallData['id']);
                if ($stall) {
                    $stall->update([
                        'table_number' => $stallData['tableNumber'],
                        'daily_rate' => $stallData['dailyRate'],
                        // 'monthly_rate' => $stallData['monthlyRate'], // <-- REMOVE THIS LINE
                        'area' => $stallData['area'],
                    ]);
                }
            }
            
            AuditLogger::log(
                'Updated Rental Rates',
                'Rental Rates',
                'Success',
                ['count' => count($validatedData['stalls']), 'changes' => $validatedData['stalls']]
            );
        });

        return response()->json(['message' => 'Rates updated successfully!']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stall = DB::table('stalls')->where('id', $id)->first();
        if ($stall) {
            AuditLogger::log(
                'Deleted Stall',
                'Rental Rates',
                'Success',
                ['stall_id' => $id, 'table_number' => $stall->table_number]
            );
            DB::table('stalls')->where('id', $id)->delete();
        }
        return response()->json(null, 204);
    }
}
