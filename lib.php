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

require_once( 'otrssoap.class.php' );
require_once( 'otrslib.class.php' );

function block_otrs_user_updated($event) {
    global $DB, $USER;

    if ($event->id == $USER->id) {
        // user is updating themselves so we create a ticket.
        // What did they change?
        $userarr = (array) $USER;
        $usernew = (array) $event;
        $changestring = '';
        foreach ($usernew as $key => $value) {
            if ($key == 'timemodified') {
                continue;
            }
            if (isset($userarr[$key]) && ($userarr[$key] != $value)) {
                if ($key == 'password') {
                    $changestring .= 'password, ';
                } else {
                    $changestring .= "$key - " . $value . ", ";
                }
            }
        }
        
        // create a ticket in OTRS
        $subject = 'User updated notification for ' . $event->username;
        $message = 'User ' . $event->username . ' has updated their user details for ' . $changestring;

        // find/create the user in OTRS
        $Data = otrslib::getUserId( $event );

        $otrssoap = new otrssoap();
        $TicketID = $otrssoap->TicketCreate( $subject, $Data['UserCustomerID'], $Data['UserEmail'], 'userupdate' );
        $ArticleID = $otrssoap->ArticleCreate( $TicketID, $subject, $message, $USER->email, 'Support', 'text/plain' );
        $success = $otrssoap->TicketCustomerSet( $TicketID, $Data['UserCustomerID'], $Data['UserLogin'] );
    }
}
