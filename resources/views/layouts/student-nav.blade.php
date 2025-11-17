@php
$currentRoute = request()->route()->getName();
$navItems = [
    ['route' => 'student.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
    ['route' => 'student.grades.index', 'icon' => 'fas fa-chart-line', 'label' => 'My Grades'],
    ['route' => 'student.absences', 'icon' => 'fas fa-calendar-times', 'label' => 'My Absences'],
    ['route' => 'student.report-cards.index', 'icon' => 'fas fa-file-alt', 'label' => 'Report Cards'],
    ['route' => 'student.events', 'icon' => 'fas fa-calendar-alt', 'label' => 'Events'],
];
@endphp

@foreach($navItems as $item)
    <a href="{{ route($item['route']) }}" 
       class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200
              {{ str_starts_with($currentRoute, explode('.', $item['route'])[0] . '.' . explode('.', $item['route'])[1]) 
                 ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-soft' 
                 : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
        <i class="{{ $item['icon'] }} mr-3 text-base
           {{ str_starts_with($currentRoute, explode('.', $item['route'])[0] . '.' . explode('.', $item['route'])[1]) 
              ? 'text-white' 
              : 'text-gray-400 group-hover:text-gray-500' }}"></i>
        {{ $item['label'] }}
    </a>
@endforeach
