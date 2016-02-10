<?php

/** This file contains Tuffy's suite of debugging tools.
 * It is loaded by tuffy/Init.php fairly early in the setup process.
 *
 * @author Matthew Frazier <mlfrazie@ncsu.edu>
 */


// Infrastructure for managing the debug log.

/**
 * This represents a single message in the debug log. It tracks an assortment
 * of useful information.
 */
class Tuffy_Debug_Message {
    private $title;
    private $data;
    private $stack;
    private $time;
    private $completeTime = NULL;
    private $flags;

    /**
     * Initializes the debug message.
     *
     * @param string $title A label for the debug message describing its data.
     * @param string $data The data to display.
     * @param Tuffy_Debug_StackFrame[] $stack The backtrace from when the
     * message was generated. (This should be treated with
     * Tuffy_Debug_StackFrame::rewriteBacktrace()).
     * @param int $flags Flags describing the message. Tuffy_Debug::PROBLEM
     * is the only one used currently.
     * @param float $time The time relative to the request start at which the
     * message was generated.
     */
    function __construct ($title, $data, $stack, $flags, $time) {
        $this->title = $title;
        $this->data = $data;
        $this->stack = $stack;
        $this->flags = $flags;
        $this->time = $time;
    }

    public function getTitle () {
        return $this->title;
    }

    public function getData () {
        return $this->data;
    }

    public function getStack () {
        return $this->stack;
    }

    public function getTime () {
        return $this->time;
    }

    /**
     * Formats the time(s) associated with this message as a string.
     */
    public function getTimeString () {
        return $this->completeTime
             ? sprintf("start: %.4f sec, end: %.4f sec, time: %.4f sec",
                       $this->time, $this->completeTime,
                       $this->completeTime - $this->time)
             : sprintf("at: %.4f sec", $this->time);
    }

    /**
     * Returns TRUE if the PROBLEM flag has been set. Messages that represent
     * problems should be highlighted.
     */
    public function isProblem () {
        return $this->flags & Tuffy_Debug::PROBLEM;
    }

    /**
     * Adds a complete time to this message. Useful for things like queries
     * and HTTP requests that take some amount of time.
     *
     * @param float|null $time If this is NULL, use the current time.
     * Otherwise, this is the time relative to the request's start time.
     */
    public function complete ($time = NULL) {
        $this->completeTime = $time === NULL
                            ? (microtime(TRUE) - REQUEST_START_TIME)
                            : $time;
    }
}


/**
 * Represents a stack frame as part of a backtrace. This uses Python-format
 * backtraces, as opposed to the PHP backtraces which mismatch functions with
 * files and line numbers (at least in my opinion).
 */
class Tuffy_Debug_StackFrame {
    public $class;
    public $function;
    public $file;
    public $line;
    public $type;

    /**
     * Takes an exception and generates an array of Tuffy_Debug_StackFrames.
     *
     * @param Exception $exc The traceback to read the exception from.
     */
    public static function rewriteBacktraceFromException ($exc) {
        return self::rewriteBacktrace($exc->getTrace(), $exc->getFile(),
                                      $exc->getLine());
    }

    /**
     * Takes a backtrace as returned by debug_backtrace or
     * Exception->getTrace() and rewrites it into an array of
     * Tuffy_Debug_StackFrames.
     *
     * @param array $tb The backtrace to rewrite.
     * @param string|null $fFile The file that the backtrace was generated at.
     * @param int|null $fLine The line that the backtrace was generated at.
     */
    public static function rewriteBacktrace ($tb, $fFile = NULL, $fLine = NULL) {
        if ($fFile || $fLine) {
            array_unshift($tb, array('file' => $fFile, 'line' => $fLine));
        }
        $level = 1;
        $calls = array();
        $count = count($tb);

        while ($level < $count) {
            $name = $tb[$level];
            $loc = $tb[$level - 1];
            $calls[] = new self(
                maybe($name, 'class'), maybe($name, 'type'),
                maybe($name, 'function'), maybe($loc, 'file'),
                maybe($loc, 'line')
            );
            $level++;
        }
        $loc = $tb[$level - 1];
        $calls[] = new self(NULL, NULL, '<main>', maybe($loc, 'file'),
                            maybe($loc, 'line'));
        return $calls;
    }

    /**
     * Initializes the new stack frame.
     *
     * @param string|null $class The name of the class associated with this
     * call.
     * @param string|null $type The type of call - `->`, `::`, or NULL.
     * @param string|null $function The name of the function associated with
     * this call.
     * @param string|null $file The name of the file associated with this
     * call.
     * @param int|null $line The line number associated with this call.
     */
    public function __construct ($class, $type, $function, $file, $line) {
        $this->class = $class;
        $this->type = $type;
        $this->function = $function;
        $this->file = strpos($file, APP_PATH) === 0
                    ? substr($file, strlen(APP_PATH)) : $file;
        $this->line = $line;
    }

    /**
     * The qualified name of the function that generated this call.
     */
    public function getName () {
        return isset($this->type)
             ? ($this->class . $this->type . $this->function)
             : $this->function;
    }

    /**
     * The file and line where this call was generated, together.
     */
    public function getLocation () {
        return $this->file . '(' . $this->line . ')';
    }

    public function __toString () {
        return $this->getName() . "() at " . $this->getLocation();
    }
}


/**
 * Adds a new message to the debug log, if APP_DEBUG is enabled.
 *
 * @param string $title A label for the debug message describing its data.
 * @param string $data The data to display.
 * @param int $flags Flags describing the message. Tuffy_Debug::PROBLEM
 * is the only one used currently.
 * @param int $skipExtra The number of stack frames to skip when assigning
 * this message a traceback.
 */
function tuffyDebug ($title, $data, $flags = 0, $skipExtra = 0) {
    if (APP_DEBUG) {
        $stack = array_slice(Tuffy_Debug::getBacktrace(), 1 + $skipExtra);
        if (!is_string($data)) {
            if (is_object($data) && method_exists($data, 'toDebug')) {
                $data = $data->toDebug();
            } else {
                $data = var_export($data, TRUE);
            }
        }
        return Tuffy_Debug::addMessage(new Tuffy_Debug_Message(
            $title, $data, $stack, $flags, microtime(TRUE) - REQUEST_START_TIME
        ));
    }
}


/**
 * The exception handler will silently ignore this exception, and exit the
 * script immediately. Use this instead of exit() to cover the eventuality
 * that your caller *doesn't* actually want to exit.
 */
class Tuffy_Exit extends Exception {
    // this class intentionally left blank
}


class Tuffy_Debug {
    const PROBLEM = 1;

    private static $log = array();

    public static function getBacktrace () {
        return Tuffy_Debug_StackFrame::rewriteBacktrace(debug_backtrace());
    }

    public static function getLog ($remove = TRUE) {
        $log = self::$log;
        if ($remove) {
            self::$log = array();
        }
        return $log;
    }

    public static function saveLogInSession () {
        $key = APP_NAME . ':debugLeftovers';
        $_SESSION[$key] = self::getLog(FALSE);
    }

    public static function restoreLogFromSession () {
        $key = APP_NAME . ':debugLeftovers';
        if (array_key_exists($key, $_SESSION)) {
            self::$log = array_merge($_SESSION[$key], self::$log);
            unset($_SESSION[$key]);
        }
    }

    public static function addMessage ($msg) {
        self::$log[] = $msg;
        return count(self::$log);
    }

    public static function completeEvent ($eventIndex) {
        self::$log[$eventIndex - 1]->complete();
    }

    public static function renderLog ($log) {
        $lines = array();
        foreach ($log as $entry) {
            $lines[] = "============================";
            $lines[] = $entry->getTitle() . " [" . $entry->getTimeString() . "]";
            foreach ($entry->getStack() as $frame) {
                $lines[] = "- $frame";
            }
            $lines[] = "";
            $lines[] = wordwrap($entry->getData(), 100);
            $lines[] = "";
        }
        return implode("\r\n", $lines);
    }

    public static function handleError ($errno, $errstr, $errfile, $errline) {
        if ($errno & (E_NOTICE | E_USER_NOTICE | E_STRICT)) {
            tuffyDebug("Notice", $errstr, 0, 1);
        } else {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }

    public static function handleException ($exception) {
        if ($exception instanceof Tuffy_Exit) {
            exit;
        }
        echo "<pre><strong>" . (
            $exception === NULL ? "Script has been stopped." : "Error occurred!"
        ) . "</strong>\r\n\r\n";
        echo $exception;
        echo "\r\n\r\n";
        echo self::renderLog(self::getLog());
        echo "</pre>";
    }

    public static function handleShutdown () {
        $error = error_get_last();
        if ($error === NULL) {
            return;
        }
        switch($error['type']) {
            case E_ERROR:
                $errorType = 'Fatal error: ';
                break;
            case E_COMPILE_ERROR:
                $errorType = 'Compile error: ';
                break;
            case E_PARSE:
                $errorType = 'Parse error: ';
                break;
            default:
                $errorType = 'Unknown error: ';
        }
        self::handleException(new ErrorException(
            $errorType . $error['message'], 0, $error['type'],
            $error['file'], $error['line']
        ));
    }
}

set_error_handler('Tuffy_Debug::handleError');
set_exception_handler('Tuffy_Debug::handleException');
register_shutdown_function('Tuffy_Debug::handleShutdown');

