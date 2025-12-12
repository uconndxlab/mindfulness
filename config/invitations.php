<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invitation Expiration Days
    |--------------------------------------------------------------------------
    |
    | This value determines how many days an invitation link will remain valid
    | before expiring. Users must complete registration within this timeframe.
    |
    */

    'expiration_days' => env('INVITATION_EXPIRATION_DAYS', 7),
];
