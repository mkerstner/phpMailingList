<?php

/**
 * @filesource Log.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 * @copyright Matthias Kerstner <matthias@kerstner.at>
 *
 * This file is part of phpMailingList.
 * @link http://www.kerstner.at/phpmailinglist
 *
 * This script is based upon the 'Email list script' by phptutorial.info,
 * @link http://www.phptutorial.info/scripts/mailinglist/index.php
 * and uses the SwiftMailer library by Chris Corbyn,
 * @link http://www.swiftmailer.org/
 * as well as the Securimage CAPTCHA library by phpcaptcha.org,
 * @link http://www.phpcaptcha.org/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
abstract class Log {

    const LOG_ERROR = 1;
    const LOG_WARNING = 2;
    const LOG_INFO = 3;
    const LOG_DEBUG = 4;

    public static function log($msg, $logLevel = LOG_DEBUG) {
        
    }

}

?>
