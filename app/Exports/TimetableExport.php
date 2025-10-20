<?php

namespace App\Exports;

use App\Models\TimetableEntry;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\AcademicProgram;
use App\Models\AcademicLevel;
use App\Models\Section;
use App\Models\TimeSlot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TimetableExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $request;
    protected $entries;
    protected $referenceData;
    protected $filters;

    public function __construct($request)
    {
        $this->request = $request;
        $this->filters = $request->only([
            'filterYear',
            'filterSemester',
            'filterProgram',
            'filterLevel',
            'filterSection'
        ]);

        // Build query similar to GridViewController
        $query = TimetableEntry::with([
            'academicYear:id,name',
            'semester:id,name',
            'academicProgram:id,name',
            'academicLevel:id,name',
            'section:id,name',
            'section.classroom:id,room_no',
            'classroom:id,room_no',
            'subject:id,name,code',
            'teachers:id,name',
            'timeSlot:id,name,start_time,end_time,day_of_week,academic_year_id,is_lunch_period',
        ]);

        $filters = [
            'filterYear' => 'academic_year_id',
            'filterSemester' => 'semester_id',
            'filterProgram' => 'program_id',
            'filterLevel' => 'level_id',
            'filterSection' => 'section_id',
        ];

        foreach ($filters as $requestKey => $column) {
            if ($request->filled($requestKey)) {
                $query->where($column, $request->$requestKey);
            }
        }

        if ($request->filled('filterYear')) {
            $query->whereHas('timeSlot', function ($q) use ($request) {
                $q->where('academic_year_id', $request->filterYear);
            });
        }

        $this->entries = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        // Get reference data
        $this->referenceData = [
            'academicYears' => AcademicYear::select('id', 'name')->get(),
            'semesters' => Semester::select('id', 'name', 'academic_year_id')->get(),
            'programs' => AcademicProgram::select('id', 'name', 'academic_year_id')->get(),
            'levels' => AcademicLevel::select('id', 'name', 'program_id')->get(),
            'sections' => Section::with(['classroom', 'sectionHeadTeacher'])->get(),
            'timeSlots' => TimeSlot::select('id', 'name', 'start_time', 'end_time', 'day_of_week', 'academic_year_id', 'is_lunch_period')->get(),
        ];
    }

    public function collection()
    {
        // Return empty collection since we'll handle everything in styles and events
        return collect([]);
    }

    public function headings(): array
    {
        return [];
    }

    public function map($entry): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Get selected items for header
        $selectedYear = $this->referenceData['academicYears']->first(fn($y) => $y->id == $this->filters['filterYear']);
        $selectedSemester = $this->referenceData['semesters']->first(fn($s) => $s->id == $this->filters['filterSemester']);
        $selectedProgram = $this->referenceData['programs']->first(fn($p) => $p->id == $this->filters['filterProgram']);
        $selectedLevel = $this->referenceData['levels']->first(fn($l) => $l->id == $this->filters['filterLevel']);
        $selectedSection = $this->referenceData['sections']->first(fn($s) => $s->id == $this->filters['filterSection']);

        // Filter time slots by academic year
        $timeSlots = $this->referenceData['timeSlots'];
        if ($this->filters['filterYear']) {
            $timeSlots = $timeSlots->filter(fn($slot) => $slot->academic_year_id == $this->filters['filterYear']);
        }

        // Get unique time slots
        $uniqueTimeSlots = [];
        $seen = [];
        foreach ($timeSlots as $slot) {
            $key = $slot->start_time->format('H:i') . '-' . $slot->end_time->format('H:i');
            if (!in_array($key, $seen)) {
                $seen[] = $key;
                $uniqueTimeSlots[] = $slot;
            }
        }

        // Days
        $days = [
            ['key' => 'monday', 'label' => 'Monday'],
            ['key' => 'tuesday', 'label' => 'Tuesday'],
            ['key' => 'wednesday', 'label' => 'Wednesday'],
            ['key' => 'thursday', 'label' => 'Thursday'],
            ['key' => 'friday', 'label' => 'Friday'],
        ];

        // Organize entries by day and time
        $organizedEntries = [];
        foreach ($days as $day) {
            $organizedEntries[$day['key']] = [];
            foreach ($uniqueTimeSlots as $slot) {
                $organizedEntries[$day['key']][$slot->start_time->format('H:i')] = null;
            }
        }

        foreach ($this->entries as $entry) {
            if ($entry->timeSlot) {
                $day = $entry->timeSlot->day_of_week;
                $time = $entry->timeSlot->start_time->format('H:i');
                if (isset($organizedEntries[$day]) && array_key_exists($time, $organizedEntries[$day])) {
                    $organizedEntries[$day][$time] = $entry;
                }
            }
        }

        // Get unique subjects
        $uniqueSubjects = [];
        $subjectsMap = [];
        foreach ($this->entries as $entry) {
            if ($entry->subject) {
                $subjectId = $entry->subject->id;
                if (!isset($subjectsMap[$subjectId])) {
                    $subjectsMap[$subjectId] = [
                        'id' => $subjectId,
                        'code' => $entry->subject->code,
                        'name' => $entry->subject->name,
                        'teachers' => [],
                    ];
                }
                if ($entry->teachers) {
                    foreach ($entry->teachers as $teacher) {
                        if (!in_array($teacher->name, $subjectsMap[$subjectId]['teachers'])) {
                            $subjectsMap[$subjectId]['teachers'][] = $teacher->name;
                        }
                    }
                }
            }
        }

        foreach ($subjectsMap as $subject) {
            $uniqueSubjects[] = [
                'code' => $subject['code'],
                'name' => $subject['name'],
                'teacherNames' => implode(' ၊ ', $subject['teachers']) ?: 'N/A',
            ];
        }

        usort($uniqueSubjects, fn($a, $b) => strcmp($a['code'], $b['code']));

        // Start building the sheet
        $row = 1;

        // University header
        $sheet->mergeCells('A' . $row . ':' . chr(65 + count($uniqueTimeSlots)) . $row);
        $sheet->setCellValue('A' . $row, 'University of Computer Studies, Hinthada');
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;

        // Academic year header
        $sheet->mergeCells('A' . $row . ':' . chr(65 + count($uniqueTimeSlots)) . $row);
        $academicYearText = $selectedYear ? 'Academic Year: ' . $selectedYear->name : '';
        if ($selectedSemester) {
            $academicYearText .= ' (' . $selectedSemester->name . ')';
        }
        $sheet->setCellValue('A' . $row, $academicYearText);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setSize(10);
        $row++;

        // Timetable header
        $sheet->mergeCells('A' . $row . ':' . chr(65 + count($uniqueTimeSlots)) . $row);
        $timetableText = 'Timetable For -';
        if ($selectedLevel) $timetableText .= ' ' . $selectedLevel->name;
        if ($selectedProgram) $timetableText .= ' (' . $selectedProgram->name . ')';
        if ($selectedSection) $timetableText .= ' Section: ' . '(' . $selectedSection->name . ')';
        if ($selectedSection && $selectedSection->classroom) {
            $timetableText .= "\nClassroom: " . $selectedSection->classroom->room_no;
        }
        $sheet->setCellValue('A' . $row, $timetableText);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setSize(10);
        $row++;

        // Time slot headers
        $sheet->setCellValue('A' . $row, 'Day');
        $col = 'B';
        foreach ($uniqueTimeSlots as $slot) {
            $startTime = $slot->start_time->format('H:i');
            $endTime = $slot->end_time->format('H:i');
            $sheet->setCellValue($col . $row, $startTime . ' - ' . $endTime);
            if ($slot->is_lunch_period) {
                $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FED7AA');
                $sheet->setCellValue($col . $row, $startTime . ' - ' . $endTime . "\nLunch");
            }
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':' . chr(65 + count($uniqueTimeSlots)) . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':' . chr(65 + count($uniqueTimeSlots)) . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
        $row++;

        // Grid data
        foreach ($days as $day) {
            $sheet->setCellValue('A' . $row, $day['label']);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $col = 'B';
            foreach ($uniqueTimeSlots as $slot) {
                $timeKey = $slot->start_time->format('H:i');
                $entry = $organizedEntries[$day['key']][$timeKey] ?? null;
                if ($entry && $entry->subject) {
                    $sheet->setCellValue($col . $row, $entry->subject->code);
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else if (!$slot->is_lunch_period) {
                    $sheet->setCellValue($col . $row, 'No subject');
                    $sheet->getStyle($col . $row)->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                if ($slot->is_lunch_period) {
                    $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                }

                $col++;
            }
            $row++;
        }

        //  Section Head Teacher (Excel Export)
        if (!empty($selectedSection?->sectionHeadTeacher?->name)) {
            $sheet->setCellValue('A' . (++$row), 'သင်တန်းမှူး - ' . $selectedSection->sectionHeadTeacher->name);
            $sheet->getStyle('A' . $row)->getFont()
                ->setBold(true)
                ->setSize(12);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }


        // Subject-Teacher table
        if (!empty($uniqueSubjects)) {
            $sheet->setCellValue('A' . $row, 'Code');
            $sheet->setCellValue('B' . $row, 'Subject Name');
            $sheet->setCellValue('C' . $row, 'Teacher Name');
            $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':C' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            $row++;

            foreach ($uniqueSubjects as $subject) {
                $sheet->setCellValue('A' . $row, $subject['code']);
                $sheet->setCellValue('B' . $row, $subject['name']);
                $sheet->setCellValue('C' . $row, $subject['teacherNames']);
                $row++;
            }
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $col = 'B';
        for ($i = 0; $i < count($uniqueTimeSlots); $i++) {
            $sheet->getColumnDimension($col)->setWidth(20);
            $col++;
        }

        // Add borders to all cells
        $lastCol = chr(65 + count($uniqueTimeSlots));
        $lastRow = $row - 1;
        $sheet->getStyle('A1:' . $lastCol . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Auto-size columns if needed
            },
        ];
    }
}
