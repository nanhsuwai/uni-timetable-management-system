<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\ProgramOption;
use App\Enums\LevelName;
use App\Enums\SemesterName;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subjects = [
            ProgramOption::CST->value => [
                LevelName::FirstYear->value => [
                    SemesterName::FirstSemester->value => [
                        ['code' => 'M-1201', 'name' => 'Myanmar Language'],
                        ['code' => 'E-1201', 'name' => 'English   Proficiency II'],
                    ],
                    SemesterName::SecondSemester->value => [
                        ['code' => 'M-1201', 'name' => 'Myanmar Language'],
                        ['code' => 'E-1201', 'name' => 'English Proficiency II'],
                        ['code' => 'P-1201', 'name' => 'College Physics'],
                        ['code' => 'CST-1241', 'name' => 'Discrete Mathematics'],
                        ['code' => 'CST-1212', 'name' => 'Programming Logic & Design (Programming in C++)'],
                        ['code' => 'CST-1223', 'name' => 'Database Fundamentals'],
                        ['code' => 'CST-1234', 'name' => 'Digital and Logic Design'],

                    ],
                ],
            ],
            ProgramOption::ComputerScience->value => [
                LevelName::SecondYear->value => [
                    SemesterName::FirstSemester->value => [
                        ['code' => 'CS301', 'name' => 'Data Structures and Algorithms'],
                        ['code' => 'CS302', 'name' => 'Database Management'],
                    ],
                    SemesterName::SecondSemester->value => [
                        ['code' => 'E-2201', 'name' => 'English Proficiency IV'],
                        ['code' => 'CST-2211', 'name' => 'Data Structure and Algorithms'],
                        ['code' => 'CST-2242', 'name' => 'Linear Algebra'],
                        ['code' => 'CST-2223', 'name' => 'Introduction to Software Engineering '],
                        ['code' => 'CS-2254', 'name' => 'Web Development (Java Script)'],
                        ['code' => 'CST(SS)-2205', 'name' => 'Advanced Java Programming (J2EE)'],

                    ],
                ],
                LevelName::ThirdYear->value => [
                    SemesterName::FirstSemester->value => [
                        ['code' => 'CS501', 'name' => 'Software Engineering'],
                        ['code' => 'CS502', 'name' => 'Computer Networks'],
                    ],
                    SemesterName::SecondSemester->value => [
                        ['code' => 'CST-3211', 'name' => 'Operating Systems '],
                        ['code' => 'CST-3242', 'name' => 'Probability and Statistics'],
                        ['code' => 'CST-3213', 'name' => 'Professional Ethics'],
                        ['code' => 'CS-3224', 'name' => 'Software Quality Assurance and Testing'],
                        ['code' => 'CST-3235', 'name' => 'Computer Networks I'],
                        ['code' => 'CST-3256(SS)', 'name' => 'Human  Computer Interaction'],
                        ['code' => 'CST-3257(SS)', 'name' => 'Applied Database and Application (ADO.Net,C#)'],


                    ],
                ],
                LevelName::FourthYear->value => [
                    SemesterName::FirstSemester->value => [
                        ['code' => 'E-4101', 'name' => 'Business English I'],
                        ['code' => 'CST-4111', 'name' => 'Analysis of Algorithms'],
                        ['code' => 'CS-4142', 'name' => 'Operation Research'],
                        ['code' => 'CS-4113', 'name' => 'Computer Vision'],
                        ['code' => 'CS-4124', 'name' => 'Introduction Assurance and Security'],
                        ['code' => 'CS-4125', 'name' => 'Software Project Management'],
                        ['code' => 'CS-4134', 'name' => 'Cloud Computing'],
                        ['code' => 'CS-4135', 'name' => 'Data Mining'],



                    ],
                    SemesterName::SecondSemester->value => [
                        ['code' => 'CST-4211', 'name' => 'Parallel and Distributed Computing'],
                        ['code' => 'CST-4242', 'name' => 'Modelling & Stimulation'],
                        ['code' => 'CS-4223', 'name' => 'Object Oriented Design and Development'],
                        ['code' => 'CS-4214', 'name' => 'Advanced Artificial Intelligence'],
                        ['code' => ' CS-4225', 'name' => 'Advanced Database Systems'],
                        ['code' => 'CST-4257', 'name' => 'Digital Business and E-Commerce Management'],

                    ],
                ],
            ],
            ProgramOption::ComputerTechnology->value => [
                LevelName::SecondYear->value => [

                    SemesterName::SecondSemester->value => [
                        ['code' => 'E-2201', 'name' => 'English Proficiency IV'],
                        ['code' => 'CST-2211', 'name' => 'Data Structure and Algorithms'],
                        ['code' => 'CST-2242', 'name' => 'Linear Algebra'],
                        ['code' => 'CST-2223', 'name' => 'Introduction to Software Engineering'],
                        ['code' => 'CT-2234', 'name' => 'Circuits and Electronics'],
                        ['code' => 'CST(SS)-2205', 'name' => 'Arduino Fundamentals'],
                    ],
                ],
                LevelName::ThirdYear->value => [
                    SemesterName::FirstSemester->value => [
                        ['code' => 'CT501', 'name' => 'Software Engineering'],
                        ['code' => 'CT502', 'name' => 'Computer Networks'],
                    ],
                    SemesterName::SecondSemester->value => [
                        ['code' => 'CST-3211', 'name' => 'Operating Systems '],
                        ['code' => 'CST-3242', 'name' => 'Probability and Statistics'],
                        ['code' => 'CST-3213', 'name' => 'Professional Ethics'],
                        ['code' => 'CS-3224', 'name' => 'Software Quality Assurance and Testing'],
                        ['code' => 'CST-3235', 'name' => 'Computer Networks I'],
                        ['code' => 'CST-3256(SS)', 'name' => 'Human  Computer Interaction'],
                        ['code' => 'CST-3257(SS)', 'name' => 'Applied Database and Application (ADO.Net,C#)'],
                    ],
                ],
                LevelName::FourthYear->value => [
                    SemesterName::FirstSemester->value => [
                        ['code' => 'E-4101', 'name' => 'Business English I'],
                        ['code' => 'CST-4111', 'name' => 'Analysis of Algorithms'],
                        ['code' => 'CS-4142', 'name' => 'Operations Research'],
                        ['code' => 'CS-4113', 'name' => 'Computer Vision'],
                        ['code' => ' CS-4124', 'name' => 'Information Assurance and Security'],
                        ['code' => 'CST-4125', 'name' => 'Software Project Management'],
                    ],

                ],
            ],


        ];

        $createdSubjects = [];

        foreach ($subjects as $program => $levels) {
            foreach ($levels as $level => $semesters) {
                foreach ($semesters as $semester => $subjectList) {
                    foreach ($subjectList as $subject) {
                        $createdSubject = \App\Models\Subject::firstOrCreate([
                            'code' => $subject['code'],
                        ], [
                            'name' => $subject['name'],
                            'status' => 'active',
                            'level' => $level,
                            'program' => $program,
                            'semester' => $semester,
                        ]);
                        $createdSubjects[] = $createdSubject;
                    }
                }
            }
        }

        // Assign teachers to subjects
        $teachers = \App\Models\Teacher::all();
        foreach ($createdSubjects as $subject) {
            // Assign random teachers to each subject (1-3 teachers per subject)
            $assignedTeachers = $teachers->random(rand(1, 3));
            $subject->teachers()->syncWithoutDetaching($assignedTeachers->pluck('id')->toArray());
        }
    }
}
