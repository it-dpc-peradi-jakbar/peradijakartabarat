<?php

return [
    'logbook' => filter_var(env('FEATURE_LOGBOOK', false), FILTER_VALIDATE_BOOLEAN),
];
