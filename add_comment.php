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

require_once( dirname(__FILE__).'/../../config.php' );
require_once( dirname(__FILE__).'/otrsgenericinterface.class.php' );
require_once( dirname(__FILE__).'/otrslib.class.php' );
require_once( dirname(__FILE__).'/add_comment_form.php' );

// get parameters
$id = required_param( 'id', PARAM_INT ); // block id
$courseid = required_param('courseid', PARAM_INT);
$TicketID = required_param( 'ticket', PARAM_INT );

// get block
$block = $DB->get_record( 'block_instances', array('id'=>$id), '*', MUST_EXIST );

// get course
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

// security stuff
require_login( $course );
$context = context_block::instance( $id );
require_capability( 'block/otrs:addcomment', $context );

// navigation
$PAGE->set_course($course);
$PAGE->navbar->add(get_string('pluginname', 'block_otrs'));
$PAGE->navbar->add(get_string('addcomment', 'block_otrs'));
$commenturl = new moodle_url('/blocks/otrs/add_comment.php', array('id'=>$courseid, 'ticket'=>$TicketID, 'courseid'=>$courseid));
$PAGE->set_url($commenturl);

// view ticket url
$url = new moodle_url("/blocks/otrs/view_ticket.php", array('id'=>$id, 'ticket'=>$TicketID, 'courseid'=>$courseid));

// get articles
$otrssoap = new otrsgenericinterface();
$Tickets = $otrssoap->GetTicket( $TicketID );
$Ticket = $Tickets[0];

// usual formslib stuff for entering data
$mform = new add_comment_form($commenturl, array('id'=>$id, 'ticket'=>$TicketID, 'title'=>$Ticket->Title, 'courseid'=>$courseid));
if ($mform->is_cancelled()) {

    // just back to form page
    redirect( $url );

} else if ($data = $mform->get_data() ) {

    // get the data
    $comment = $data->comment;
    $subject = $data->subject;

    // get mimetype from format
    $mimetype = otrslib::getMimetype( $comment['format'] );

    // add a comment to ticket

    $Ticket = $otrssoap->ArticleCreate( $TicketID, '"'. fullname($USER) .'" <'.$USER->email.'>', $subject, $comment['text']);

    // back to course
    redirect( $url, get_string( 'commentadded','block_otrs' ), 3 );
}
else {
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
