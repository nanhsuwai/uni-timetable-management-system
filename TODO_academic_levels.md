# TODO: Implement Academic Level Restrictions

## Overview
Restrict academic levels to fixed names: "First Year", "Second Year", "Third Year", "Fourth Year", "Fifth Year", "Master". Levels depend on academic programs with specific structure:
- First Year: One program (assume general or single per year).
- Second Year to Master: Divided into CS and CT programs per academic year.
Update logic and UI to enforce this, using program_type (CS/CT) for division.

## Steps
- [ ] Create migration to add unique constraint on program_id and name in academic_levels table
- [ ] Update AcademicLevel model: Add validation rules for name to fixed list, add methods to validate level based on program_type (e.g., First Year for all, higher for CS/CT)
- [ ] Update AcademicLevel CreateController: Use model validation, add program_type check (e.g., restrict higher levels to CS/CT programs)
- [ ] Update AcademicLevel UpdateController: Similar to create, enforce restrictions
- [ ] Update AcademicLevel IndexController: Filter programs by type if needed, pass filtered options to UI
- [ ] Update AcademicLevel Index.vue: Change name input to dropdown with fixed levels, filter available levels based on selected program_type (e.g., show all for first, CS/CT specific for higher)
- [ ] Update AcademicProgram model if needed (e.g., add scope for CS/CT programs)
- [ ] Run migration
- [ ] Test the changes (create levels for different program types, verify restrictions)
