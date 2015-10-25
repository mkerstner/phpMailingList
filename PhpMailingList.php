<?php

/**
 * @filesource PhpMailingList.php
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
require_once PHPMAILINGLIST_BASEPATH . 'lib/config/Config.php';
require_once PHPMAILINGLIST_BASEPATH . 'lib/phpcaptcha/securimage.php';
require_once PHPMAILINGLIST_BASEPATH . 'lib/email/Email.php';
require_once PHPMAILINGLIST_BASEPATH . 'UserException.php';

abstract class PhpMailingList {

    /**
     * 
     * @param string $list
     * @param string $msgId
     * @return string
     * @throws Exception
     */
    private static function loadMessage($list, $msgId) {

        $startRegexp = "____START____" . $msgId . "____";
        $endRegexp = "____END____" . $msgId . "____";
        $filepath = self::getMessagesFilePath($list);
        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new Exception('Failed to open members file.');
        }

        $message = null;

        foreach ($lines as $line) {
            if ($line == $startRegexp) {
                $message = ""; // init message for next iteration
                continue;
            }
            if ($line == $endRegexp) {
                return $message; // we are done
            }
            if ($message !== null) {
                $message .= $line; // append line of current message
            }
        }

        if ($message === null) {
            throw new Exception('So such message exists');
        }

        return $message;
    }

    /**
     * Checks if list-directory exists and initializes it on demand, otherwise
     * throws an Exception.
     * @param string $list
     */
    private static function checkForList($list) {

        if (empty($list)) {
            throw new Exception(Config::__('NoListSpecified'));
        } else if (!file_exists(self::getListFolder($list))) {
            throw new Exception('List ' . $list . ' does not exist.');
        }

        $membersHandle = fopen(self::getMembersFilePath($list), 'ab+');
        $authHandle = fopen(self::getSubscribeAuthorizationFilePath($list), 'ab+');
        $unauthHandle = fopen(self::getUnsubscribeAuthorizationFilePath($list), 'ab+');
        $messagesHandle = fopen(self::getMessagesFilePath($list), 'ab+');
        $htaccessHandle = fopen(self::getHtaccessFilePath(), 'ab+');

        if (!$membersHandle || !$authHandle || !$unauthHandle ||
                !$htaccessHandle) {
            throw new Exception('Failed to initialize list ' . $list
            . '. Hint for permissions.');
        }


        // only write once in .htaccess
        if (!filesize(self::getHtaccessFilePath())) {
            $htaccessContent = 'deny from all';

            if (!fwrite($htaccessHandle, $htaccessContent, mb_strlen($htaccessContent))) {
                throw new Exception('Failed to create htaccess file.');
            }
        }

        fclose($membersHandle);
        fclose($authHandle);
        fclose($unauthHandle);
        fclose($messagesHandle);
        fclose($htaccessHandle);
    }

    /**
     * Checks if $email is member of $list.
     * @param string $list
     * @param string $email
     * @return bool
     */
    private static function isMember($list, $email) {
        $members = self::getMembers($list);

        foreach ($members as $member) {
            if ($email === $member[1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns email corresponding to $hash specified, throws Exception
     * otherwise.
     * @param string $list
     * @param string $hash
     * @return string email if $hash matches a email from member list
     */
    private static function getMemberByAuthHash($list, $hash) {
        $members = self::getMembers($list);

        $memberEmail = null;
        foreach ($members as $member) {
            if ($member[0] === $hash) {
                $memberEmail = $member[1];
                break;
            }
        }

        if ($memberEmail !== null) {
            return $memberEmail;
        }

        throw new Exception('Invalid authentication.');
    }

    /**
     * Adds $email to $list.
     * @param string $list
     * @param string $email
     * @param string $authHash
     */
    private static function addMember($list, $email, $authHash) {
        $handle = fopen(self::getMembersFilePath($list), 'ab');

        if (!$handle) {
            throw new Exception('Failed to open members file.');
        }
        if (fputs($handle, "\n<$authHash> : <$email>") === false) {
            throw new Exception('Failed to subscribe. Could not write ' .
            'to list file.');
        }

        fclose($handle);
    }

    /**
     * Removes $email from $list.
     * @param string $list
     * @param string $email
     */
    private static function removeMember($list, $email) {
        $filename = self::getMembersFilePath($list);
        $handle = fopen($filename, 'ab+');
        if (!$handle) {
            throw new Exception('Failed to open list file.');
        }

        $membersData = fread($handle, filesize($filename) + 1);
        fclose($handle);

        if (!$membersData) {
            throw new Exception('Failed to read members data.');
        }

        $membersData = preg_replace("/\n<[^>]+> : <$email>/", '', $membersData);

        $handle = fopen($filename, 'wb');
        if (!$handle) {
            throw new Exception('Failed to open list file.');
        }
        if (fputs($handle, $membersData) === false)
            throw new Exception('Failed to write to list file.');

        fclose($handle);
    }

    /**
     * 
     * @param type $list
     * @param type $message
     * @param type $msgId
     * @throws Exception
     */
    private static function addMessage($list, $message, $msgId) {
        $handle = fopen(self::getMessagesFilePath($list), 'ab');

        if (!$handle) {
            throw new Exception('Failed to open messages file.');
        }

        $messageContent = "____START____" . $msgId . "____\n"
                . preg_replace('/<br(\s)*\/>/', '<br>', nl2br($message)) .
                "\n____END____" . $msgId . "____";

        if (fputs($handle, "\n" . $messageContent) === false) {
            throw new Exception('Failed to write message ' .
            'to file.');
        }

        fclose($handle);
    }

    /**
     * Notifies admin (if set) by sending $message.
     * @param string $list
     * @param string $message
     */
    private static function notifyAdmin($list, $message) {
        if (Config::get('admin_notification_email') != '') {
            Email::sendEmail(Config::get('email_reply_to'), Config::get('admin_notification_email'), wordwrap($message . self::getFooter(true)), 'Update for list "' . $list . '"');
        }
    }

    /**
     * Removes tags "<" and ">" from $email and returns it.
     * @param string $email
     * @return string
     */
    private static function removeEmailTags($email) {
        return mb_ereg_replace('\>$', '', mb_ereg_replace('^\<', '', $email));
    }

    /**
     * Returns path to lists folder.
     * @return string
     */
    private static function getListsFolder() {
        return (PHPMAILINGLIST_BASEPATH . Config::get('lists_folder'));
    }

    /**
     * Returns path to specific list folder.
     * @param string $list
     * @return string
     */
    private static function getListFolder($list) {
        return self::getListsFolder() . basename($list)
                . PHPMAILINGLIST_DIRSEPERATOR;
    }

    /**
     * Returns path to subscription authorization file.
     * @param string $list
     * @return string
     */
    private static function getSubscribeAuthorizationFilePath($list) {
        return (self::getListFolder($list) . Config::get('rand_prefix') .
                'authorize');
    }

    /**
     * Returns path to unsubscribe authorization file.
     * @param string $list
     * @return string
     */
    private static function getUnsubscribeAuthorizationFilePath($list) {
        return (self::getListFolder($list) . Config::get('rand_prefix') .
                'unauthorize');
    }

    /**
     * Returns path to members file.
     * @param string $list
     * @return string
     */
    private static function getMembersFilePath($list) {
        return (self::getListFolder($list) . Config::get('rand_prefix') .
                'members');
    }

    /**
     * Returns path to password file.
     * @param string $list
     * @return string
     */
    private static function getPasswordFilePath($list) {
        return (self::getListFolder($list) . Config::get('rand_prefix') .
                'password');
    }

    /**
     * Returns path to messages file..
     * @param string $list
     * @return string
     */
    private static function getMessagesFilePath($list) {
        return (self::getListFolder($list) . Config::get('rand_prefix') .
                'messages');
    }

    /**
     * Returns path to .htaccess file used for *all* list folders.
     * @return string
     */
    private static function getHtaccessFilePath() {
        return (self::getListsFolder() . '.htaccess');
    }

    /**
     * Returns footer to be attached to messages.
     * @param bool? $plainText
     * @return string
     */
    private static function getFooter($plainText = true) {
        $lineBreak = ($plainText) ? "\n" : "<br/>";
        return ($lineBreak . $lineBreak . "-- " . $lineBreak .
                Config::get('message_footer'));
    }

    /**
     * Returns URL based on current request.
     * @param bool? $baseUrlOnly if specified query-part will not be included
     * @return string
     */
    private static function getCurrentUrl($baseUrlOnly = false) {
        $url = 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . '://' .
                $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $urlParts = parse_url($url);

        if (!$urlParts) {
            throw new Exception('Failed to parse url.');
        }

        $baseUrl = ($urlParts['scheme'] . '://' . $urlParts['host'] .
                $urlParts['path']);

        if ($baseUrlOnly) {
            return $baseUrl;
        }

        return ($baseUrl . $urlParts['query']);
    }

    /**
     * Prints form, optionally filling it with the arguments provided below.
     * Automatically checks if list requested has a custom form file and loads
     * it on demand.
     * @param string? $list current list selected
     * @param string? $email email currently entered
     * @param string? $message message currently entered
     * @param string? $attachment
     * @param string? $replyToMsgId message ID to be pre-loaded in form
     * @param string? $userMessage message for the user
     * @param bool? $disabled whether the form is disabled
     * @param string? $formModule which form module (admin|send) to be 
     *                displayed, possible values are 'admin' and 'send'.
     * @see form.php
     * @see config.ini
     */
    private static function printForm($list = null, $email = null, $message = null, $attachment = null, $replyToMsgId = null, $userMessage = null, $disabled = false, $formModule = 'send') {
        $formFilename = Config::get('form_file');
        $adminFilename = Config::get('admin_file');
        $formFile = self::getListFolder($list) . $formFilename;
        $formModule = (!in_array($formModule, array('admin', 'send')) || !$formModule) ? 'send' : $formModule;

        if (!empty($replyToMsgId)) {
            $message = self::loadMessage($list, $replyToMsgId); // overwrite possible $message specified
        }

        if ($formModule === 'send') {
            if (!empty($formFilename) && file_exists($formFile)) {
                require_once $formFile; //customized file exists
                return;
            }
        } else if ($formModule === 'admin') {

            $members = self::getMembers($list);

            if (!empty($adminFilename) && file_exists($adminFilename)) {
                require_once $adminFilename; //customized file exists
                return;
            }
        }

        require_once PHPMAILINGLIST_BASEPATH . 'form.php'; //take default
    }

    /**
     * Prints error form.
     * @param string $message
     * @param string $list
     * @see error.php
     * @see config.ini
     */
    private static function printError($message, $list = null) {
        $errorFilename = Config::get('error_file');
        $errorFile = self::getListFolder($list) . $errorFilename;
        $userMessage = $message;

        if (!empty($errorFilename) && file_exists($errorFile)) {
            require_once $errorFile; //customized file exists
        } else {
            require_once PHPMAILINGLIST_BASEPATH . 'error.php'; //take default
        }
    }

    /**
     * Subscribes $email to $list by first sending an authorization request.
     * Automatically tries to create required files.
     * @param string $email
     * @param string $list
     */
    private static function subscribe($email, $list) {

        try {
            Email::verifyAndSplitEmail($email);
        } catch (Exception $e) {
            throw new UserException(Config::__('FailedToSubscribe') . ': '
            . Config::__($e->getMessage()));
        }

        if (self::isMember($list, $email)) {
            throw new UserException(Config::__('EmailAlreadyInList'));
        }

        $authorizationFile = self::getSubscribeAuthorizationFilePath($list);
        $authorizationHandle = fopen($authorizationFile, 'ab+');
        if (!$authorizationHandle) {
            throw new Exception('Failed to subscribe: Could not open ' .
            'required file(s).');
        }

        $authorizationFileContent = fread($authorizationHandle, filesize($authorizationFile) + 1);
        if ($authorizationFileContent === false) {
            throw new Exception('Failed to subscribe: Could not read ' .
            'authorization file.');
        }
        if (mb_strpos($authorizationFileContent, "<$email>") !== false) {
            throw new UserException(Config::__('EmailIsAlreadyPendingAuthorization'));
        }

        $hash = md5($email . (string) time() . (string) rand(1, 256));

        if (fputs($authorizationHandle, "\n<$hash> : <$email>") === false) {
            throw new Exception('Failed to subscribe. Could not write to ' .
            'authorization file.');
        }
        @fclose($authorizationHandle);

        $authUrl = self::getCurrentUrl(true) . '?list=' . $list .
                '&action=authsubc&hash=' . $hash;
        $from = ($list . ' <' . Config::get('email_reply_to') . '>');
        $subject = 'Authorization request for mailing list "' . $list . '"';
        $message = "Hello,\n\nsomeone (hopefully you) has requested to add your " .
                "email address \n\n" . $email . "\n\nto the mailing list\n\n\"" .
                $list . "\"\n\n" .
                "In order to complete this request please use the following " .
                "link:\n\n" . $authUrl . "\n\n" .
                "If you think that you have received this email in error please " .
                "just ignore it.";

        Email::sendEmail($from, $email, wordwrap($message . self::getFooter(true)), $subject); //send authorization request to email

        self::notifyAdmin($list, $email . ' is pending authorization for list ' .
                $list . '.');
    }

    /**
     * Processes subscription authorization request. On success adds email
     * specified via $hash to $list.
     * @param string $hash
     * @param string $list
     */
    private static function subscribeAuthorize($hash, $list) {

        if (empty($hash)) {
            throw new Exception('Failed to authorize subscription: ' .
            'Invalid hash specified.');
        }

        // check for entry to auth-file and remove if it exists
        $authorizationFile = self::getSubscribeAuthorizationFilePath($list);
        $authorizationHandle = fopen($authorizationFile, 'ab+');
        if (!$authorizationHandle) {
            throw new Exception('Failed to subscribe: Could not open ' .
            'required file(s).');
        }
        $authorizationFileContent = fread($authorizationHandle, filesize($authorizationFile) + 1);
        @fclose($authorizationHandle);

        if ($authorizationFileContent === false) {
            throw new Exception('Failed to subscribe: Could not read ' .
            'authorization file.');
        }
        if (mb_strpos($authorizationFileContent, "<$hash>") === false) {
            throw new UserException('Email is not pending subscription ' .
            'authorization.');
        }

        $regexp = "/\n<($hash)> : <([^>]+)>/";
        $matches = null; // [0] = chunk, [1] = hash, [2] = email

        if (!preg_match($regexp, $authorizationFileContent, $matches)) {
            throw new Exception('Failed to read data from authorization file.');
        }

        $email = $matches[2];
        $authorizationFileContent = preg_replace($regexp, '', $authorizationFileContent); //remove email

        $authorizationHandle = fopen($authorizationFile, 'wb');
        if (!$authorizationHandle) {
            throw new Exception('Failed to open authorization file.');
        }
        if (fputs($authorizationHandle, $authorizationFileContent) === false) {
            throw new Exception('Failed to authorize subscription: ' .
            'Could not write to authorization file.');
        }

        @fclose($authorizationHandle);

        $authHash = md5($email . (string) time() . (string) rand(1, 256));
        self::addMember($list, $email, $authHash); //add member to list

        $url = self::getCurrentUrl(true) . '?list=' . $list;
        $from = $list . ' <' . Config::get('email_reply_to') . '>';
        $subject = 'Joined mailing list "' . $list . '"';
        $message = "Hello,\n\nyou have successfully joined the mailing " .
                "list \"" . $list . "\".\n\n" .
                "In order to send messages to members of the list or to " .
                "administrate your account please go to \n\n" . $url .
                "\n\nEnjoy!";

        self::notifyAdmin($list, $email . ' joined list ' . $list . '.');

        if (Email::sendEmail($from, $email, wordwrap($message .
                                self::getFooter(true)), $subject) !== null) {
            throw new UserException('Failed to send confirmation message');
        }
    }

    /**
     * Unsubscribes $email from $list in $file if it does exist.
     * @param string $email
     * @param string $list
     */
    private static function unsubscribe($email, $list) {
        try {
            Email::verifyAndSplitEmail($email);
        } catch (Exception $e) {
            throw new UserException('Failed to unsubscribe: ' .
            $e->getMessage());
        }

        if (!self::isMember($list, $email)) {
            throw new UserException(Config::__('EmailNotSubscribedToList'));
        }

        self::removeMember($list, $email);

        $url = self::getCurrentUrl(true) . '?list=' . $list;
        $from = $list . ' <' . Config::get('email_reply_to') . '>';
        $subject = 'Unsubscribed from mailing list "' . $list . '"';
        $message = "Hello,\n\nyou have successfully unsubscribed from the " .
                "mailing list \"" . $list . "\".\n\n" .
                "We are sorry to see you go.\n\n" .
                "In case you want to join again you can subscribe using the " .
                "following link:\n\n" . $url . "\n\nHave a nice day!";

        Email::sendEmail($from, $email, wordwrap($message .
                        self::getFooter(true)), $subject);

        self::notifyAdmin($list, $email . ' unsubscribed from list ' . $list .
                '.');
    }

    /**
     * Sends message to all recipients in list specified.
     * @param string $message
     * @param string $list
     * @param array? $attachment
     * @return string? error message in case of error, otherwise NULL.
     */
    private static function sendMessageToList($message, $list, $attachments = null) {

        if (empty($message)) {
            throw new UserException(Config::__('FailedToSendMessageNoMessageSpecified'));
        }

        $members = self::getMembers($list);

        if (count($members) < 1) {
            throw new UserException(Config::__('ListHasNoMembers'));
        }

        $from = $list . ' <' . Config::get('email_reply_to') . '>';
        $subject = Config::__('MessageFromList')
                . ' "' . $list . '"';

        // assemble message
        $messageOriginal = $message;
        $message .= "\n\n**" . Config::__('ReplyToLink') .
                ":\n\n" .
                self::getCurrentUrl(true) . '?list=' . $list;
        $messageId = rand(10000, 999999) . time(); //yeah, a little too deterministic ;)
        $message .= '&msgId=' . $messageId;

        // check for Google Analytics campaign tracking
        if (Config::get('google_analytics_campaign_tracking_source') != '' &&
                Config::get('google_analytics_campaign_tracking_medium') != '' &&
                Config::get('google_analytics_campaign_tracking_campaign') != '') {
            $message .= '&utm_source='
                    . Config::get('google_analytics_campaign_tracking_source')
                    . '&utm_medium='
                    . Config::get('google_analytics_campaign_tracking_medium')
                    . '&utm_campaign='
                    . Config::get('google_analytics_campaign_tracking_campaign');
        }

        // assemble recipients
        $recipients = '';

        foreach ($members as $member) {
            $recipients .= ( ($recipients !== '' ? EMAIL_LIST_SEPERATOR : '') .
                    $member[1]);
        }

        try {
            Email::sendEmail($from, null, ($message . self::getFooter(true)), $subject, $attachments, $recipients);

            //TODO: remove files from tmp again            

            self::addMessage($list, $messageOriginal, $messageId);

            return NULL; //success
        } catch (Exception $e) {
            return 'Failed to send mail to list: ' . $e->getMessage();
        }
    }

    /**
     * Returns members of list specified.
     * @param string $list
     * @return array [[0]=hash, [1]=email]
     */
    private static function getMembers($list) {
        $members = array();
        $filepath = self::getMembersFilePath($list);
        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new Exception('Failed to open members file.');
        }

        foreach ($lines as $line) {
            $member = explode(' : ', $line); //[0]=hash, [1]=email

            if (count($member) < 2) {
                throw new Exception('Invalid syntax in members file.');
            }

            //[0]=hash, [1]=email
            $members[] = array($member[0], self::removeEmailTags($member[1]));
        }

        return $members;
    }

    /**
     * Returns password hash of list set in password file.
     * @param string $list
     * @return string
     */
    private static function getListPassword($list) {
        $filepath = self::getPasswordFilePath($list);
        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new Exception('Failed to open password file.');
        }

        return $lines[0];
    }

    /**
     * Checks authorization setting and returns TRUE if authorization is 
     * required for list.
     * @return boolean
     */
    private static function requireAuthentication() {
        return filter_var(Config::get('require_authentication'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 
     * @param type $locale
     * @return string
     */
    public static function getLocaleUrl($locale) {
        $url = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://'
                . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        if (mb_strpos($url, 'locale=') !== false) {
            $url = preg_replace('/locale=([^&]*|$)/i', 'locale=' . $locale, $url);
        } else {
            $url .= '&locale=' . $locale;
        }

        return $url;
    }

    /**
     * 
     * @return type
     */
    public static function isLoggedIn() {
        return isset($_SESSION['pml_logged_in']);
    }

    /**
     * 
     */
    public static function logout() {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        $redirectURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $redirectURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $redirectURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        // replace previous login call with call to "form"
        $redirectURL = str_replace('showModule=logout', '', $redirectURL);
        header('Location: ' . $redirectURL);
        exit;
    }

    /**
     * 
     * @param type $list
     */
    public static function login($list) {
        try {

            if (self::isLoggedIn()) {
                return; // already logged in
            }

            $password = null;

            if (self::requireAuthentication() && !isset($_SESSION['pml_logged_in'])) {
                $password = isset($_POST['pml_login_password']) ? $_POST['pml_login_password'] : null;
            }

            $redirectURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $redirectURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $redirectURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }

            self::checkForList($list); //auto-initialize list

            if (md5($password) === trim(self::getListPassword($list))) {
                $_SESSION['pml_logged_in'] = true;
            }

            unset($_POST['pml_login_password']);

            if (isset($_SESSION['pml_logged_in'])) {
                // replace previous login call with call to "form"
                $redirectURL = str_replace('action=login', 'action=', $redirectURL);
                header('Location: ' . $redirectURL);
                exit;
            }

            $userMessage = '';
            if (!empty($password)) {
                $userMessage = Config::__('LoginFailed');
            }

            require_once 'login.php';
            die;
        } catch (UserException $e) {
            $userMessage = $e->getMessage();
            require_once 'login.php';
            die;
        } catch (Exception $e) {
            self::printError($e->getMessage());
        }
    }

    /**
     * Processes action for list specified.
     * @param string? $action
     * @param string? $list
     */
    public static function processRequest($action = null, $list = null) {
        $formEmail = isset($_POST['pmlEmail']) ? mb_strtolower($_POST['pmlEmail']) :
                null;
        $formMessage = isset($_POST['pmlMessage']) ? $_POST['pmlMessage'] : null;
        $formAttachment = isset($_POST['pmlAttachment']) ? $_POST['pmlAttachment'] : null;
        $replyToMsgId = isset($_GET['msgId']) ? urldecode($_GET['msgId']) : null;
        $formModule = isset($_GET['showModule']) ? $_GET['showModule'] : null;

        /**
         * handle logout request, destroy session and continue request
         */
        if ($formModule === 'logout') {
            self::logout();
            $formModule = null; // reset request
        }
        /**
         * handle authentication if activated via configuration
         */
        self::login($list);

        header('Content-type: text/html; charset=utf-8');

        try {
            self::checkForList($list); //auto-initialize list

            $securimage = new Securimage();

            if ($action === 'subc') {
                if (!self::requireAuthentication() &&
                        $securimage->check($_POST['captcha_code']) == false) {
                    throw new UserException('InvalidCAPTCHA');
                }

                self::subscribe($formEmail, $list);
                self::printForm($list, null, null, null, null, Config::__('AuthorizationRequestSent'), false, $formModule);
            } else if ($action === 'unsubc') {
                if (!self::requireAuthentication() &&
                        $securimage->check($_POST['captcha_code']) == false) {
                    throw new UserException('InvalidCAPTCHA');
                }

                self::unsubscribe($formEmail, $list);
                self::printForm($list, null, null, null, null, Config::__('EmailRemovedFromList'), false, $formModule);
            } else if ($action === 'sendmsgc') {
                if (!self::requireAuthentication() &&
                        $securimage->check($_POST['captcha_code']) == false) {
                    throw new UserException('InvalidCAPTCHA');
                }

                $formAttachments = array();

                if (isset($_FILES['attachments'])) {
                    $uploaddir = realpath(PHPMAILINGLIST_BASEPATH . Config::get('tmp_folder'));
                    $attachmentCount = count($_FILES['attachments']['name']);

                    for ($i = 0; $i < $attachmentCount; $i++) {
                        if (empty($_FILES['attachments']['name'][$i])) {
                            continue;
                        }

                        if ($_FILES['attachments']['error'][$i] || !$_FILES['attachments']['size'][$i]) {
                            //echo 'ignoring file ' . $_FILES['attachments']['name'][$i];
                            continue;
                        }

                        $uploadfile = $uploaddir . basename($_FILES['attachments']['name'][$i]);

                        if (!move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $uploadfile)) {
                            //echo 'ignoring file ' . $_FILES['attachments']['name'][$i];
                            continue;
                        } else {
                            $formAttachments[] = $uploadfile;
                        }
                    }
                }

                $sendResult = self::sendMessageToList($formMessage, $list, $formAttachments);

                if ($sendResult === null) { //success
                    self::printForm($list, null, null, null, null, Config::__('MessageSuccessfullySent'), false, $formModule);
                } else { //display error
                    self::printForm($list, $formEmail, $formMessage, $formAttachment, null, $sendResult, false, $formModule);
                }
            } else if ($action === 'authsubc') {
                $hash = isset($_GET['hash']) ? mb_strtolower($_GET['hash']) : null;
                self::subscribeAuthorize($hash, $list);
                self::printForm($list, null, null, null, null, 'Your email has been successfully authorized!<br/>' .
                        'You have now joined the mailing list<br/>' .
                        '<span style="font-weight:bold;">' . $list .
                        '</span><br/>Feel free to send messages or ' .
                        'administrate your account. Enjoy!', false, $formModule);
            } else { // print form (form or admin), optionally load message identified by $replyToMsgId
                self::printForm($list, $formEmail, null, null, $replyToMsgId, null, false, $formModule);
            }
        } catch (UserException $e) {
            self::printForm($list, $formEmail, $formMessage, $formAttachment, $replyToMsgId, $e->getMessage(), false, $formModule);
        } catch (Exception $e) {
            self::printError($e->getMessage());
        }
    }

}
