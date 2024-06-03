<?php

namespace App\DataObject;

class CoursesData
{
    const OWNED_COURSES_ORDER = ['recentlyUsed' => 'progress_updated_at', 'progress' => 'progress', 'createdDate' => 'created_at'];

    const AVAILABLE_COURSES_ORDER = ['createdDate' => 'created_at', 'popularity' => 'popularity'];

    const COMING_SOON_COURSES_ORDER = ['createdDate' => 'created_at'];

    const ORDER_DIRECTION = ['ASC' => 'ASC', 'DESC' => 'DESC'];
}
