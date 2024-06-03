<?php

return [
    'iu_ticket_not_replied_reminder_email' => env('IU_TICKET_NOT_REPLIED_REMINDER_EMAIL', 48),
    'iu_ticket_closed_email' => env('IU_TICKET_CLOSED_EMAIL', 72),
    'af_ticket_claim_reminder_email' => env('AF_TICKET_CLAIM_REMINDER_EMAIL', 48),
    'af_ticket_claimed_but_not_responded_email' => env('af_ticket_claimed_but_not_responded_email', 24),
    'af_ticket_on_hold_reminder_email' => env('AF_TICKET_ON_HOLD_REMINDER_EMAIL', 120), // 5 days
];
