<?php

namespace App\Http\Controllers\TimetableEntry;

use App\Http\Controllers\Controller;
use App\Models\{
    AcademicYear,
    TimetableEntry,
    TimeSlot,
    Section
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // 🔹 Find TimeSlot early (we need its day_of_week, start_time, end_time)
        $timeSlot = TimeSlot::find($request->input('time_slot_id'));
        if (!$timeSlot) {
            return back()->withErrors([
                'time_slot_id' => 'The selected time slot is invalid.'
            ])->withInput();
        }

        // 🔹 Auto-fill program_id from section (if available)
        $section = Section::find($request->input('section_id'));
        $programId = $section?->program_id;

        // 🔹 Validate incoming request
        $validated = $request->validate([
            'id'               => 'nullable|exists:timetable_entries,id',
            'academic_year_id' => [
                'required', 'exists:academic_years,id',
                function ($attr, $val, $fail) {
                    $academicYear = AcademicYear::find($val);
                    if (!$academicYear || !$academicYear->isActive()) {
                        $fail('The selected academic year must be active.');
                    }
                }
            ],
            'semester_id'    => 'required|exists:semesters,id',
            'section_id'     => 'nullable|exists:sections,id',
            'classroom_id'   => 'sometimes|exists:classrooms,id',
            'subject_id'     => 'required|exists:subjects,id',
            'teacher_ids'    => 'nullable|array',
            'teacher_ids.*'  => 'nullable|exists:teachers,id',
            'time_slot_id'   => 'required|exists:time_slots,id',
        ]);

        // 🔹 Database Transaction ensures atomic safety
        return DB::transaction(function () use ($validated, $timeSlot, $programId) {

            $existingSlot = TimetableEntry::where('section_id', $validated['section_id'])
                ->where('time_slot_id', $validated['time_slot_id'])
                ->where('day_of_week', $timeSlot->day_of_week)
                ->first();

            $teacherIds = $validated['teacher_ids'] ?? [];

            // 🧩 Common data fields for creation/update
            $commonData = [
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id'      => $validated['semester_id'],
                'section_id'       => $validated['section_id'],
                'classroom_id'     => $validated['classroom_id'] ?? null,
                'subject_id'       => $validated['subject_id'],
                'program_id'       => $programId,
                'time_slot_id'     => $validated['time_slot_id'],
                'start_time'       => $timeSlot->start_time,
                'end_time'         => $timeSlot->end_time,
                'day_of_week'      => $timeSlot->day_of_week,
            ];

            // 🟢 CREATE MODE
            if (empty($validated['id'])) {
                if ($existingSlot) {
                    throw ValidationException::withMessages([
                        'subject_id' => 'This section already has a subject assigned for this period. Edit or substitute instead.'
                    ]);
                }

                // Teacher conflict check
                if (!empty($teacherIds)) {
                    $conflict = TimetableEntry::where('day_of_week', $timeSlot->day_of_week)
                        ->where('time_slot_id', $timeSlot->id)
                        ->whereHas('teachers', fn($q) => $q->whereIn('teachers.id', $teacherIds))
                        ->exists();

                    if ($conflict) {
                        throw ValidationException::withMessages([
                            'teacher_ids' => 'One or more selected teachers are already assigned during this time.'
                        ]);
                    }
                }

                $timetable = TimetableEntry::create($commonData);
                $timetable->teachers()->sync($teacherIds);

                return to_route('timetable_entry:all')
                    ->with('toast', '✅ Timetable entry created successfully!');
            }

            // 🟡 UPDATE MODE
            $current = TimetableEntry::find($validated['id']);
            if (!$current) {
                throw ValidationException::withMessages([
                    'id' => 'The timetable entry you want to update was not found.'
                ]);
            }

            // Substitute logic: If another entry already occupies the slot, update that one instead
            if ($existingSlot && $existingSlot->id !== $current->id) {

                // Prevent teacher conflicts
                if (!empty($teacherIds)) {
                    $conflict = TimetableEntry::where('day_of_week', $timeSlot->day_of_week)
                        ->where('time_slot_id', $timeSlot->id)
                        ->whereHas('teachers', fn($q) => $q->whereIn('teachers.id', $teacherIds))
                        ->where('id', '!=', $existingSlot->id)
                        ->exists();

                    if ($conflict) {
                        throw ValidationException::withMessages([
                            'teacher_ids' => 'One or more selected teachers are already assigned during this time.'
                        ]);
                    }
                }

                // Replace occupant with new subject
                $existingSlot->update($commonData);
                $existingSlot->teachers()->sync($teacherIds);

                // Delete the original record
                $current->teachers()->detach();
                $current->delete();

                return to_route('timetable_entry:all')
                    ->with('toast', '✅ Timetable entry substituted into occupied slot successfully!');
            }

            // 🧭 Otherwise, simple update of the same entry
            if (!empty($teacherIds)) {
                $conflict = TimetableEntry::where('day_of_week', $timeSlot->day_of_week)
                    ->where('time_slot_id', $timeSlot->id)
                    ->whereHas('teachers', fn($q) => $q->whereIn('teachers.id', $teacherIds))
                    ->where('id', '!=', $current->id)
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'teacher_ids' => 'One or more selected teachers are already assigned during this time.'
                    ]);
                }
            }

            $current->update($commonData);
            $current->teachers()->sync($teacherIds);

            return to_route('timetable_entry:all')
                ->with('toast', '✅ Timetable entry updated successfully!');
        }, 3);
    }
}
