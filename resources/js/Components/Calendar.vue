<script setup>
import '@fullcalendar/core/vdom'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'


const calendarOptions = {
  plugins: [ dayGridPlugin ],
  initialView: 'dayGridMonth'
}
</script>

<template>
  <FullCalendar :options="calendarOptions" />
</template>
