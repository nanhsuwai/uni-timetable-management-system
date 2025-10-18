<?php

namespace App\Exports;

use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Section;
use App\Models\TimetableEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimetableExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected array $filters;
    protected array $dataGrid;
    protected Collection $entriesLookup;
    
    // Models for dynamic headers
    protected ?AcademicYear $academicYearModel;
    protected ?Semester $semesterModel;
    protected ?AcademicProgram $programModel;
    protected ?AcademicLevel $levelModel;
    protected ?Section $sectionModel;
    
    // Define timetable structure constants once
    protected const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    protected const TIME_SLOTS = [
        '09:00 AM - 10:00 AM',
        '10:05 AM - 11:05 AM',
        '11:10 AM - 12:10 PM',
        '01:00 PM - 02:00 PM',
        '02:05 PM - 03:05 PM',
        '03:10 PM - 04:10 PM'
    ];


    public function __construct(array $filters)
    {
        $this->filters = $filters;
        
        // 1. Fetch all necessary context models (Dynamic Headers)
        $this->fetchContextModels();
        
        // 2. Fetch Entries and build the O(1) lookup map
        $entries = $this->fetchTimetableEntries();
        $this->buildEntriesLookup($entries);
        
        // 3. Generate the final Excel data grid (array)
        $this->dataGrid = $this->buildTimetableDataGrid();
    }

    /**
     * Fetch required models for dynamic headers.
     */
    protected function fetchContextModels(): void
    {
        $this->academicYearModel = AcademicYear::find(
            $this->filters['filterYear'] ?? null
        );
        $this->semesterModel = Semester::find(
            $this->filters['filterSemester'] ?? null
        );
        $this->programModel = AcademicProgram::find(
            $this->filters['filterProgram'] ?? null
        );
        $this->levelModel = AcademicLevel::find(
            $this->filters['filterLevel'] ?? null
        );
        // Ensure Section loads its required relationships (classroom and sectionHeadTeacher)
        $this->sectionModel = Section::with(['classroom', 'sectionHeadTeacher'])->find(
            $this->filters['filterSection'] ?? null
        );
    }
    
    /**
     * Fetch Timetable Entries based on filters.
     */
    protected function fetchTimetableEntries(): Collection
    {
        $query = TimetableEntry::with([
            'subject.teachers',
            'timeSlot'
        ]);

        if (!empty($this->filters['filterYear'])) {
            $query->where('academic_year_id', $this->filters['filterYear']);
        }
        if (!empty($this->filters['filterSemester'])) {
            $query->where('semester_id', $this->filters['filterSemester']);
        }
        if (!empty($this->filters['filterProgram'])) {
            $query->where('program_id', $this->filters['filterProgram']);
        }
        if (!empty($this->filters['filterLevel'])) {
            $query->where('level_id', $this->filters['filterLevel']);
        }
        if (!empty($this->filters['filterSection'])) {
            $query->where('section_id', $this->filters['filterSection']);
        }

        return $query->get();
    }

    /**
     * Builds an efficient O(1) lookup map: [day_timeslot] => Entry
     */
    protected function buildEntriesLookup(Collection $entries): void
    {
        $this->entriesLookup = $entries->keyBy(function ($entry) {
            $day = $entry->timeSlot?->day_of_week;
            $start = $entry->timeSlot?->start_time;
            $end = $entry->timeSlot?->end_time;
            
            // Format time to match the TIME_SLOTS constant format for reliable lookup
            $formattedTime = $start && $end 
                ? date('h:i A', strtotime($start)) . ' - ' . date('h:i A', strtotime($end))
                : null;

            // Use day and formatted time as the lookup key
            return $day && $formattedTime ? strtoupper($day) . '_' . $formattedTime : null;
        })->filter(); // Filter out any entries missing a timeSlot
    }
    
    /**
     * Generates the final array data structure using the optimized lookup.
     */
    protected function buildTimetableDataGrid(): array
    {
        $data = [];
        foreach (self::DAYS as $day) {
            $row = [$day];
            foreach (self::TIME_SLOTS as $slot) {
                // Optimized O(1) lookup
                $key = strtoupper($day) . '_' . $slot;
                $entry = $this->entriesLookup->get($key);

                if ($entry) {
                    $subjectCode = $entry->subject?->code ?? '';
                    // The subject model likely holds the teachers relationship, not the entry model.
                    // Assuming the 'subject.teachers' was loaded via the query above:
                    $teachers = $entry->subject?->teachers->pluck('name')->join(', ') ?? '';
                    $cell = $subjectCode . ($teachers ? " ($teachers)" : '');
                } else {
                    $cell = ''; // Empty if no class
                }

                $row[] = $cell;
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Returns the pre-calculated data grid.
     */
    public function array(): array
    {
        return $this->dataGrid;
    }

    public function headings(): array
    {
        return array_merge(['Day'], self::TIME_SLOTS);
    }

    public function styles(Worksheet $sheet)
    {
        // Calculate the last column dynamically (G is correct for 7 columns, but better to be dynamic)
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(self::TIME_SLOTS) + 1);
        
        // Headers start at Row 6
        $headerRange = 'A6:' . $lastCol . '6';
        // Body starts at Row 7
        $dataRange = 'A7:' . $lastCol . $sheet->getHighestRow();

        // Header style
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');


        // Body styles
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle($dataRange)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($dataRange)->getAlignment()->setVertical('center');
        $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);
        
        // Column width
        $sheet->getColumnDimension('A')->setWidth(18); // Day column
        foreach (range(2, count(self::TIME_SLOTS) + 1) as $colIndex) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($col)->setWidth(22);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                
                // --- 1. Dynamic Header Rows (A1:A4) ---

                // A1: University Name
                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->setCellValue('A1', 'University of Computer Studies, Hinthada');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0070C0');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // A2: Academic Year and Semester
                $sheet->mergeCells('A2:' . $lastCol . '2');
                $year = $this->academicYearModel->name ?? 'N/A Year';
                $semester = $this->semesterModel->name ?? 'N/A Semester';
                $sheet->setCellValue('A2', "Academic Year: $year ($semester)");
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // A3: Timetable for Level, Program, and Section
                $sheet->mergeCells('A3:' . $lastCol . '3');
                $level = $this->levelModel->name ?? 'N/A Level';
                $program = $this->programModel->name ?? 'N/A Program';
                $section = $this->sectionModel->name ?? 'N/A Section';
                $sheet->setCellValue('A3', "Timetable for $level ($program) (Section: $section)");
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // A4: Room Info
                $sheet->mergeCells('A4:' . $lastCol . '4');
                $roomNo = $this->sectionModel?->classroom?->room_no ?? 'N/A Room';
                $sheet->setCellValue('A4', 'Room - ' . $roomNo );
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('right');
                
                // --- 2. Footer / Signature ---
                
                // Find the first empty row after the table data
                $footerRow = $lastRow + 2; 

                // သင်တန်းမှူး (Section Head Teacher)
                $sheet->mergeCells('A' . $footerRow . ':' . $lastCol . $footerRow);
                $headTeacher = $this->sectionModel?->sectionHeadTeacher?->name ?? '';
                $sheet->setCellValue('A' . $footerRow, 'သင်တန်းမှူး - ' . $headTeacher);
                $sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal('right');
                
                // Date
                $dateRow = $footerRow + 2;
                $sheet->mergeCells('A' . $dateRow . ':' . $lastCol . $dateRow);
                $sheet->setCellValue('A' . $dateRow, 'Date: ' . date('d/m/Y'));
                $sheet->getStyle('A' . $dateRow)->getAlignment()->setHorizontal('right');
            },
        ];
    }
}
