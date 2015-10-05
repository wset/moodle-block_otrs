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


require_once( '../../config.php' );
require_once( 'otrssoap.class.php' );
require_once( 'otrslib.class.php' );
require_once( 'create_ticket_form.php' );

// get parameters
$id = required_param( 'id', PARAM_INT ); // block id

// get block
$block = $DB->get_record( 'block_instances', array('id'=>$id) );
$courseid = $COURSE->id;

// get course
$course = $DB->get_record( 'course', array('id'=>$courseid) );

// security stuff
require_login( $course );
$context = context_block::instance( $id );
require_capability( 'block/otrs:create', $context );

// course url
$url = new moodle_url("/course/view.php", array('id'=>$courseid));

// create additional information
$coursetext = 'Course name: ' . $course->fullname .'<br />';
$coursetext .= "Course link: <a href=\"$url\">$url</a><br /><br />";

// usual formslib stuff for entering data
$mform = new create_ticket_form( null, array('id'=>$id, 'description'=>$coursetext));
if ($mform->is_cancelled()) {

    // just back to form page
    redirect( $url );

} else if ($data = $mform->get_data() ) {

    // get the data
    $subject = $data->subject;
    $description = $data->description;


    // find/create the user in OTRS
    $Data = otrslib::getUserId( $USER );

    // get mimetype from format
    $mimetype = otrslib::getMimetype( $description['format'] );

    // create a ticket in OTRS
    $otrssoap = new otrssoap();
    $TicketID = $otrssoap->TicketCreate( $subject, $Data['UserCustomerID'], $Data['UserEmail'] );
    $ArticleID = $otrssoap->ArticleCreate( $TicketID, $subject, $description['text'], $USER->email, 'Support', $mimetype );
    $success = $otrssoap->TicketCustomerSet( $TicketID, $Data['UserCustomerID'], $Data['UserLogin'] );

    // back to course
    $PAGE->set_url('/blocks/otrs/create_ticket.php', array('id'=>$courseid));
    echo $OUTPUT->header();
    redirect( $url, get_string( 'ticketcreated','block_otrs' ), 3 );
}
else {
    // navigation
    //$PAGE->set_course($course);
    $PAGE->navbar->add(get_string('pluginname', 'block_otrs'));
    $PAGE->set_url('/blocks/otrs/create_ticket.php', array('id'=>$courseid));
    $PAGE->set_heading( get_string('pluginname', 'block_otrs' ));
    $PAGE->set_pagetype('otrs');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('createticket', 'block_otrs'));
    $mform->display();
    echo $OUTPUT->footer();
}
