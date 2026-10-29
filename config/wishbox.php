<?php

return [

    /*
    | Whose public wish note the Wunschliste is. Used for "Tanjas Wunschzettel"
    | and the signature at the bottom of the list.
    */
    'owner' => env('WISHBOX_OWNER', 'Tanja'),

    /*
    | Local time of the family, for the Christmas countdown. The app itself
    | stays on UTC, so stored timestamps don't change.
    */
    'timezone' => env('WISHBOX_TIMEZONE', 'Europe/Zurich'),

];
