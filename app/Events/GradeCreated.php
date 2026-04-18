<?php

namespace App\Events;

use App\Models\Grade;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GradeCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Grade $grade)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("student.{$this->grade->student_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'grade.created';
    }

    public function broadcastWhen(): bool
    {
        return !app()->environment('testing');
    }

    public function broadcastWith(): array
    {
        $grade = $this->grade->loadMissing([
            'student:id,name,first_name,last_name',
            'subject:id,name',
            'class:id,name',
        ]);

        $firstName = (string) ($grade->student?->first_name ?? '');
        $lastName = (string) ($grade->student?->last_name ?? '');
        $fullName = trim("{$firstName} {$lastName}");

        return [
            'id' => $grade->id,
            'subject' => $grade->subject?->name ?? 'Non precise',
            'value' => (float) $grade->value,
            'max_value' => (float) ($grade->max_value ?: 20),
            'term' => (string) ($grade->term ?? ''),
            'date' => optional($grade->grade_date)->toDateString() ?? (string) $grade->grade_date,
            'class' => $grade->class?->name ?? '',
            'student' => $fullName !== '' ? $fullName : ($grade->student?->name ?? ''),
            'message' => 'Nouvelle note enregistree',
        ];
    }
}
