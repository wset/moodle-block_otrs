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
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Default Queues section.
    $settings->add( new admin_setting_heading( 'queuesconfig', get_string('defaultqueues','block_otrs'), get_string('defaultqueuesdesc','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs_queue', get_string('queue','block_otrs'), get_string('queueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_quiz_queue', get_string('quizqueue','block_otrs'), get_string('quizqueueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_completion_queue', get_string('completionqueue','block_otrs'), get_string('completionqueueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_user_update_queue', get_string('userupdatequeue','block_otrs'), get_string('userupdatequeueconfig','block_otrs'), 'moodle') );

    // SOAP RPC Connector settings.
    $settings->add( new admin_setting_heading( 'rpcconfig', get_string('rpcsettings','block_otrs'), get_string('rpcsettingsdesc','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs_url', get_string('url','block_otrs'), get_string('urlconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs_otrsuser', get_string('otrsuser','block_otrs'), get_string('otrsuserconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configpasswordunmask( 'block_otrs_otrspassword', get_string('otrspassword','block_otrs'), get_string('otrspasswordconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs_userfields', get_string('userfields','block_otrs'), get_string('userfieldsconfig','block_otrs'), '') );


    // SOAP Generic Interface settings.
    $settings->add( new admin_setting_heading( 'giconfig', get_string('genericinterfacesettings','block_otrs'), get_string('genericinterfacesettingsdesc','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs_giurl', get_string('giurl','block_otrs'), get_string('giurlconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs_gins', get_string('gins','block_otrs'), get_string('ginsdesc','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs_giuser', get_string('giuser','block_otrs'), get_string('giuserconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configpasswordunmask( 'block_otrs_gipassword', get_string('gipassword','block_otrs'), get_string('gipasswordconfig','block_otrs'), '') );
}
