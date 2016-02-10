<?php

// Tuffy settings for FEDD Scoreboard (admin).

define('APP_NAME',                  'FEDD');
define('APP_TIMEZONE',              'America/New_York');
define('APP_LIBRARY_PATH',          'lib');

if (strpos(REQUEST_HOST, 'webtest.') === 0) {
    define('APP_DEBUG',             TRUE);
}

// Settings for Tuffy_Template
define('APP_CONFIGURE_TEMPLATE',    TRUE);
define('APP_TEMPLATE_PATH',         'templates');
define('APP_TEMPLATE_HELPERS',      'FEDD_Helpers::registerTemplateHelpers');

// Settings for Tuffy_Database
define('APP_CONFIGURE_DATABASE',    TRUE);
require(APP_PATH . '_data/database-connections/db-WengrFirstye-w.php');

