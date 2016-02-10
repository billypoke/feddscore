<?php

class FEDD_Helpers {
    private static $FOURTH_THURSDAYS = array(
        // if the first day of November is a <blank>, Thanksgiving is on...
        'Sun' => 26,    'Mon' => 25,    'Tue' => 24,    'Wed' => 23,
        'Thu' => 22,    'Fri' => 28,    'Sat' => 29
    );

    public static function computeDate ($year = NULL) {
        $year = (int)($year === NULL ? date('Y') : $year);
        $nov1Weekday = date('D', mktime(0, 0, 0, 11, 1, $year));
        $day = self::$FOURTH_THURSDAYS[$nov1Weekday] - 2;
        return new DateTime("$year/11/$day");
    }

    const WURFL_URL = 'http://webapps.ncsu.edu/wurfl/?method=getDeviceByUserAgent&userAgent=%s&capabilities=mobile_browser';

    public static function isMobile ($userAgent = NULL) {
        if (!$userAgent) {
            if (APP_DEBUG && array_key_exists('forceMobile', $_GET)) {
                return TRUE;
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        $url = sprintf(self::WURFL_URL, urlencode($userAgent));
		$xmlData = file_get_contents($url);
		if ($xmlData !== FALSE) {
			$res = simplexml_load_string($xmlData);
			$mobile = trim($res->getDeviceByUserAgent->capabilities->mobile_browser);
			return (!empty($mobile));
		}
	}

    public static function registerTemplateHelpers () {
        Tuffy_Template::addFunction('feddDate', 'FEDD_Helpers::computeDate');
        Tuffy_Template::addGlobal('mobile', self::isMobile());
        Tuffy_Template::addGlobal('REQUEST_FULL_PATH', $_SERVER['REQUEST_URI']);
    }
}

