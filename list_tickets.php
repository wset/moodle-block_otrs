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
require_once( dirname(__FILE__).'/otrssoap.class.php' );
require_once( dirname(__FILE__).'/otrslib.class.php' );

// get parameters
$id = required_param( 'id', PARAM_INT ); // block id
$state = optional_param( 'state','open', PARAM_ALPHA );
$userid = optional_param( 'user', 0, PARAM_INT );
$courseid = required_param('courseid', PARAM_INT);

// get block
$block = $DB->get_record( 'block_instances', array('id'=>$id), '*', MUST_EXIST );

// get course
$course = $DB->get_record( 'course', array('id'=>$courseid), '*', MUST_EXIST );

// security stuff
require_login( $course );
$context = context_block::instance( $id );
require_capability( 'block/otrs:view', $context );

// navigation
$PAGE->set_course($course);
$PAGE->navbar->add(get_string('pluginname', 'block_otrs'));
$PAGE->navbar->add(get_string('listtickets', 'block_otrs'));
$PAGE->set_url('/blocks/otrs/list_tickets.php', array('id'=>$courseid));
$PAGE->set_heading( get_string('pluginname', 'block_otrs' ));
$PAGE->set_pagetype('otrs');

// Get renderer
$renderer = $PAGE->get_renderer('block_otrs');

// check state
if ($state=='all') {
    $closed = true;
    $open = true;
} else if ($state=='closed') {
    $closed = true;
    $open = false;
} 
else {
    $open = true;
    $closed = false;
}

// course url
$url = new moodle_url("/course/view.php", array('id'=>$courseid));

// view ticket url
$ticketurl = new moodle_url("/blocks/otrs/view_ticket.php", array('id'=>$id, 'courseid'=>$courseid));

// this (base) url
$listurl = new moodle_url("/blocks/otrs/list_tickets.php", array('id'=>$id, 'courseid'=>$courseid));

// get correct user
if (!empty($userid)) {
    if ($userid != $USER->id) {
        require_capability( 'block/otrs:viewothers', $context );
    }
    $user = $DB->get_record( 'user', array('id'=>$userid) );
    $ticketurl .= "&user=$userid";
    $listurl .= "&user=$userid";
}
else {
    $user = $USER;
}

// get tickets
$Tickets = otrslib::getBlockTickets( $user );

echo $OUTPUT->header();
echo $OUTPUT->heading( get_string('listtickets','block_otrs' ) );

echo $renderer->OpenClosed( $listurl, $state );
echo $renderer->listTickets( $Tickets, $id, $ticketurl, $open, $closed );

echo $OUTPUT->footer();
