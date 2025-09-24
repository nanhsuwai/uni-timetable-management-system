<?php

namespace App\Http\Controllers\TimeSlotTemplate;

use App\Http\Controllers\Controller;
use App\Models\TimeSlotTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'period_number' => 'required|integer|min:1|unique:time_slot_templates,period_number',
        ]);

        if ($validator->fails()) {
             return back()->withInput()->withErrors([
                 'errors' => $validator->errors()->all()
             ]);
        }

        $timeSlotTemplate = TimeSlotTemplate::create($validator->validated());

        return response()->json([
            'message' => 'Time slot template created successfully',
            'data' => $timeSlotTemplate
        ], 201);
    }
}
