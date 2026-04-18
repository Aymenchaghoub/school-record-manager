<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('student.{studentId}', function ($user, $studentId) {
    return (int) $user->id === (int) $studentId && $user->role === 'student';
});

Broadcast::channel('parent.{parentId}', function ($user, $parentId) {
    return (int) $user->id === (int) $parentId && $user->role === 'parent';
});
