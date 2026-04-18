<?php

namespace App\Events;

use App\Models\Absence;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbsenceCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Absence $absence)
    {
    }

    public function broadcastOn(): array
    {
        $absence = $this->absence->loadMissing([
            'student.studentParents:id',
            'subject:id,name',
            'class:id,name',
        ]);

        $channels = [
            new PrivateChannel("student.{$absence->student_id}"),
        ];

        $parentIds = $absence->student?->studentParents
            ?->pluck('id')
            ->filter()
            ->unique()
            ->values()
            ->all() ?? [];

        foreach ($parentIds as $parentId) {
            $channels[] = new PrivateChannel("parent.{$parentId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'absence.created';
    }

    public function broadcastWhen(): bool
    {
        return !app()->environment('testing');
    }

    public function broadcastWith(): array
    {
        $absence = $this->absence->loadMissing([
            'student:id,name,first_name,last_name',
            'subject:id,name',
            'class:id,name',
        ]);

        $firstName = (string) ($absence->student?->first_name ?? '');
        $lastName = (string) ($absence->student?->last_name ?? '');
        $fullName = trim("{$firstName} {$lastName}");

        return [
            'id' => $absence->id,
            'subject' => $absence->subject?->name ?? 'Non precise',
            'date' => optional($absence->absence_date)->toDateString() ?? (string) $absence->absence_date,
            'class' => $absence->class?->name ?? '',
            'student' => $fullName !== '' ? $fullName : ($absence->student?->name ?? ''),
            'justified' => (bool) $absence->is_justified,
            'message' => 'Nouvelle absence enregistree',
        ];
    }
}
