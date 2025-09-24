<?php

namespace App\Http\Controllers\TimeSlotTemplate;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    public function __invoke(Request $request, TimeSlotTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'period_number' => 'required|integer|min:1|unique:time_slot_templates,period_number,' . $template->id,
            'is_lunch_period' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $template->update($validator->validated());

        return to_route('time-slot:all')->with('toast', 'Time slot template updated successfully!');
    }
}
