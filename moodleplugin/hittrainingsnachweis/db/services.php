<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Certificate external functions and service definitions.
 *
 * @package    mod_hittrainingsnachweis
 * @category   external
 * @copyright
 * @license
 */

$services = array(
    'mobile_new_trainingsnachweis' => array(                                                // the name of the web service
        'functions' => array ('mobile_new_trainingsnachweis'), // web service functions of this service
        'requiredcapability' => 'mod/hittrainingsnachweis:edit',   // if set, the web service user need this capability to access
        // any function of this service. For example: 'some/capability:specified'
        'restrictedusers' => 0,  // if enabled, the Moodle administrator must link some user to this service
        // into the administration
        'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
        'shortname' =>  'mobile_new_trainingsnachweis',       // optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 0,    // allow file downloads.
        'uploadfiles'  => 1      // allow file uploads.
    ),
    'mobile_trainingsnachweis_abzeichnen' => array(                                                // the name of the web service
        'functions' => array ('mobile_trainingsnachweis_abzeichnen'), // web service functions of this service
        'requiredcapability' => 'mod/hittrainingsnachweis:edit',   // if set, the web service user need this capability to access
        // any function of this service. For example: 'some/capability:specified'
        'restrictedusers' => 0,  // if enabled, the Moodle administrator must link some user to this service
        // into the administration
        'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
        'shortname' =>  'mobile_trainingsnachweis_abzeichnen',       // optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 0,    // allow file downloads.
        'uploadfiles'  => 1      // allow file uploads.
    )
);

$functions = array(
    'mobile_new_trainingsnachweis' => array(
        'classname'     => 'hittrainingsnachweis_external',
        'methodname'    => 'mobile_create_new_trainingsnachweis',
        'description'   => 'Create new Trainingsnachweis',
        'type'          => 'write',
        'capabilities'  => 'mod/hittrainingsnachweis:edit',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
    'mobile_trainingsnachweis_abzeichnen' => array(
        'classname'     => 'hittrainingsnachweis_external',
        'methodname'    => 'mobile_trainingsnachweis_abzeichnen',
        'description'   => 'Trainingsnachweis Abzeichnen',
        'type'          => 'write',
        'capabilities'  => 'mod/hittrainingsnachweis:edit',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
    ),
);

