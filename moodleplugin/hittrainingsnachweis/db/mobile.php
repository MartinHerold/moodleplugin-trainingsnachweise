<?php
$addons = [
    'mod_hittrainingsnachweis' => [ // Plugin identifier
        'handlers' => [ // Different places where the plugin will display content.
            'hittrainingsnachweiscontentview' => [ // Handler unique name (alphanumeric).
                'displaydata' => [
                    'icon' => $CFG->wwwroot . '/mod/hittrainingsnachweis/pix/icon.svg',
                    'class' => '',
                ],

                'delegate' => 'CoreCourseModuleDelegate', // Delegate (where to display the link to the plugin)
                'method' => 'mobile_trainingsnachweis_view', // Main function in \mod_certificate\output\mobile
                'offlinefunctions' => [

                ], // Function that needs to be downloaded for offline.
            ],
        ],
        'lang' => [ // Language strings that are used in all the handlers.
            ['pluginname', 'hittrainingsnachweis'],
        ],
    ],
];