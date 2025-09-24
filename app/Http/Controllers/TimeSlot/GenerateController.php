<?php

namespace App\Http\Controllers\TimeSlot;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\AcademicYear;
use App\Models\TimeSlotTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'clear_existing' => 'boolean',
        ]);

        $academicYearId = $request->academic_year_id;

        // Check if time slots already exist for this academic year
        $existingSlots = TimeSlot::where('academic_year_id', $academicYearId)->count();

        if ($existingSlots > 0 && !$request->clear_existing) {
            return back()->withErrors([
                'generation' => 'Time slots already exist for this academic year. Check "Clear existing slots" to replace them.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Clear existing slots if requested
            if ($request->clear_existing && $existingSlots > 0) {
                TimeSlot::where('academic_year_id', $academicYearId)->delete();
            }

            // Get time slot templates from database
            $templates = TimeSlotTemplate::active()->ordered()->get();

            if ($templates->isEmpty()) {
                return back()->withErrors([
                    'generation' => 'No active time slot templates found. Please configure time slot templates first.'
                ]);
            }

            // Days of the week (Monday to Friday only - no weekends)
            $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

            $createdCount = 0;

            // Create time slots for each day using templates
            foreach ($daysOfWeek as $day) {
                foreach ($templates as $template) {
                    TimeSlot::create([
                        'name' => $template->name,
                        'academic_year_id' => $academicYearId,
                        'day_of_week' => $day,
                        'start_time' => $template->start_time,
                        'end_time' => $template->end_time,
                        'is_lunch_period' => $template->is_lunch_period,
                        'status' => 'active',
                    ]);
                    $createdCount++;
                }
            }

            DB::commit();

            return to_route('time-slot:all')->with('toast', "Successfully generated {$createdCount} time slots for weekdays (Monday-Friday)!");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors([
                'generation' => 'Failed to generate time slots: ' . $e->getMessage()
            ]);
        }
    }
}
