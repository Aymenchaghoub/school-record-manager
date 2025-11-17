@extends('layouts.base')

@section('navigation')
    @if(Auth::check())
        @if(Auth::user()->role === 'admin')
            @include('layouts.admin-nav')
        @elseif(Auth::user()->role === 'teacher')
            @include('layouts.teacher-nav')
        @elseif(Auth::user()->role === 'student')
            @include('layouts.student-nav')
        @elseif(Auth::user()->role === 'parent')
            @include('layouts.parent-nav')
        @endif
    @endif
@endsection
