<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Notifications\Notification;

/** In-app notice to trainees after a supervisor archives a course's results. */
class CourseResultsArchived extends Notification
{
    public function __construct(private Course $course) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'url' => route('courses.index', ['tab' => 'mine'], false),
            'message' => "\"{$this->course->title}\" was updated. Your earlier results were archived: please redo the course and take the new final exam.",
        ];
    }
}
