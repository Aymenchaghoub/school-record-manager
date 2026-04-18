import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

export default function CalendarView({ events = [], onEventClick, height = 600 }) {
  return (
    <div
      style={{
        background: 'var(--color-surface)',
        borderRadius: 'var(--radius-card)',
        boxShadow: 'var(--shadow-card)',
        padding: '16px',
      }}
    >
      <FullCalendar
        plugins={[dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin]}
        initialView="dayGridMonth"
        locale={frLocale}
        height={height}
        headerToolbar={{
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,listWeek',
        }}
        events={events}
        eventClick={onEventClick}
        eventDisplay="block"
        eventColor="var(--color-primary)"
        nowIndicator
      />
    </div>
  );
}
