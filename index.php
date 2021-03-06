<?php

/**
 * @filesource index.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 * @copyright Matthias Kerstner <matthias@kerstner.at>
 *
 * This file is part of phpMailingList.
 * @link https://www.kerstner.at/phpmailinglist/
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
define('PHPMAILINGLIST_DIRSEPERATOR', '/');
define('PHPMAILINGLIST_BASEPATH', mb_ereg_replace('/\\/g', PHPMAILINGLIST_DIRSEPERATOR, dirname(realpath(__FILE__)))
        . PHPMAILINGLIST_DIRSEPERATOR);

require_once PHPMAILINGLIST_BASEPATH . 'PhpMailingList.php';

session_start();

// get required paramaters so that we could manually call PhpMailingList
$action = isset($_POST['action']) ? $_POST['action'] :
        (isset($_GET['action']) ? $_GET['action'] : null); //post before get
$list = isset($_GET['list']) ? urldecode($_GET['list']) : null;

try {
    Config::init(PHPMAILINGLIST_BASEPATH . 'config/config.ini');
    PhpMailingList::processRequest($action, $list);
} catch (Exception $e) {
    //try to write to log file
    file_put_contents(PHPMAILINGLIST_BASEPATH .
            Config::get('log_file'), $e->getMessage() . "\n", FILE_APPEND);

    //there's nothing more we can do, good bye...
    die('Sorry, your request could not be processed. Please check your log file.');
}
