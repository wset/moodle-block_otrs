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
    global $USER;

    // Default Queues section.
    $settings->add( new admin_setting_heading( 'queuesconfig', get_string('defaultqueues','block_otrs'), get_string('defaultqueuesdesc','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs/queue', get_string('queue','block_otrs'), get_string('queueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs/create_queues', get_string('createqueue','block_otrs'), get_string('createqueueconfig','block_otrs'), "{{'queue': 'moodle'}}"));

    $settings->add( new admin_setting_configtext( 'block_otrs/quiz_queue', get_string('quizqueue','block_otrs'), get_string('quizqueueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs/qgrade_dfield', get_string('qgradedfield','block_otrs'), get_string('qgradedfielddesc','block_otrs'), ''));

    $settings->add( new admin_setting_configtext( 'block_otrs/qmark_dfield', get_string('qmarkdfield','block_otrs'), get_string('qmarkdfielddesc','block_otrs'), ''));

    $settings->add( new admin_setting_configtext( 'block_otrs/completion_queue', get_string('completionqueue','block_otrs'), get_string('completionqueueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs/user_update_queue', get_string('userupdatequeue','block_otrs'), get_string('userupdatequeueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs/course_dfield', get_string('coursedfield','block_otrs'), get_string('coursedfielddesc','block_otrs'), ''));


    // SOAP RPC Connector settings.
    $settings->add( new admin_setting_heading( 'rpcconfig', get_string('rpcsettings','block_otrs'), get_string('rpcsettingsdesc','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs/rpcurl', get_string('url','block_otrs'), get_string('urlconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs/rpcuser', get_string('otrsuser','block_otrs'), get_string('otrsuserconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configpasswordunmask( 'block_otrs/rpcpassword', get_string('otrspassword','block_otrs'), get_string('otrspasswordconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs/userfields', get_string('userfields','block_otrs'), get_string('userfieldsconfig','block_otrs'), '') );

    $defaultuserkeys = array_keys( (array) $USER );
    $userkeys = array_combine($defaultuserkeys, $defaultuserkeys);
    $settings->add( new admin_setting_configmultiselect( 'block_otrs/usermatchfields', get_string('usermatchfields', 'block_otrs'), get_string('usermatchfieldsconfig', 'block_otrs'), $defaultuserkeys, $userkeys));


    // SOAP Generic Interface settings.
    $settings->add( new admin_setting_heading( 'giconfig', get_string('genericinterfacesettings','block_otrs'), get_string('genericinterfacesettingsdesc','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs/giurl', get_string('giurl','block_otrs'), get_string('giurlconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs/gins', get_string('gins','block_otrs'), get_string('ginsdesc','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs/giuser', get_string('giuser','block_otrs'), get_string('giuserconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configpasswordunmask( 'block_otrs/gipassword', get_string('gipassword','block_otrs'), get_string('gipasswordconfig','block_otrs'), '') );
}
