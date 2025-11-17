@php
$currentRoute = request()->route()->getName();
$navItems = [
    ['route' => 'admin.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
    ['route' => 'admin.users.index', 'icon' => 'fas fa-users', 'label' => 'Users'],
    ['route' => 'admin.classes.index', 'icon' => 'fas fa-school', 'label' => 'Classes'],
    ['route' => 'admin.subjects.index', 'icon' => 'fas fa-book', 'label' => 'Subjects'],
    ['route' => 'admin.grades.index', 'icon' => 'fas fa-chart-line', 'label' => 'Grades'],
    ['route' => 'admin.absences.index', 'icon' => 'fas fa-calendar-times', 'label' => 'Absences'],
    ['route' => 'admin.events.index', 'icon' => 'fas fa-calendar-alt', 'label' => 'Events'],
    ['route' => 'admin.reports.index', 'icon' => 'fas fa-file-alt', 'label' => 'Reports'],
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
