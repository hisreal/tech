<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class SchoolCalendarModel extends BaseModel
{
    protected string $table = 'school_calendar';

    /** @var array<int, string> */
    protected array $fillable = ['session_id', 'term_id', 'title', 'event_type', 'start_date', 'end_date', 'location', 'status', 'created_by'];
}
