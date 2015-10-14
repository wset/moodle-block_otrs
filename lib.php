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

defined('MOODLE_INTERNAL') || die;

require_once( 'otrsgenericinterface.class.php' );
require_once( 'otrslib.class.php' );

function block_otrs_user_updated($event) {
    global $DB, $USER, $CFG;

    // update user record on OTRS.
    $usernew = $event->get_record_snapshot('user', $event->objectid);
    otrslib::userupdate($usernew);

    if ($event->objectid == $USER->id) {
        // user is updating themselves so we create a ticket.
        // What did they change?
        $userarr = (array) $USER;
        $changestring = '';
        foreach ($usernew as $key => $value) {
            if ($key == 'timemodified') {
                continue;
            }
            if (isset($userarr[$key]) && ($userarr[$key] != $value)) {
                if ($key == 'password') {
                    $changestring .= get_string('password'). ",<br />";
                } if ($key == 'country') {
                    $countries = get_string_manager()->get_list_of_countries(false);
                    $changestring .= get_string('country') . " - " . $countries[$value] . ",<br />";
                }else {
                    $changestring .= get_string($key) . " - " . $value . ",<br />";
                }
            }
        }

        // create a ticket in OTRS
        $subject = 'User updated notification for ' . $usernew->username;
        $message = 'User ' . $usernew->username . ' has updated their user profile as follows:<br /><br />' . $changestring;

        $otrssoap = new otrsgenericinterface();
        $Ticket = $otrssoap->TicketCreate( $usernew->username, $subject, $message, $CFG->block_otrs_user_update_queue, 'system', 'note-report');
    }


}

function block_otrs_notes_updated($event) {
    // update user record on OTRS    
    $user=core_user::get_user($event->relateduserid);
    otrslib::userupdate($user,true);
}
