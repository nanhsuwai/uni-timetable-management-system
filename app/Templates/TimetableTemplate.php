<?php

namespace App\Templates;

use App\Traits\MPDFTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class TimetableTemplate
{
    protected $entries;
    protected $timeSlots;
    protected $academicYear;
    protected $semester;
    protected $program;
    protected $level;
    protected $section;
    protected $template;
    protected $mpdf;
    protected $excelData;

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

        $this->excelData = $this->getTimetableDataForExport();

        // 🟢 PDF Setup
        $this->template = File::get(storage_path('templates/GenerateTimetable.html'));
        $configs = [
            'margin_left' => 10,
            'margin_header' => 5,
            'margin_right' => 10,
            'margin_top' => 5,
            'margin_bottom' => 15, // extra space for footer
            'format' => 'A4',
            'orientation' => 'P',
            'defaultPageNumStyle' => 'myanmar'
        ];
        $this->mpdf = $this->mpdf($configs);

        $this->prepareTimetable();
        $this->mpdf->shrink_tables_to_fit = 0;
        $this->mpdf->keep_table_proportions = true;
        $this->mpdf->SetFooter([
            'L' => 'Developed by Nan Hsu Wai',
            'C' => '',
            'R' => '{PAGENO}',
        ]);
    }

    /** ✅ Excel Data for Export */
    protected function getTimetableDataForExport(): Collection
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timeSlots = $this->timeSlots->pluck('start_time')->unique()->sort();

        $headers = ['Day\Time'];
        foreach ($timeSlots as $time) {
            $slot = $this->timeSlots->firstWhere('start_time', $time);
            $end = $slot?->end_time ?? '';
            $headers[] = date('H:i', strtotime($time)) . ' - ' . date('H:i', strtotime($end));
        }
        $data = [$headers];

        foreach ($days as $day) {
            $row = ['Day' => $day];
            foreach ($timeSlots as $time) {
                $entry = $this->entries->first(function ($e) use ($day, $time) {
                    return $e->timeSlot->day_of_week == strtolower($day) && $e->timeSlot->start_time == $time;
                });

                $slot = $this->timeSlots->firstWhere('start_time', $time);
                $isLunch = $slot?->is_lunch_period ?? false;

                if ($isLunch) {
                    $content = 'LUNCH BREAK';
                } elseif ($entry) {
                    $subjectName = $entry->subject?->code ?? 'Unknown';
                    /* $teacherNames = $entry->teachers->pluck('name')->implode(', '); */
                    $content = "{$subjectName}"; /* ({$teacherNames}) */
                } else {
                    $content = 'Lab/Libraryအားကစား/ဂီတ';
                }
                $row[] = $content;
            }
            $data[] = $row;
        }

        return collect($data);
    }

    /** ✅ Prepare HTML for PDF */
    protected function prepareTimetable()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timeSlots = $this->timeSlots->pluck('start_time')->unique()->sort();

        // 🏫 Header Section
        $html  = '<div style="display:flex; align-items:center; justify-content:space-between; border-bottom:2px solid #00B9C4; padding-bottom:6px; margin-bottom:10px;">';

        $html .= '<div style="flex:0 0 auto;">';
        $html .= '<img src="' . public_path('images/logo.png') . '" alt="University Logo" style="width:65px; height:auto;">';
        $html .= '</div>';

        $html .= '<div style="flex:1; text-align:center;">';
        $html .= '<h1 style="color:#00B9C4; font-size:20px; margin:0;">University of Computer Studies, Hinthada</h1>';
        $html .= '<p style="color:#00B9C4; margin:4px 0;">Academic Year: ' . $this->academicYear->name;
        if ($this->semester) $html .= ' (' . $this->semester->name . ')';
        $html .= '</p>';

        $html .= '<p style="color:#00B9C4; margin:4px 0;">Timetable for ';
        if ($this->level) $html .= $this->level->name . ' ';
        if ($this->program) $html .= '(' . $this->program->name . ') ';
        if (!empty($this->section) && !empty($this->section->name)) {
            $html .= '(Section: ' . $this->section->name . ')';
        }
        $html .= '</p>';
        $html .= '</div>';
        $html .= '</div>';
        if ($this->section && $this->section->classroom)
            $html .= '<p style="text-align:right; font-size:12px; margin-top:5px;">Room - ' . $this->section->classroom->room_no . '</p>';


        // 🧾 Timetable Table
        $html .= '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr>';
        $html .= '<th style="border: 1px solid #ccc; padding: 8px; background-color: #f3f4f6;">Day\Time</th>';

        foreach ($timeSlots as $time) {
            $slot = $this->timeSlots->firstWhere('start_time', $time);
            $end = $slot?->end_time ?? '';
            $isLunch = $slot?->is_lunch_period ?? false;
            $bg = $isLunch ? '#fde68a' : '#f9fafb';
            $html .= '<th style="border: 1px solid #ccc; padding: 8px; background:' . $bg . '; text-align:center;">' .
                date('h:i A', strtotime($time)) . ' - ' . date('h:i A', strtotime($end)) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($days as $day) {
            $html .= '<tr>';
            $html .= '<td style="border:1px solid #ccc; padding:8px; text-align:center; font-weight:300;">' . $day . '</td>';

            foreach ($timeSlots as $time) {
                $entry = $this->entries->first(function ($e) use ($day, $time) {
                    return $e->timeSlot->day_of_week == strtolower($day) && $e->timeSlot->start_time == $time;
                });
                $slot = $this->timeSlots->firstWhere('start_time', $time);
                $isLunch = $slot?->is_lunch_period ?? false;
                $bgColor = $isLunch ? '#fff7ed' : '#ffffff';
                $html .= '<td style="border:1px solid #ccc; padding:8px; text-align:center; background:' . $bgColor . ';">';

                if ($isLunch) {
                    $html .= '<b>LUNCH BREAK</b>';
                } elseif ($entry) {
                    $subject = $entry->subject?->code ?? 'Unknown';
                    $teacherNames = $entry->teachers->pluck('name')->implode(', ');
                    $roomNo = $entry->classroom?->room_no ?? 'No room';
                    $html .= "<div style='font-weight:600; color:#005975;'>$subject</div>";
                } else {
                    $html .= '<i style="color:#1CADA1;">Library/Lab<br>အားကစား/ဂီတ </i>';
                }
                $html .= '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        // 👩‍🏫 Section Head Teacher
        if ($this->section && $this->section->sectionHeadTeacher) {
            $html .= '<p style="margin-top:12px; font-weight:600;"> သင်တန်းမှူး - ' . $this->section->sectionHeadTeacher->name . '</p>';
        }

        // 📘 Subject Codes + Teachers
        $uniqueSubjects = collect($this->entries)->pluck('subject')->unique('id')->filter();

        if ($uniqueSubjects->isNotEmpty()) {

            $html .= '<table style="width:100%; border:2px; border-collapse: collapse; margin-top:6px;">';
            $html .= '<thead><tr>
                <th style="border:1px solid #ccc; padding:6px; background:#f3f4f6;">Code</th>
                <th style="border:1px solid #ccc; padding:6px; background:#f3f4f6;">Subject Name</th>
                <th style="border:1px solid #ccc; padding:6px; background:#f3f4f6;">Teacher Name(s)</th>
            </tr></thead><tbody>';

            foreach ($uniqueSubjects as $subject) {
                $relatedEntries = $this->entries->where('subject_id', $subject->id);
                $teacherNames = $relatedEntries->flatMap(fn($e) => $e->teachers->pluck('name'))->unique()->implode(' ၊ ');
                $html .= '<tr>
                    <td style="border:1px solid #ccc; padding:6px; font-size:12px;">' . $subject->code . '</td>
                    <td style="border:1px solid #ccc; text-align:left; padding:6px; font-size:12px;">' . $subject->name . '</td>
                    <td style="border:1px solid #ccc; text-align:left; padding:6px; font-size:12px;">' . $teacherNames . '</td>
                </tr>';
            }

            $html .= '</tbody></table>';
        }

        // ✍️ Footer Signature
        $html .= '
<div style="width:100%; text-align:right; font-size:12px; margin-top:190px;">
    Date: ' . date('d/m/Y') . '
</div>

<div style="width:100%; text-align:center; font-weight:bold; font-size:14px; margin-top:20px;">
    University Timetable Management System
</div>
';



        $this->template = str_replace('###TIMETABLE###', $html, $this->template);
    }

    public function generate()
    {
        $this->mpdf->WriteHTML($this->template);
        return $this->mpdf->Output('timetable.pdf', 'S');
    }

    public function generateExcelData(): Collection
    {
        return $this->excelData;
    }
}
