<?php
/**
 * OTRS Integration Block
 *
 * @author Howard Miller
 * @version  See version in block_otrs.php
 * @copyright Copyright (c) 2011-2014 E-Learn Design Limited
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package block_otrs
 */

require_once( dirname(__FILE__).'/../../config.php' );
require_once( dirname(__FILE__).'/otrsgenericinterface.class.php' );
require_once( dirname(__FILE__).'/otrslib.class.php' );

// get parameters
$id = required_param( 'id', PARAM_INT ); // block id note
$TicketID = required_param( 'ticket', PARAM_INT );
$ArticleID = optional_param( 'article',0,PARAM_INT );
$userid = optional_param( 'user',0,PARAM_INT );
$courseid = required_param('courseid', PARAM_INT);

// get block
$block = $DB->get_record('block_instances', array('id'=>$id), '*', MUST_EXIST);

// get course
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

// security stuff
require_login( $course );
$context = context_block::instance( $id );
require_capability( 'block/otrs:view', $context );

// navigation
$PAGE->set_course($course);
$PAGE->navbar->add(get_string('pluginname', 'block_otrs'));
$PAGE->navbar->add(get_string('viewticket', 'block_otrs'));
$PAGE->set_url('/blocks/otrs/view_ticket.php', array('id'=>$courseid, 'ticket'=>$TicketID));
$PAGE->set_heading( get_string('pluginname', 'block_otrs' ));
$PAGE->set_pagetype('otrs');

// Get renderer
$renderer = $PAGE->get_renderer('block_otrs');

// course url
$url = new moodle_url("/course/view.php", array('id'=>$courseid));

// view ticket url
$ticketurl = new moodle_url("/blocks/otrs/view_ticket.php", array('id'=>$id, 'ticket'=>$TicketID, 'courseid'=>$courseid));

// insufficient user check :(
if (!empty($userid)) {
    if ($userid != $USER->id) {
        require_capability( 'block/otrs:viewothers', $context );
    }
    $ticketurl .= "&user=$userid";
}
else {
    $userid = $USER->id;
}

// get ticket
$otrssoap = new otrsgenericinterface();
$Tickets = $otrssoap->GetTicket( $TicketID, true );
$Ticket = $Tickets[0];

// get title
$title = $Ticket->Title . "  <span class=\"otrs_state\">($Ticket->State)</span>";

// Ensure Articles are listed in an array even if there is just one.
if(is_array($Ticket->Article)) {
    $TicketArticles = $Ticket->Article;
} else {
    $TicketArticles = array($Ticket->Article);
}

// select Articles for display
$Articles = array();
foreach ($TicketArticles as $Article) {
    if(!(strpos($Article->ArticleType, 'internal') ) && !(strpos($Article->ArticleType, 'report') )){ // Skip internal and report articles types.
        $Articles[] = $Article;
    }
    if ($Article->ArticleID == $ArticleID) {
        $SelectedArticle = $Article;
    }
}
if (empty($ArticleID)) {
    $SelectedArticle = $Articles[ count($Articles)-1 ];
}

// get Moodle format from mimetype
$format = otrslib::getFormat( $SelectedArticle->MimeType );

echo $OUTPUT->header();
echo $OUTPUT->heading( $title );

echo $renderer->ArticleTable( $Articles, $ticketurl, $SelectedArticle->ArticleID );

echo $renderer->SingleArticle( $SelectedArticle, $format );

if ($userid == $USER->id) {
    $link = new moodle_url('/blocks/otrs/add_comment.php', array('id'=>$id, 'ticket'=>$TicketID, 'courseid'=>$courseid));
    echo $renderer->CommentButton($link);
}

echo $OUTPUT->footer();
