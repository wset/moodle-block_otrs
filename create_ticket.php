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
require_once( 'otrsgenericinterface.class.php' );
require_once( 'otrslib.class.php' );
require_once( 'create_ticket_form.php' );

global $CFG;

// get parameters
$id = required_param( 'id', PARAM_INT ); // block id
$courseid = optional_param( 'courseid', 1, PARAM_INT );  //course id
$cmid = optional_param( 'cmid', null, PARAM_INT ); //course module

// get block
$block = $DB->get_record( 'block_instances', array('id'=>$id) );

// get course
$course = $DB->get_record( 'course', array('id'=>$courseid) );

// get course module
if($cmid){
    $modinfo = get_fast_modinfo($course);
    $cm = $modinfo->get_cm($cmid);
}

// set url params
$urloptions= array('id'=>$id, 'courseid'=>$courseid);
if($cmid){
    $urloptions['cmid'] = $cmid;
}

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
$mform = new create_ticket_form( null, array('id'=>$id, 'courseid'=>$courseid, 'description'=>$coursetext, 'cmid'=>$cmid ));
if ($mform->is_cancelled()) {

    // just back to form page
    redirect( $url );

} else if ($data = $mform->get_data() ) {

    // get the data
    $subject = $data->subject;
    $description = $data->description;


    // ensure user is in OTRS
    otrslib::userupdate($USER);

    // get mimetype from format
    $mimetype = otrslib::getMimetype( $description['format'] );

    // get attachments
    $fs = get_file_storage();
    $context = context_user::instance($USER->id);
    $attachments = $fs->get_area_files($context->id, 'user', 'draft', $data->attachments, 'id DESC', false);
   
    // Setup dynamic fields.
    $dfields = array();
    $cdfield = get_config('block_otrs','course_dfield');
    if($cdfield && $cdfield != '' && $courseid > 1) {
        $dfields[$cdfield] = $course->shortname;
    }
    $mdfield = get_config('block_otrs','module_dfield');
    if($mdfield && $mdfield != '' & !empty($cm) ) {
        $dfields[$mdfield] = $cm->name;
    }
   
    // create a ticket in OTRS
    $otrssoap = new otrsgenericinterface();
    $Ticket = $otrssoap->TicketCreate( $USER->username, $subject, $description['text'], null, 'customer', 'webrequest', $mimetype, 3, $dfields, $attachments );

    // back to course

    $PAGE->set_url('/blocks/otrs/create_ticket.php', $urloptions);
    echo $OUTPUT->header();
    redirect( $url, get_string( 'ticketcreated','block_otrs' ), 3 );
} else {
    // navigation
    //$PAGE->set_course($course);
    $PAGE->navbar->add(get_string('pluginname', 'block_otrs'));
    $PAGE->set_url('/blocks/otrs/create_ticket.php', $urloptions);
    $PAGE->set_heading( get_string('pluginname', 'block_otrs' ));
    $PAGE->set_pagetype('otrs');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('createticket', 'block_otrs'));
    $mform->display();
    echo $OUTPUT->footer();
}
