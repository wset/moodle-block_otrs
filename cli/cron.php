<?php

define('CLI_SCRIPT', true);

require(dirname(__FILE__).'/../../../config.php');
require($CFG->dirroot . '/blocks/moodleblock.class.php' );
require($CFG->dirroot . '/blocks/otrs/block_otrs.php' );

$otrs = new block_otrs();

$otrs->clicron();
