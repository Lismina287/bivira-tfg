<?php
defined('MOODLE_INTERNAL') || die();

$messageproviders = [
    'nueva_solicitud' => [
        'capability' => 'moodle/site:config',
        'defaults'   => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED,
            'email'  => MESSAGE_PERMITTED,
        ],
    ],
];
