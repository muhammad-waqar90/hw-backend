<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

abstract class AbstractSubscriber implements ShouldQueue
{
    /**
     * Create a new subscriber instance.
     */
    public $queue = 'low';
}
