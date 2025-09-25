# TODO: Fix Semester Logic and UI

## Overview
Restrict semesters to only "First Semester" and "Second Semester" per academic year, making semesters global per academic year (remove program_id).

## Steps
- [x] Create migration to remove program_id from semesters table and add unique constraint on academic_year_id and name
- [x] Update Semester model: remove program_id from fillable, remove academicProgram relationship, update validation rules
- [x] Update Semester CreateController: remove program_id handling
- [x] Update Semester UpdateController: remove program_id handling
- [x] Update Semester IndexController: remove program filtering and program_id from queries
- [x] Update Semester Index.vue: change name to dropdown, remove program selection
- [x] Update WelcomeController: remove program_id from semester select
- [x] Update AcademicProgram model: remove hasMany semesters if needed
- [x] Run migration
- [x] Test the changes
