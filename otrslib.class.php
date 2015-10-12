<?php

/**
 * OTRS Integration Block
 *
 * @author Howard Miller
 * @version  See version in block_otrs.php
 * @copyright Copyright (c) 2011 E-Learn Design Limited
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block_otrs
 */


require_once( 'otrssoap.class.php' );
require_once( 'otrsgenericinterface.class.php' );

define( 'CLOSED_MAX_AGE',5184000); //60 days (I think)

class otrslib {

    /**
     * Delete closed tickets that are too old
     */
    private static function deleteAgedTickets( $Tickets ) {

        // array to hold cleaned up list
        $tickets_clean = array();

        // iterate through tickets checking
        foreach ($Tickets as $Ticket) {

            // if not a closed ticket then we don't care
            if (stripos($Ticket->State,'closed')===false) {
                $tickets_clean[] = $Ticket;
                continue;
            }

            // check if too old
            if ($Ticket->Age > CLOSED_MAX_AGE) {
                continue;
            }
            else {
                $tickets_clean[] = $Ticket;
            }
        }

        return $tickets_clean;
    }

    /**
     * Get the tickets for block display
     */
    static function getBlockTickets( $user ) {

        // search for tickets
        $otrssoap = new otrsgenericinterface();
        $TicketIDs = $otrssoap->ListTickets( $user->username );

        // did we get any
        if (empty($TicketIDs)) {
            return false;
        }

        return self::deleteAgedTickets( $TicketIDs );
    }

    /**
     * Filter course users with tickets
     * (Check if each user has tickets)
     */
    static function getTicketUsers( $users ) {

        // build list of users with tickets
        $ticketusers = array();

        // iterate over course users
        foreach ($users as $user) {
            $tickets = self::getBlockTickets( $user );
            if (!empty( $tickets )) {
                $ticketusers[] = $user;
            }
        }

        return $ticketusers;
    }

    /**
     * translate from moodle format to correct mimetype
     */
    static function getMimetype( $format ) {
        if ($format == FORMAT_HTML) {
            return 'text/html';
        }
        else {
            return 'text/plain';
        }
    }

    /**
     * translate mimetype to moodle format
     */
    static function getFormat( $mimetype ) {
        if ($mimetype == 'text/html') {
            return FORMAT_HTML;
        }
        else {
            return FORMAT_PLAIN;
        }
    }

    /**
     * Get all (any?) profile fields for a user
     */
    static function getProfileFields( $user ) {
        global $CFG, $DB;

        $sql = "select shortname, data from {user_info_data} as uid, {user_info_field} as uif ";
        $sql .= "where uid.fieldid=uif.id and userid=?";

        if ($fields = $DB->get_records_sql( $sql, array($user->id) )) {
            $profile = $user;
            foreach ($fields as $field) {
                $name = 'profile_field_' . $field->shortname;
                $profile->$name = $field->data;
            }
            return $profile;
        }
        else {
            return $user;
        }
    }

    static function userupdate( $userid ) {
        global $DB;

        // get users from otrs
        $otrssoap = new otrssoap();

        // get full record
        $user = $DB->get_record( 'user', array('id'=>$userid->id) );

        // don't add guest or primary admin
        if ($user->id <= 2) {
            return true;
        }

        // try for OTRS user
        $customer = $otrssoap->CustomerUserDataGet( $user->username );

        if(empty( $customer )) {
            // If no customer with username then check for email address.
            $newcustomers = $otrssoap->CustomerEmailSearch( $user->email );
            if(!empty( $newcustomers )){
                $existinguser = array_keys($newcustomers)[0];
            }
        }

        // get custom profile fields
        $profile = otrslib::getProfileFields( $user );

        // are we adding or updating
        if (!empty( $customer )) {
            debugging( 'Updating OTRS record for '.fullname( $user ));
            $otrssoap->CustomerUserUpdate( $user, $profile, null );
        } else if (!empty( $existinguser )){
            debugging( 'Updating existing OTRS email record for '.fullname( $user ));
            $otrssoap->CustomerUserUpdate( $user, $profile, $existinguser);
        } else {
            debugging( 'Adding '.fullname( $user ). ' to OTRS');
            $otrssoap->CustomerUserAdd( $user, $profile );
        }
        return true;
    }

}
