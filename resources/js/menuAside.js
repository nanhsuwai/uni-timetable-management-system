import {
  mdiMonitorDashboard,
  mdiAccountArrowRight,
  mdiAccountPlus,
  mdiAccountSchool,
  mdiShape,
  mdiFileChart,
  mdiAccountGroup,
  mdiFileDocument,
  mdiCalendarBadge,
  mdiHome,
  mdiCalendar,
  mdiFileAccount,
  mdiPublish,
  mdiGrid,
  mdiClockTimeFour,
} from "@mdi/js";

export default [
  {
    route: "dashboard",
    icon: mdiMonitorDashboard,
    label: "Dashboard",
    permission_code: "dashboard_access",
    show: true
  },
  {
    route: "academic-year:all",
    icon: mdiCalendarBadge,
    label: "Academic Years",
    permission_code: "academic_year_manage",
    show: true
  },
  {
    route: "time-slot:all",
    icon: mdiClockTimeFour,
    label: "Time Slots",
    permission_code: "time_slot_manage",
    show: true
  },
  // {
  //   route: "semester:all",
  //   icon: mdiCalendar,
  //   label: "Semesters",
  //   permission_code: "manage_semester",
  //   show: false
  // },
  // {
  //   route: "academic_program:all",
  //   icon: mdiFileDocument,
  //   label: "Academic Programs",
  //   permission_code: "manage_academic_program",
  //   show: true
  // },
  {
    route: "academic_level:all",
    icon: mdiFileChart,
    label: "Academic Levels",
    permission_code: "academic_level_manage",
    show: true
  },

  {
    route: "classroom:all",
    icon: mdiShape,
    label: "Classrooms",
    permission_code: "classroom_manage",
    show: true
  },
  {
    route: "timetable_entry:all",
    icon: mdiFileAccount,
    label: "Timetable Entries",
    permission_code: "timetable_entry_manage",
    show: true
  },
  {
  route: "timetable_entry:grid",
  icon: mdiGrid,
  label: "Timetable Grid View",
  permission_code: "timetable_entry_manage",
  show: true
}
,
  {
    route: "subject:all",
    icon: mdiAccountPlus,
    label: "Subjects",
    permission_code: "subject_manage",
    show: true
  },
  {
    route: "teacher:all",
    icon: mdiAccountArrowRight,
    label: "Teachers",
    permission_code: "teacher_manage",
    show: true  
  },
  {
    route: "users:all",
    label: "Users Management",
    icon: mdiAccountGroup,
    permission_code: "user_manage",
    show: true
  },
]
