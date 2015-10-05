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

define( 'CLOSED_MAX_AGE',5184000); //60 days (I think)

class otrslib {

    /**
     * Get the OTRS Customer User ID
     * Create user if not in OTRS
     * @param $user moodle user object
     */
    static function getUserId( $user ) {

        // get users from otrs
        $otrssoap = new otrssoap();
        $otrsusers = $otrssoap->CustomerSearch( $user->username );
//echo "<pre>"; echo count($otrsusers); print_r($otrsusers); die;

        // if user doesn't exist then we need to add them
        if (!array_key_exists( $user->username, $otrsusers )) {
            $profile = self::getProfileFields( $user->id );
            $otrssoap->CustomerUserAdd( $user, $profile );
        }

        // get data
        $Data = $otrssoap->CustomerUserDataGet( $user->username );

        return $Data;
    }

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

        // get the user info
        $UserData = self::getUserId( $user );

        // search for tickets
        $otrssoap = new otrssoap();
        $TicketIDs = $otrssoap->TicketSearchUsername( $UserData['UserLogin'] );

        // did we get any
        if (empty($TicketIDs)) {
            return false;
        }

        // loop through
        $tickets = array();
        foreach ($TicketIDs as $TicketID) {
            $Article = $otrssoap->ArticleFirstArticle( $TicketID );
            $Ticket = $otrssoap->TicketGet( $TicketID );
            $BlockTicket = array_merge( $Article, $Ticket );
            $tickets[] = (object)$BlockTicket;
        }
//echo "<pre>"; print_r($tickets); die;
        
        return self::deleteAgedTickets( $tickets );
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
     * Get full list of articles for ticket
     */
    static function getArticles( $TicketID ) {

        // get article index
        $otrssoap = new otrssoap();
        $Articles = $otrssoap->ArticleContentIndex( $TicketID );

        // we don't want ones that the punter shouldn't see
        $FilteredArticles = array();
        foreach ($Articles as $Article) {
            if (!in_array( $Article->ArticleType, array( 'note-internal' ))) {
                $FilteredArticles[] = $Article;
            }
        }
 
        return $FilteredArticles;
    }

    /**
     * Get one ticket
     */
    static function getTicket( $TicketID ) {
        $otrssoap = new otrssoap();
        $Ticket = $otrssoap->TicketGet( $TicketID );
        return $Ticket;
    }

    /**
     * Re-open closed ticket
     */
    static function ReopenTicket( $TicketID ) {
        
        // get current state
        $Ticket = self::getTicket( $TicketID );
        $state = $Ticket['State'];

        // if some form of closed then open
        if (stripos( $state, 'closed' ) !== false) {
            $otrssoap = new otrssoap();
            $otrssoap->TicketStateSet( $TicketID, 'open' );
        }
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
    static function getProfileFields( $userid ) {
        global $CFG, $DB;

        //$sql = "select shortname, data from {$CFG->prefix}user_info_data as uid, {$CFG->prefix}user_info_field as uif ";
        //$sql .= "where uid.fieldid=uif.id and userid=$userid";
        $sql = "select shortname, data from {user_info_data} as uid, {user_info_field} as uif ";
        $sql .= "where uid.fieldid=uif.id and userid=?";

        if ($fields = $DB->get_records_sql( $sql, array($userid) )) {
            $profile = new stdClass();
            foreach ($fields as $field) {
                $name = $field->shortname;
                $profile->$name = $field->data;
            }
            return $profile;
        }
        else {
            return null;
        }
    }

}
