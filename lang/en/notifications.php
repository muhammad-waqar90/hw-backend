<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notifications Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for the Notifications types & titles.
    |
    */
    'titles' => [
        'ticket' => [
            'reply'         => 'You received a reply regarding your ticket',
            'status'        => 'Status of ticket changed',
            'claimed'       => 'Admin :username has claimed your ticket',
            'unclaimed'     => 'Admin :username has unclaimed your ticket',
            'resolved'      => ':username marked the ticket as resolved',
            'notClaimed'    => 'Ticket #:id not claimed',
        ],
        'general' =>  'General notification',
    ],
    'body' => [
        'ticket' => [
            'notClaimed'    => 'We are sorry to not entertain your ticket (#:id :subject) but we are doing our best and will back to you asap',
        ],
    ],
    'success' => [
        'read'  => 'Notification successfully marked as read',
        'bulkRead'  => 'Marked all notifications as read',
    ],
    'errors' => [
        'alreadyRead'   => 'Notification already marked as read',
    ],
    'certificates' => [
        'title'         => 'Congratulation',
        'certificate' => 'Congratulation for receiving certificate upon completion of your :entity_type',
    ],
    'qa' => [ // lesson Q&A
        'title'         => 'Lecture (:lesson) Q&A reply',
    ]

];
