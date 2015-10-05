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
$courseid = required_param('courseid', PARAM_INT);

// get block
$block = $DB->get_record( 'block_instances', array('id'=>$id), '*', MUST_EXIST);

// get course
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

// security stuff
require_login( $course );
$context = context_block::instance( $id );
require_capability( 'block/otrs:viewothers', $context );

// navigation
$PAGE->set_course($course);
$PAGE->navbar->add(get_string('pluginname', 'block_otrs'));
$PAGE->navbar->add(get_string('listusertickets', 'block_otrs'));
$PAGE->set_url('/blocks/otrs/list_users.php', array('id'=>$courseid));
$PAGE->set_heading( get_string('pluginname', 'block_otrs' ));
$PAGE->set_pagetype('otrs');

// Get renderer
$renderer = $PAGE->get_renderer('block_otrs');

// course url
$url = new moodle_url("/course/view.php", array('id'=>$courseid));

// view ticket url
$ticketurl = new moodle_url("/blocks/otrs/view_ticket.php", array('id'=>$id, 'courseid'=>$courseid));

// this (base) url
$listurl = new moodle_url("/blocks/otrs/list_users.php", array('id'=>$id, 'courseid'=>$courseid));

// get users in current course
$coursecontext = context_course::instance( $courseid );
$users = get_users_by_capability( $coursecontext, 'moodle/course:view' );

// get users who have viewable tickets
$ticketusers = otrslib::getTicketUsers( $users ); 

echo $OUTPUT->header();
echo $OUTPUT->heading( get_string('listusertickets','block_otrs' ) );

echo $renderer->listUserTickets( $ticketusers, $id, $courseid );

echo $OUTPUT->footer();
