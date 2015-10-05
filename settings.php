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
    $settings->add( new admin_setting_heading( 'heading', get_string('pluginname','block_otrs'), get_string('settingconfig','block_otrs')) );

    $settings->add( new admin_setting_configtext( 'block_otrs_url', get_string('url','block_otrs'), get_string('urlconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs_otrsuser', get_string('otrsuser','block_otrs'), get_string('otrsuserconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configpasswordunmask( 'block_otrs_otrspassword', get_string('otrspassword','block_otrs'), get_string('otrspasswordconfig','block_otrs'), '') );

    $settings->add( new admin_setting_configtext( 'block_otrs_queue', get_string('queue','block_otrs'), get_string('queueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_quiz_queue', get_string('quizqueue','block_otrs'), get_string('quizqueueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_completion_queue', get_string('completionqueue','block_otrs'), get_string('completionqueueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_user_update_queue', get_string('userupdatequeue','block_otrs'), get_string('userupdatequeueconfig','block_otrs'), 'moodle') );

    $settings->add( new admin_setting_configtext( 'block_otrs_userfields', get_string('userfields','block_otrs'), get_string('userfieldsconfig','block_otrs'), '') );
}
