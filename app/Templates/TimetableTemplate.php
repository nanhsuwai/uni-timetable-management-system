<?php

namespace App\Templates;

use App\Traits\MPDFTrait;
use Illuminate\Support\Facades\File;

class TimetableTemplate
{
    protected $entries;
    protected $timeSlots;
    protected $academicYear;
    protected $semester;
    protected $program;
    protected $level;
    protected $section;
    protected $classroom;
    protected $template;
    protected $mpdf;

    use MPDFTrait;

    public function __construct($entries, $timeSlots, $academicYear, $semester, $program, $level, $section)
    {
        $this->entries = $entries;
        $this->timeSlots = $timeSlots;
        $this->academicYear = $academicYear;
        $this->semester = $semester;
        $this->program = $program;
        $this->level = $level;
        $this->section = $section;

        $this->template = File::get(storage_path('templates/GenerateTimetable.html'));
        $configs = [
            'margin_left' => 10,
            'margin_header' => 5,
            'margin_right' => 10,
            'margin_top' => 5,
            'margin_bottom' => 10,
            'format' => 'A4-L', // Landscape
            'orientation' => 'L',
            'defaultPageNumStyle' => 'myanmar'
        ];
        $this->mpdf = $this->mpdf($configs);

        $this->prepareTimetable();
        $this->mpdf->shrink_tables_to_fit = 0;
        $this->mpdf->keep_table_proportions = true;
        $this->mpdf->AddPage('', '', 1);
    }

    protected function prepareTimetable()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timeSlots = $this->timeSlots->pluck('start_time')->unique()->sort();

        $html = '<h1 style="text-align: center;">University of Computer Studies, Hinthada</h1>';
        $html .= '<h2 style="text-align: center;">Timetable</h2>';
        if ($this->academicYear) {
            $html .= '<p style="text-align: center;">Academic Year: ' . $this->academicYear->name . '</p>';
        }
        $html .= '<p style="text-align: center;">';
        if ($this->semester) $html .= 'Semester: ' . $this->semester->name . ' | ';
        if ($this->program) $html .= 'Program: ' . $this->program->name . ' | ';
        if ($this->level) $html .= 'Level: ' . $this->level->name . ' | ';
        if ($this->section) $html .= 'Section: ' . $this->section->name . ' | ';
        if ($this->section && $this->section->classroom) $html .= 'Classroom: ' . $this->section->classroom->room_no;
        $html .= '</p>';

        $html .= '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr>';
        $html .= '<th style="border: 1px solid #d1d5db; padding: 12px; background-color: #f9fafb; font-weight: 600; font-size: 14px; width: 96px;">Day</th>';
        foreach ($timeSlots as $time) {
            $slot = $this->timeSlots->firstWhere('start_time', $time);
            $isLunch = $slot?->is_lunch_period ?? false;
            $end = $slot?->end_time ?? '';
            $formattedStart = date('h:i A', strtotime($time));
            $formattedEnd = date('h:i A', strtotime($end));
            $bgColor = $isLunch ? '#fed7aa' : '#f9fafb';
            $html .= '<th style="border: 1px solid #d1d5db; padding: 12px; background-color: ' . $bgColor . '; font-weight: 600; font-size: 14px; text-align: center; min-width: 128px;">';
            $html .= $formattedStart . ' - ' . $formattedEnd;
            if ($isLunch) {
                $html .= '<div style="font-size: 12px; color: #9a3412; font-weight: normal;">Lunch</div>';
            }
            $html .= '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($days as $dayIndex => $day) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #d1d5db; padding: 12px; background-color: #f9fafb; font-weight: 600; text-align: center;">' . $day . '</td>';
            foreach ($timeSlots as $time) {
                $entry = $this->entries->first(function($e) use ($day, $time) {
                    return $e->timeSlot->day_of_week == strtolower($day) && $e->timeSlot->start_time == $time;
                });
                $slot = $this->timeSlots->firstWhere('start_time', $time);
                $isLunch = $slot?->is_lunch_period ?? false;
                $bgColor = $isLunch ? '#fff7ed' : '#ffffff';
                $html .= '<td style="border: 1px solid #d1d5db; padding: 8px; text-align: center; min-height: 80px; vertical-align: top; background-color: ' . $bgColor . ';">';
                if ($entry) {
                    $subjectName = $entry->subject ? $entry->subject->code : 'Unknown Subject';
                    $teachers = $entry->teachers ?? collect();
                    $teacherCount = $teachers->count();
                    $teacherDisplay = '';
                    if ($teacherCount > 1) {
                        $teacherDisplay = $teacherCount . ' teachers';
                    } elseif ($teacherCount === 1) {
                        $teacherDisplay = $teachers->first()->name;
                    } else {
                        $teacherDisplay = 'No teacher';
                    }
                    $roomNo = $entry->classroom ? $entry->classroom->room_no : 'No room';
                    $html .= '<div style="font-size: 12px;">';
                    $html .= '<div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">' . $subjectName . '</div>';
                    $html .= '<div style="color: #4b5563; margin-bottom: 4px; font-size: ' . ($teacherCount > 1 ? '12px' : '14px') . ';">' . $teacherDisplay . '</div>';
                    $html .= '</div>';
                } elseif (!$isLunch) {
                    $html .= '<div style="color: #9ca3af; font-size: 12px; font-style: italic;">No class</div>';
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';

        $this->template = str_replace('###TIMETABLE###', $html, $this->template);
    }

    public function generate()
    {
        $this->mpdf->WriteHTML($this->template);
        return $this->mpdf->Output('timetable.pdf', 'S');
    }
}
