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

define( 'MAX_PROFILE',5 );

require_once( 'otrsgenericinterface.class.php' );
require_once( 'otrslib.class.php' );

function block_otrs_user_updated($event) {
    global $DB, $USER;

    // update user record on OTRS.
    $usernew = $event->get_record_snapshot('user', $event->objectid);
    otrslib::userupdate($usernew);
    $umatchfields = get_config('block_otrs','usermatchfields');

    if ($event->objectid == $USER->id && $umatchfields) {
        // user is updating themselves so we create a ticket.
        // What did they change?
        $userarr = (array) $USER;
        $changestring = '';
        $ufields = explode(',',$umatchfields);
        foreach ($usernew as $key => $value) {
            if ($key == 'timemodified') {
                continue;
            }

            if (isset($userarr[$key]) && ($userarr[$key] != $value)) {
                if(array_search($key,$ufields)) {
                    if ($key == 'password') {
                        $changestring .= get_string('password'). ",<br />";
                    } else if ($key == 'country') {
                        $countries = get_string_manager()->get_list_of_countries(false);
                        $changestring .= get_string('country') . " - " . $countries[$value] . ",<br />";
                    } else {
                        $changestring .= get_string($key) . " - " . $value . ",<br />";
                    }
                }

            }
        }

        // create a ticket in OTRS
        if($changestring != '') {
            $subject = 'User updated notification for ' . $usernew->username;
            $message = 'User ' . $usernew->username . ' has updated their user profile as follows:<br /><br />' . $changestring;

            $otrssoap = new otrsgenericinterface();
            $Ticket = $otrssoap->TicketCreate( $usernew->username, $subject, $message, get_config('block_otrs','user_update_queue'), 'system', 'note-report');
        }
    }


}

function block_otrs_notes_updated($event) {
    // update user record on OTRS
    $user=core_user::get_user($event->relateduserid);
    otrslib::userupdate($user,true);
}


function block_otrs_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE;

    $context = context_user::instance( $user->id );
    $content = '';

    if(has_capability( 'block/otrs:viewothers',$context)) {
        $Tickets = otrslib::getBlockTickets( $user, false, MAX_PROFILE +1 );

        if(count($Tickets)) {
            $category = new core_user\output\myprofile\category('tickets', get_string('usertickets', 'block_otrs'), null);
            $tree->add_category($category);

            $count = 1;
            foreach($Tickets as $Ticket) {
                if($count<=MAX_PROFILE) {
                    $urlparams = array('ticket'=>$Ticket->TicketID);
                    if(isset($course->id)) {
                        $urlparams['courseid'] = $course->id;
                    }
                    $link = new moodle_url("/blocks/otrs/view_ticket.php", $urlparams);
                    $node = new core_user\output\myprofile\node('tickets','ticket'.$Ticket->TicketID, $Ticket->Title, null, $link);
                    $tree->add_node($node);
                    $count++;
                }
            }

            if ($count > MAX_PROFILE) {
                $linkstr = get_string('moretickets','block_otrs');
            } else {
                $linkstr = get_string('fulltickets','block_otrs');
            }

            $urlparams = array('userid'=>$user->id);
            if(isset($course->id)) {
                $urlparams['courseid'] = $course->id;
            }
            $link = new moodle_url('/blocks/otrs/list_tickets.php', $urlparams);

            $node = new core_user\output\myprofile\node('tickets','fulltickets', $linkstr, null, $link);
            $tree->add_node($node);
        }
    }
}
