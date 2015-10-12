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


class otrssoap {

    function __construct() {
        global $CFG;

        // user id for agent
        $this->agentid = 1;

        $params = array(
            'location' => $CFG->block_otrs_url,
            'uri' => 'Core',
            'trace' => 1,
            'login' => $CFG->block_otrs_otrsuser,
            'password' => $CFG->block_otrs_otrspassword,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED
            );

        // check for proxy
        if (!empty($CFG->proxyhost)) {
            $params['proxy_host'] = $CFG->proxyhost;
        }
        if (!empty($CFG->proxyport)) {
            $params['proxy_port'] = $CFG->proxyport;
        }

        $this->_client = new SoapClient( null, $params );
    }

    private function dispatch( $object, $method, $params ) {
        global $CFG;

        // construct array of params for call
        $p = array();
        $p[] = $CFG->block_otrs_otrsuser;
        $p[] = $CFG->block_otrs_otrspassword;
        $p[] = $object;
        $p[] = $method;

        // remainder by unpacking pairs in $params
        foreach ($params as $key => $value ) {
            $p[] = $key;
            $p[] = $value;
        }

        // do soap call
        try {
            $result = $this->_client->__soapCall('Dispatch', $p);
        } catch (SoapFault $e) {
            echo 'OTRS Soap call failed '.$e;
            //echo '<br /><pre>'.$this->_client->__getLastResponse().'</pre>';
            $result = false;
        }

        // another check (sigh!)
        if (is_soap_fault($result)) {
            echo 'OTRS Soap call returned fault - '.$result->faultstring;
            $result = false;
        }

        // and again
        if (empty($result)) {
        //    echo 'OTRS Soap call returned no data (check OTRS logs) ';
            $result = false;
        }

        debugging("OTRS RPC SOAP Request: " . s($this->_client->__getLastRequest()), DEBUG_DEVELOPER);
        debugging("OTRS RPC SOAP Response: " . s($this->_client->__getLastResponse()), DEBUG_DEVELOPER);

        return $result;
    }

    //
    // CUSTOMER USER OBJECT
    //

    /**
     * Add custom fields
     */
    private function addCustomFields( &$params, $profile ) {
        global $CFG;

        // if profile is null then nothing to do
        if (empty($profile)) {
            return false;
        }

        // if settings empty then ditto
        if (empty($CFG->block_otrs_userfields)) {
            return false;
        }

        // turn block_otrs_userfields into assoc array
        $pairs = explode( ',',$CFG->block_otrs_userfields );
        $fields = array();
        foreach ($pairs as $pair) {
            $mapping = explode( '=',trim($pair) );
            $data = new stdClass();
            $data->local = $mapping[0];
            $data->otrs = $mapping[1];
            $fields[] = $data;
        }

        // add to params
        foreach ($fields as $field) {
            $local = $field->local;
            if (!empty( $profile->$local )) {
                $params[$field->otrs] = $profile->$local;
            }
        }
    }

    /**
     * Create a new user
     * param object $user moodle user object
     */
    public function CustomerUserAdd( $user, $profile ) {
        global $CFG;

        $object = 'CustomerUserObject';
        $method = 'CustomerUserAdd';
        $params = array(
            'Source' => 'CustomerUser',
            'UserFirstname' => $user->firstname,
            'UserLastname' => $user->lastname,
            'UserCustomerID' => $user->id,
            'UserLogin' => $user->username,
            'UserEmail' => $user->email,
            'ValidID' => 1,
            'UserID' => $this->agentid,
            'moodle_url' => fullname( $user ),
            'moodleID' => $user->id,
            );
        self::addCustomFields( $params, $profile );
        $UserLogin = $this->dispatch( $object,$method,$params );
        return $UserLogin;
    }

    /**
     * Update existing customer user
     * param object $user moodle user object
     */
    public function CustomerUserUpdate( $user, $profile, $olduser ) {
        global $CFG;

        $object = 'CustomerUserObject';
        $method = 'CustomerUserUpdate';
        $params = array(
            'Source' => 'CustomerUser',
            'ID' => $user->username,
            'UserFirstname' => $user->firstname,
            'UserLastname' => $user->lastname,
            'UserCustomerID' => $user->id,
            'UserLogin' => $user->username,
            'UserEmail' => $user->email,
            'ValidID' => 1,
            'UserID' => $this->agentid,
            'moodle_url' => fullname( $user ),
            'moodleID' => $user->id,
            );

        //  Change userlogins for existing accounts
        if (!empty( $olduser )) {
            $params['ID'] = $olduser;
        }
        self::addCustomFields( $params, $profile );
        $this->dispatch( $object,$method,$params );
        return true;
    }

    /**
     * Search for users
     * @param string Search search token
     */
    public function CustomerSearch( $Search ) {
        $object = 'CustomerUserObject';
        $method = 'CustomerSearch';
        $params = array(
            'Search' => $Search,
            );
        $List = $this->dispatch( $object, $method, $params );
        return self::unserialise($List);
    }

    /**
     * Search for username
     * @param string Username
     */
    public function CustomerIDs( $Username ) {
        $object = 'CustomerUserObject';
        $method = 'CustomerIDs';
        $params = array(
            'User' => $Username,
            );
        $ID = $this->dispatch( $object, $method, $params );
        return $ID;
    }

    /**
     * Search for user data
     * @param string Username
     */
    public function CustomerUserDataGet( $Username ) {
        $object = 'CustomerUserObject';
        $method = 'CustomerUserDataGet';
        $params = array(
            'User' => $Username,
            );
        $Data = $this->dispatch( $object, $method, $params );
        return self::unserialise($Data);
    }

    /**
     * Search for user by email
     * @param string Email
     */
    public function CustomerEmailSearch( $Email ) {
        $object = 'CustomerUserObject';
        $method = 'CustomerSearch';
        $params = array(
            'PostMasterSearch' => $Email,
            );
        $Data = $this->dispatch( $object, $method, $params );
        return self::unserialise($Data);
    }


    //
    // TICKET OBJECT
    //

    /**
     * Obtain a new ticket number
     */
    public function TicketCreateNumber() {
        $object = 'TicketObject';
        $method = 'TicketCreateNumber';
        $TicketNumber = $this->dispatch( $object,$method,array() );
        return $TicketNumber;
    }

    /**
     * Set ticket state
     */
    public function TicketStateSet( $TicketID, $state) {
        $object = 'TicketObject';
        $method = 'TicketStateSet';
        $params = array(
            'State' => $state,
            'TicketID' => $TicketID,
            'UserID' => $this->agentid,
            );
        $Success = $this->dispatch( $object,$method,$params );
        return $Success;
    }

    /**
     * Create a new ticket
     */
    public function TicketCreate( $Title, $CustomerId, $CustomerUser, $queue = '' ) {
        global $CFG;

        // Work out which queue to use.
        if ($queue == 'quiz' && !empty($CFG->block_otrs_quiz_queue)) {
            $queuename = $CFG->block_otrs_quiz_queue;
        } else if ($queue == 'completion' && !empty($CFG->block_otrs_completion_queue)) {
            $queuename = $CFG->block_otrs_completion_queue;
        } else if ($queue == 'userupdate' && !empty($CFG->block_otrs_user_update_queue)) {
            $queuename = $CFG->block_otrs_user_update_queue;
        } else {
            $queuename = $CFG->block_otrs_queue;
        }
        $object = 'TicketObject';
        $method = 'TicketCreate';
        $params = array(
            'Title' => $Title,
            'Queue' => $queuename,
            'Lock' => 'unlock',
            'Priority' => '3 normal',
            'State' => 'new',
            'CustomerID' => $CustomerId,
            //'CustomerUser' => $CustomerUser,
            'OwnerID' => $this->agentid,
            'UserID' => $this->agentid,
            );
        $TicketID = $this->dispatch( $object,$method,$params );
        return $TicketID;
    }

    /**
     * Create a new article
     */
    public function ArticleCreate( $TicketID, $Subject, $Body, $From, $To='Support',$mimetype='text/html' ) {
        global $CFG;

        $object = 'TicketObject';
        $method = 'ArticleCreate';
        $params = array(
            'TicketID' => $TicketID,
            'ArticleType' => 'webrequest',
            'SenderType' => 'customer',
            'HistoryType' => 'WebRequestCustomer',
            'HistoryComment' => 'Created by Moodle',
            'Subject' => $Subject,
            'Body' => $Body,
            'From' => $From,
            'To' => $To,
            'UserID' => $this->agentid,
            'ContentType' => "$mimetype; charset=UTF8",
            );
        $ArticleID = $this->dispatch( $object, $method, $params );
        return $ArticleID;
    }

    /**
     * Search for tickets by customer login (username)
     */
    public function TicketSearchUsername( $username ) {
        $object = 'TicketObject';
        $method = 'TicketSearch';
        $params = array(
            'Result' => 'ARRAY',
            'CustomerUserLogin' => $username,
            'UserID' => $this->agentid,
            );
        $TicketIDs = $this->dispatch( $object, $method, $params );
        return self::arrayize($TicketIDs);
    }

    /**
     * Get ticket details
     */
    public function TicketGet( $TicketID ) {
        $object = 'TicketObject';
        $method = 'TicketGet';
        $params = array(
            'TicketID' => $TicketID,
            'UserID' => $this->agentid,
            );
        $Ticket = $this->dispatch( $object, $method, $params );
        return self::unserialise( $Ticket );
    }

    /**
     * Get ticket's first article
     */
    public function ArticleFirstArticle( $TicketID ) {
        $object = 'TicketObject';
        $method = 'ArticleFirstArticle';
        $params = array(
            'TicketID' => $TicketID,
            );
        $Article = $this->dispatch( $object, $method, $params );
        return self::unserialise( $Article );
    }

    /**
     * Get article
     */
    public function ArticleGet( $ArticleID ) {
        $object = 'TicketObject';
        $method = 'ArticleGet';
        $params = array(
            'ArticleID' => $ArticleID,
            'UserID' => $this->agentid,
            );
        $Article = $this->dispatch( $object, $method, $params );
        return self::unserialise( $Article );
    }

    /**
     * Get ticket's article index
     */
    public function ArticleIndex( $TicketID ) {
        $object = 'TicketObject';
        $method = 'ArticleIndex';
        $params = array(
            'TicketID' => $TicketID,
            );
        $ArticleIDs = $this->dispatch( $object, $method, $params );
        return self::arrayize( $ArticleIDs );
    }

    /**
     * Get array of articles
     */
    public function ArticleContentIndex( $TicketID ) {
        $object = 'TicketObject';
        $method = 'ArticleContentIndex';
        $params = array(
            'TicketID' => $TicketID,
            'UserID' => $this->agentid,
            );
        $ArticleBox = $this->dispatch( $object, $method, $params );
        return self::arrayize( $ArticleBox );
    }

    /**
     * Set ticket customer
     */
    public function TicketCustomerSet( $TicketID, $userid, $username ) {
        $object = 'TicketObject';
        $method = 'TicketCustomerSet';
        $params = array(
            'TicketID' => $TicketID,
            'User' => $username,
            'UserID' => $this->agentid,
            );
        $success = $this->dispatch( $object, $method, $params );
        return $success;
    }

    /**
     * Convert OTRS list to associative array
     */
    static function unserialise( $List ) {
        $assoc = array();
        $count = floor(count( $List ) / 2);
        for ($i=1; $i<=$count; $i++) {
            $key = array_shift( $List );
            $assoc[ $key ] = array_shift( $List );
        }
        return $assoc;
    }

    /**
     * Make sure result is an array (one result can be integer)
     */
    static function arrayize( $List ) {
        if (empty($List)) {
            return array();
        }
        if (!is_array( $List )) {
            return array( $List );
        }
        else {
            return $List;
        }
    }


}
