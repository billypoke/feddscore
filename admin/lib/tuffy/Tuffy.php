<?php

/** This class contains generic functionality useful to just about all Web
 * pages.
 *
 * @author Matthew Frazier <mlfrazie@ncsu.edu>
 */
class Tuffy {
    // URLs and HTTP responses.

    /**
     * Expands a URL. Absolute URLs, and URLs with a leading slash,
     * are returned as is, but relative URLs are treated as relative to
     * APP_PATH instead of the current directory.
     *
     * @param string $target The URL to redirect to.
     * @param array $params GET parameters to include in the built URL's
     * query string.
     * @param boolean $forceHTTPS If this is TRUE, the generated URL will
     * always begin with https://. Otherwise, it will be the same as the
     * current request's scheme.
     */
    public static function url ($target, $params = NULL, $forceHTTPS = FALSE) {
        $scheme = $forceHTTPS ? 'https://' : REQUEST_SCHEME;
        if ($target === 'index') {
            $base = $scheme . REQUEST_HOST . REQUEST_PREFIX;
        } else if (parse_url($target, PHP_URL_SCHEME) !== NULL) {
            $base = $target;
        } else if ($target[0] === '/') {
            $base = $scheme . REQUEST_HOST . $target;
        } else {
            $base = $scheme . REQUEST_HOST . REQUEST_PREFIX . $target;
        }
        return $params === NULL ? $base : $base . '?' . self::buildQuery($params);
    }

    /**
     * Redirects to another page and exits. This includes a brief HTML message
     * explaining to where the redirect goes. It also saves the debug log
     * for this request in the session, if both the debug log and sessions
     * are enabled.
     *
     * @param string $target The URL to redirect to. This is passed through
     * Tuffy::expand_url.
     * @param string $code The HTTP status line (e.g. "303 See Other").
     */
    public static function redirect ($target, $code = '303 See Other') {
        $dest = self::url($target);
        if (APP_DEBUG && APP_USE_SESSIONS) {
            tuffyDebug("Redirecting", $dest);
            Tuffy_Debug::saveLogInSession();
        }
        header('HTTP/1.1 ' . $code);
        header('Location: ' . $dest);
        echo "<!doctype html>\n";
        echo "<p>Redirecting you to <a href=\"$dest\">$dest</a>.</p>";
        throw new Tuffy_Exit();
    }

    /**
     * If this request is not over HTTPS, redirects to the HTTPS equivalent.
     * This uses a 307 response, so the request method and data should be
     * preserved...but if the user already sent a password over an insecure
     * connection, you're already borked.
     */
    public static function requireSSL () {
        if (!REQUEST_SECURE) {
            self::redirect('https://' . APP_HOST . $_SERVER['REQUEST_URI'],
                           '307 Temporary Redirect');
        }
    }

    // Sessions.

    /**
     * Saves a flash message in the user's sessions, to be retrieved
     * and displayed at a later time (possibly within the same request).
     *
     * @param string $type The type of message. (Possiblities include "info",
     * "error", "success", and "warning".)
     * @param string $message The actual message to display. This will
     * probably not be HTML-escaped.
     * @see Tuffy::getFlashes
     */
    public static function flash ($type, $message) {
        if (!APP_USE_SESSIONS) {
            trigger_error("sessions are disabled", E_USER_NOTICE);
            return;
        }
        $_SESSION[APP_NAME . ':flashes'][] = array(
            'type' => $type, 'message' => $message
        );
    }

    /**
     * Retrieves all of the user's flash message from the session. They
     * are returned as an array of arrays with two keys each - `type` and
     * `message`.
     *
     * @param boolean $remove If this is TRUE (the default), the flashes will
     * also be removed from the session.
     */
    public static function getFlashes ($remove = TRUE) {
        if (!APP_USE_SESSIONS) {
            trigger_error("sessions are disabled", E_USER_NOTICE);
            return;
        }
        $key = APP_NAME . ':flashes';
        if (array_key_exists($key, $_SESSION)) {
            if ($remove) {
                $flashes = $_SESSION[$key];
                unset($_SESSION[$key]);
                return $flashes;
            } else {
                return $_SESSION[$key];
            }
        } else {
            return array();
        }
    }

    // Mail sending.

    /**
     * Sends email using the PHP standard mail() function. (This assumes your
     * system administrator has configured it properly.)
     *
     * @param string $from The email address to send the message from.
     * @param string $to The email address(es) to send the message to.
     * @param string $replyTo The email address to which replies should be
     * delivered.
     * @param string $subject The subject of the message.
     * @param string $body The content of the message.
     */
    public static function mail ($from, $to, $replyTo, $subject, $body) {
        $headers = "From: $from\r\nReply-To: $replyTo";
        $sendmailParams = "-f" . $from;
        tuffyDebug("Mail", "To:       $to\r\n" .
                           "From:     $from\r\n" .
                           "Reply-To: $replyTo\r\n" .
                           "Subject:  $subject\r\n\r\n" . $body);
        mail($to, $subject, $body, $headers, $sendmailParams);
    }

    // Helper functions.

    /**
     * Builds a query string out of an array of data. It uses multiple
     * key/value pairs to represent arrays.
     *
     * @param array $data The data to put in the query string.
     */
    public static function buildQuery ($data) {
        $pairs = array();
        foreach ($data as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $pairs[] = rawurlencode($name) . '=' . rawurlencode($item);
                }
            } else {
                $pairs[] = rawurlencode($name) . '=' . rawurlencode($value);
            }
        }
        return implode('&', $pairs);
    }
}


