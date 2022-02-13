<?php

/**
 * Defines message providers (types of messages being sent)
 *
 * @package   mod_hittrainingsnachweis
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$messageproviders = array(
    'trainingsnachweiserstellt' => array(
        //'capability' => 'mod/hittrainingsnachweis:edit',
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN ,
            'email' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDOFF,
        ],
    ),
    // Confirm a student's quiz attempt
    'trainingsnachweisabgezeichnet' => array (
        //'capability'  => 'mod/hittrainingsnachweis:read'
        'defaults' => [
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
            'email' => MESSAGE_PERMITTED
        ],
    )
);