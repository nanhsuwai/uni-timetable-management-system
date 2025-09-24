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
    permission_code: "manage_dashboard",
    show: true
  },
  {
    route: "academic-year:all",
    icon: mdiCalendarBadge,
    label: "Academic Years",
    permission_code: "manage_academic_year",
    show: true
  },
  {
    route: "time-slot:all",
    icon: mdiClockTimeFour,
    label: "Time Slots",
    permission_code: "manage_time_slot",
    show: true
  },
  {
    route: "semester:all",
    icon: mdiCalendar,
    label: "Semesters",
    permission_code: "manage_semester",
    show: true
  },
  {
    route: "academic_program:all",
    icon: mdiFileDocument,
    label: "Academic Programs",
    permission_code: "manage_academic_program",
    show: true
  },
  {
    route: "academic_level:all",
    icon: mdiFileChart,
    label: "Academic Levels",
    permission_code: "manage_academic_level",
    show: true
  },
  {
    route: "section:all",
    icon: mdiAccountSchool,
    label: "Sections",
    permission_code: "manage_section",
    show: true
  },
  {
    route: "classroom:all",
    icon: mdiShape,
    label: "Classrooms",
    permission_code: "manage_classroom",
    show: true
  },
  {
    route: "timetable_entry:all",
    icon: mdiFileAccount,
    label: "Timetable Entries",
    permission_code: "manage_timetable_entry",
    show: true
  },
  {
  route: "timetable_entry:grid",
  icon: mdiGrid,
  label: "Timetable Grid View",
  permission_code: "manage_timetable_entry",
  show: true
}
,
  {
    route: "subject:all",
    icon: mdiAccountPlus,
    label: "Subjects",
    permission_code: "manage_subject",
    show: true
  },
  {
    route: "teacher:all",
    icon: mdiAccountArrowRight,
    label: "Teachers",
    permission_code: "manage_teacher",
    show: true  
  },
  {
    route: "users:all",
    label: "Users Management",
    icon: mdiAccountGroup,
    permission_code: "manage_users_management",
    show: true
  },
]
