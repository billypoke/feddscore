<?php

/** Tuffy is halfway between a library and a full-fledged Web framework.
 * It is designed to function on PHP 5.2 with open_basedir, no advanced
 * debugging extensions, and assorted other restrictions.
 * Its main attraction is the advanced suite of debugging and logging tools.
 *
 * This file contains the initial setup code that sets up some basic
 * environmental information, loads the app's settings file, and then
 * loads the main Tuffy code.
 *
 * @author Matthew Frazier <mlfrazie@ncsu.edu>
 */


// 1. Compute some environmental information.

/**
 * The time at which the request started, down to the microsecond.
 */
define('REQUEST_START_TIME',    microtime(TRUE));
/**
 * TRUE if this script is being run from the console, FALSE if it is live
 * on the Web.
 */
define('REQUEST_CONSOLE',       PHP_SAPI === 'cli');
if (!REQUEST_CONSOLE) {
    /**
     * TRUE if the request was made over HTTPS, FALSE if not.
     */
    define('REQUEST_SECURE',    !empty($_SERVER['HTTPS']));
    /**
     * The URL scheme associated with the current request.
     */
    define('REQUEST_SCHEME',    REQUEST_SECURE ? 'https://' : 'http://');
    /**
     * The hostname attached to the current request.
     */
    define('REQUEST_HOST',      $_SERVER['HTTP_HOST']);
    /**
     * The request method (i.e. GET, POST, BREW) for the current request.
     */
    define('REQUEST_METHOD',    $_SERVER['REQUEST_METHOD']);
}


// 2. Compute the app's location.

if (!defined('APP_SCRIPT_DEPTH')) {
    define('APP_SCRIPT_DEPTH', 1);
}

$tuffyScriptDepth = APP_SCRIPT_DEPTH;
$tuffyAppPath = realpath($_SERVER['SCRIPT_FILENAME']);
$tuffyAppPrefix = $_SERVER['SCRIPT_NAME'];

while ($tuffyScriptDepth-- > 0) {
    $tuffyAppPath = dirname($tuffyAppPath);
    $tuffyAppPrefix = dirname($tuffyAppPrefix);
}

/**
 * The path to the application's root directory.
 */
define('APP_PATH',              rtrim($tuffyAppPath, '/\\') . '/');
if (!REQUEST_CONSOLE) {
    /**
     * The prefix for this request (i.e., the directory all the scripts
     * are in from an HTTP perspective.
     */
    define('REQUEST_PREFIX',    rtrim($tuffyAppPrefix, '/') . '/');
}

unset($tuffyScriptDepth, $tuffyAppPath, $tuffyAppPrefix);

/**
 * The path to Tuffy's module files.
 */
define('TUFFY_PATH',        rtrim(dirname(__FILE__), '/\\') . '/');


// 3. Load the debugging tools.

require_once(TUFFY_PATH . 'Debug.php');
require_once(TUFFY_PATH . 'Loader.php');


// 4. Set up the autoloader.

$tuffyCoreLoader = new Tuffy_Loader_ForTuffy('Tuffy', TUFFY_PATH, array(
    'Tuffy' => TUFFY_PATH . 'Tuffy.php'
));
$tuffyCoreLoader->register();


// 5. Load and normalize settings.

require_once(APP_PATH . (defined('APP_SETTINGS_FILE') ?
                         APP_SETTINGS_FILE : '_data/settings.php'));

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', FALSE);
}

if (!defined('APP_NAME')) {
    die("You must define an APP_NAME in your settings file.");
}

if (defined('APP_TIMEZONE')) {
    date_default_timezone_set(APP_TIMEZONE);
} else {
    die("You must define a timezone as APP_TIMEZONE in your settings file.");
}

if (!defined('APP_USE_SESSIONS')) {
    define('APP_USE_SESSIONS', TRUE);
}

if (defined('APP_LIBRARY_PATH')) {
    $tuffyAppLoader = new Tuffy_Loader(APP_NAME, APP_PATH . APP_LIBRARY_PATH);
    $tuffyAppLoader->register();
}


// 6. Do some miscellaneous input processing.

if (get_magic_quotes_gpc()) {
    function _recursiveStripSlashes ($value) {
        return is_array($value)
             ? array_map('_recursiveStripSlashes', $value)
             : stripslashes($value);
    }
    $_GET = array_map('_recursiveStripSlashes', $_GET);
    $_POST = array_map('_recursiveStripSlashes', $_POST);
    $_COOKIE = array_map('_recursiveStripSlashes', $_COOKIE);
}


if (APP_USE_SESSIONS) {
    session_start();
    if (APP_DEBUG) {
        Tuffy_Debug::restoreLogFromSession();
        tuffyDebug("Session " . session_id(), $_SESSION);
    }
}


// 7. Here are a couple of completely non-namespaced utility functions
// that are just too useful not to have.

/**
 * If the $key exists in $array, returns the value stored therein. Otherwise,
 * returns $default.
 *
 * @param array $array The array to check.
 * @param mixed $key The key to search for.
 * @param mixed $default The value to return if the key does not exist.
 * (It defaults to NULL.)
 */
function maybe ($array, $key, $default = NULL) {
    return array_key_exists($key, $array) ? $array[$key] : $default;
}


/**
 * Escapes HTML. This will always escape quotes as well, which does nothing
 * when in body text and helps quite a bit in attributes.
 *
 * @param string $data The HTML to escape.
 */
function esc ($data) {
    return htmlspecialchars($data, ENT_QUOTES);
}


