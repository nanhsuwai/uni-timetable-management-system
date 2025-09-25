<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AcademicYear;
use App\Models\AcademicProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcademicProgramDefaultFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sets_default_filter_to_active_academic_year()
    {
        // Create an inactive academic year
        $inactiveYear = AcademicYear::create([
            'name' => '2022-2023',
            'start_date' => '2022-09-01',
            'end_date' => '2023-08-31',
            'status' => 'inactive'
        ]);

        // Create an active academic year
        $activeYear = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-08-31',
            'status' => 'active'
        ]);

        // Create programs for different years
        $program1 = AcademicProgram::create([
            'academic_year_id' => $inactiveYear->id,
            'name' => 'Computer Science 2022',
            'status' => 'active'
        ]);

        $program2 = AcademicProgram::create([
            'academic_year_id' => $activeYear->id,
            'name' => 'Computer Science 2023',
            'status' => 'active'
        ]);

        // Test the controller
        $controller = new \App\Http\Controllers\AcademicProgram\IndexController();
        $request = new \Illuminate\Http\Request();

        $response = $controller($request);

        // Check that the default academic year is set to the active year
        $responseData = $response->getOriginalContent();
        $this->assertEquals($activeYear->id, $responseData['filters']['filterAcademicYear']);
        $this->assertEquals($activeYear->id, $responseData['defaultAcademicYear']->id);
    }

    /** @test */
    public function it_uses_provided_filter_when_available()
    {
        // Create academic years
        $year1 = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-08-31',
            'status' => 'active'
        ]);

        $year2 = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-08-31',
            'status' => 'inactive'
        ]);

        // Create programs
        $program1 = AcademicProgram::create([
            'academic_year_id' => $year1->id,
            'name' => 'Program 1',
            'status' => 'active'
        ]);

        $program2 = AcademicProgram::create([
            'academic_year_id' => $year2->id,
            'name' => 'Program 2',
            'status' => 'active'
        ]);

        // Test with provided filter
        $controller = new \App\Http\Controllers\AcademicProgram\IndexController();
        $request = new \Illuminate\Http\Request(['filterAcademicYear' => $year2->id]);

        $response = $controller($request);

        // Check that the provided filter is used
        $responseData = $response->getOriginalContent();
        $this->assertEquals($year2->id, $responseData['filters']['filterAcademicYear']);
        $this->assertEquals($year2->id, $responseData['defaultAcademicYear']->id);
    }

    /** @test */
    public function it_handles_case_when_no_active_academic_year_exists()
    {
        // Create only inactive academic year
        $inactiveYear = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-08-31',
            'status' => 'inactive'
        ]);

        $controller = new \App\Http\Controllers\AcademicProgram\IndexController();
        $request = new \Illuminate\Http\Request();

        $response = $controller($request);

        // Check that no default filter is set
        $responseData = $response->getOriginalContent();
        $this->assertNull($responseData['filters']['filterAcademicYear']);
        $this->assertNull($responseData['defaultAcademicYear']);
    }
}
