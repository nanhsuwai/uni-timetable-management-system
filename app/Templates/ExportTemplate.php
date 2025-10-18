<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

// NOTE: We assume that the necessary descriptive data (AcademicYear, Section, etc.) 
// is available or fetched in the service layer before calling this export.

class TimetableTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $dataRows;
    protected $headers;
    protected $headerContext;

    /**
     * @param Collection $fullData - The Collection returned by TimetableTemplate::generateExcelData()
     * @param array $headerContext - Contextual data for the static header (e.g., year, program, section names).
     */
    public function __construct(Collection $fullData, array $headerContext)
    {
        // Split the full data collection into headings (first row) and data (rest of the rows)
        $this->headers = $fullData->first();
        $this->dataRows = $fullData->slice(1)->toArray();
        $this->headerContext = $headerContext;
    }

    /**
     * Returns the body of the timetable (data rows only).
     */
    public function array(): array
    {
        return $this->dataRows;
    }

    /**
     * Returns the headings for the timetable table (Day and Time Slots).
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * Defines custom styles for the Excel sheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Determine the last column letter based on the number of headings
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headers));

        // Row 6 is where the table headings start (after 5 rows of custom header info)
        $headerRange = 'A6:' . $lastCol . '6';
        
        // Table Header Style
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // Table Body Style (starting from row 7 to the last row)
        $dataRange = 'A7:' . $lastCol . $sheet->getHighestRow();
        $sheet->getStyle($dataRange)->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            'font' => ['size' => 10],
        ]);
        
        // Ensure "Day" column (A) is aligned to the left for readability
        $sheet->getStyle('A7:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal('left');

        // Column width optimization
        $sheet->getColumnDimension('A')->setWidth(18); // Day column
        foreach (range(2, count($this->headers)) as $colIndex) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setWidth(20); // Time Slot columns
        }

        return [];
    }

    /**
     * Registers custom events for merging cells and adding fixed header/footer text.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $context = $this->headerContext;

                // --- 1. Top Header Information ---
                
                // A1: University Name
                $sheet->mergeCells("A1:$lastCol" . '1');
                $sheet->setCellValue('A1', 'University of Computer Studies, Hinthada');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0070C0');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // A2: Academic Year and Semester
                $sheet->mergeCells("A2:$lastCol" . '2');
                $yearSemester = ($context['academicYear']->name ?? 'N/A Year') . 
                                ($context['semester']->name ? ' (' . $context['semester']->name . ')' : '');
                $sheet->setCellValue('A2', 'Academic Year: ' . $yearSemester);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // A3: Program, Level, and Section
                $sheet->mergeCells("A3:$lastCol" . '3');
                $levelProgramSection = ($context['level']->name ?? 'N/A Level') . 
                                       ($context['program']->name ? ' (' . $context['program']->name . ')' : '') .
                                       ($context['section']->name ? ' (Section: ' . $context['section']->name . ')' : '');
                $sheet->setCellValue('A3', 'Timetable for ' . $levelProgramSection);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // A4: Room Info
                $sheet->mergeCells("A4:$lastCol" . '4');
                $roomNo = $context['section']->classroom->room_no ?? 'N/A Room';
                $sheet->setCellValue('A4', 'Room - ' . $roomNo);
                $sheet->getStyle('A4')->getAlignment()->setHorizontal('right');
                
                // Add space before the main table
                $sheet->getRowDimension(5)->setRowHeight(10); 
                
                // --- 2. Footer/Signature ---
                // Find the next available row after the data table
                $footerRow = $sheet->getHighestRow() + 2; 

                // Footer signature (from PDF template)
                if ($context['section'] && $context['section']->sectionHeadTeacher) {
                    $sheet->mergeCells("A$footerRow:$lastCol$footerRow");
                    $headTeacherName = $context['section']->sectionHeadTeacher->name;
                    $sheet->setCellValue("A$footerRow", 'သင်တန်းမှူး - ' . $headTeacherName);
                    $sheet->getStyle("A$footerRow")->getAlignment()->setHorizontal('right');
                }

                // Date
                $dateRow = $footerRow + 2;
                $sheet->mergeCells("A$dateRow:$lastCol$dateRow");
                $sheet->setCellValue("A$dateRow", 'Date: ' . date('d/m/Y'));
                $sheet->getStyle("A$dateRow")->getAlignment()->setHorizontal('right');
            },
        ];
    }
}
