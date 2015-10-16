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
 *  OTRS Block - Generic Interface SOAP Library
 *
 *  This filter will replace defined cloudfront URLs with signed
 *  URLs as described at http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-urls.html
 *
 * @package    block
 * @subpackage otrs
 * @copyright  2015 Owen Barritt, Wine & Spirit Education Trust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class otrsgenericinterface {

    function __construct() {
        global $CFG;

        $params = array(
            'location' => get_config('block_otrs','giurl'),
            'uri' => get_config('block_otrs','gins'),
            'trace' => 1,
            'login' => get_config('block_otrs','giuser'),
            'password' => get_config('block_otrs', 'gipassword'),
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

    private function dispatch( $method, $params ) {
        // prepend username and password to soap parameters.
        array_unshift($params, new SoapParam(get_config('block_otrs','giuser'),"UserLogin"), new SoapParam(get_config('block_otrs','gipassword'),"Password"));

        // do soap call
        try {
            $result = $this->_client->__soapCall($method, $params);
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

        debugging("OTRS Generic Interface SOAP Request: " . s($this->_client->__getLastRequest()), DEBUG_DEVELOPER);
        debugging("OTRS Generic Interface SOAP Response: " . s($this->_client->__getLastResponse()), DEBUG_DEVELOPER);

        return $result;
    }

    public function TicketCreate( $Customer, $Title, $message, $queue , $sendertype = 'customer', $articletype = 'webrequest', $mimetype='text/html', $priority = 3, $dynamicfields = array(), $attachments = array(), $extraticketparams = array(), $extraarticleparams = array() ) {
        // Use default queue if not selected
        if(empty($queue)){
            $queue = get_config('block_otrs','queue');
        }

        $method = 'TicketCreate';

        // Create ticket and article.
        $ticket = array_merge(
            array(
                'Title' => $Title,
                'Queue' => $queue,
                'PriorityID' => $priority,
                'State' => 'new',
                'CustomerUser' => $Customer,
                ),
            $extraticketparams
            );
        $article = array_merge(
            array(
                'Subject' => $Title,
                'Body' => $message,
                'SenderType' => $sendertype,
                'ArticleType' => $articletype,
                'ContentType' => "$mimetype; charset=UTF8",
                ),
            $extraarticleparams
            );

        // Convert to SoapParams.
        $params = array(
            new SoapParam($ticket, "Ticket"),
            new SoapParam($article, "Article")
            );

        // Add any dynamic fields.
        foreach ($dynamicfields as $name => $value) {
            $params[] = new SoapParam(
                array(
                    'Name' => $name,
                    'Value' => $value,
                    ),
                "DynamicField"
            );
        }

        // Add any attachments.
        foreach ($attachments as $file) {
            $base64content = base64_encode( $file->get_content() );
            $params[] = new SoapParam(
                array(
                    'Content' => $base64content,
                    'Filename' => $file->get_filename(),
                    'ContentType' => $file->get_mimetype(),
                    ),
                "Attachment"
            );
        }

        // Dispatch to OTRS.
        $TicketID = $this->dispatch( $method, $params );

        if(is_array($TicketID) && $TicketID['TicketNumber']){
            debugging("Ticket Created: " . $TicketID['TicketNumber']);
        }
        else {
            debugging("Error Creating Ticket: " . $TicketID->ErrorMessage);
        }
        return $TicketID;
    }

    public function ArticleCreate( $Ticket, $From, $Title, $message, $sendertype = 'customer', $articletype = 'webrequest', $mimetype='text/html', $dynamicfields = array(), $attachments = array(), $extraarticleparams = array()) {
        $method = 'TicketUpdate';

        // Create article.
        $article = array_merge(
            array(
                'From' => $From,
                'Subject' => $Title,
                'Body' => $message,
                'SenderType' => $sendertype,
                'ArticleType' => $articletype,
                'ContentType' => "$mimetype; charset=UTF8",
                ),
            $extraarticleparams
            );

        // Convert to SoapParams.
        $params = array(
            new SoapParam($Ticket, "TicketID"),
            new SoapParam($article, "Article")
            );

        // Check ticket state and reopen if necessary.
        $CurTicket = $this->GetTicket( $Ticket );

        if( $CurTicket[0]->State != 'new' ) {
            $params[] = new SoapParam(array(
                'State' => 'open',
                ), "Ticket");
        }


        // Add any dynamic fields.
        foreach ($dynamicfields as $name => $value) {
            $params[] = new SoapParam(
                array(
                    'Name' => $name,
                    'Value' => $value,
                    ),
                "DynamicField"
            );
        }

        // Add any attachments.
        foreach ($attachments as $file) {
            $base64content = base64_encode( $file->get_content() );
            $params[] = new SoapParam(
                array(
                    'Content' => $base64content,
                    'Filename' => $file->get_filename(),
                    'ContentType' => $file->get_mimetype(),
                    ),
                "Attachment"
            );
        }

        // Dispatch to OTRS.
        $TicketID = $this->dispatch( $method, $params );

        if(is_array($TicketID) && isset($TicketID['TicketNumber'])) {
            debugging("Ticket Updated: " . $TicketID['TicketNumber']);
        } else {
            debugging("Error Updating Ticket: " . $TicketID->ErrorMessage);
        }
        return $TicketID;
    }

    public function GetTicket ( $TicketID, $articles = false, $dynamicfields = false, $attachments = false ) {
        $method = 'TicketGet';

        $params = array(new SoapParam($TicketID, "TicketID"));

        if( $articles ){
            $params[] = new SoapParam(1,"AllArticles");
        }
        if( $dynamicfields ){
            $params[] = new SoapParam(1,"DynamicFields");
        }
        if( $attachments ){
            $params[] = new SoapParam(1,"Attachments");
        }

        $Ticket = $this->dispatch( $method, $params );

        if(is_array($Ticket) && isset($Ticket['Ticket'])) {
            debugging(count($Ticket['Ticket']). " tickets retrieved.");
            return $Ticket['Ticket'];
        } else if (isset($Ticket->TicketNumber)) {
            debugging("Ticket Retreived: " . $Ticket->TicketNumber);
            return array($Ticket);
        } else {
            debugging("Error Getting Ticket: " . $Ticket->ErrorMessage);
        }
        return false;
    }

    public function ListTickets ( $Customer, $articles = false, $dynamicfields = false ) {
        $method = 'TicketSearch';

        $params = array(new SoapParam($Customer, "CustomerUserLogin"));

        $Tickets = $this->dispatch( $method, $params );

        if(is_array($Tickets) && isset($Tickets['TicketID'])) {
            debugging(count($Tickets['TicketID']). " tickets found.");
            $ReturnTickets = $this->GetTicket( $Tickets['TicketID'], $articles, $dynamicfields );
            return $ReturnTickets;
        } else if (isset($Tickets->TicketNumber)) {
            debugging("Ticket Found: " . $Ticket->TicketNumber);
            return $this->GetTicket( $Ticket->TicketNumber, $articles, $dynamicfields );
        } else {
            debugging("Error Getting Tickets: " . $Tickets->ErrorMessage);
        }

        return false;
    }
}
