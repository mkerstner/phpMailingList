<?php

/**
 * @filesource PhpMailingList.php
 * @author Matthias Kerstner <info@kerstner.at>
 * @copyright Matthias Kerstner <info@kerstner.at>
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
     * @param string? $authHash
     * @return string? email of authenticated user
     */
    private static function checkAuth($authHash = null) {
        if (Config::get('require_authentication') != '') {
            throw new Exception('Failed to authenticate.' .
                    '<br/>Please use the link provided in the initial ' .
                    'subscription mail.');
        }
    }

    /**
     * Checks if list-directory exists and initializes it on demand, otherwise
     * throws an Exception.
     * @param string $list
     */
    private static function checkForList($list) {

        if (empty($list)) {
            throw new Exception('No list specified.');
        } else if (!file_exists(self::getListFolder($list))) {
            throw new Exception('List<br/><span style="font-weight:bold;">' .
                    $list . '</span><br/>does not exist.');
        }

        $membersHandle = fopen(self::getMembersFilePath($list), 'ab+');
        $authHandle = fopen(self::getSubscribeAuthorizationFilePath($list), 'ab+');
        $unauthHandle = fopen(self::getUnsubscribeAuthorizationFilePath($list), 'ab+');
        $htaccessHandle = fopen(PHPMAILINGLIST_BASEPATH .
                Config::get('lists_folder') . '.htaccess', 'ab+');

        if (!$membersHandle || !$authHandle || !$unauthHandle ||
                !$htaccessHandle) {
            throw new Exception('Failed to initialize list ' . $list);
        }

        $htaccessContent = 'deny from all';
        if (!fwrite($htaccessHandle, $htaccessContent, mb_strlen($htaccessContent))) {
            throw new Exception('Failed to create htaccess file.');
        }

        fclose($membersHandle);
        fclose($authHandle);
        fclose($unauthHandle);
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
            if ($email === $member[1])
                return true;
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

        if ($memberEmail !== null)
            return $memberEmail;

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
        if (!$handle)
            throw new Exception('Failed to open members file.');
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
        if (!$handle)
            throw new Exception('Failed to open list file.');

        $membersData = fread($handle, filesize($filename) + 1);
        fclose($handle);

        if (!$membersData)
            throw new Exception('Failed to read members data.');

        $membersData = preg_replace("/\n<[^>]+> : <$email>/", '', $membersData);

        $handle = fopen($filename, 'wb');
        if (!$handle)
            throw new Exception('Failed to open list file.');
        if (fputs($handle, $membersData) === false)
            throw new Exception('Failed to write to list file.');

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
     * Returns path to list folder.
     * @param string $list
     * @return string
     */
    private static function getListFolder($list) {
        return (PHPMAILINGLIST_BASEPATH . Config::get('lists_folder') .
                basename($list) . PHPMAILINGLIST_DIRSEPERATOR);
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
        if (!$urlParts)
            throw new Exception('Failed to parse url.');

        $baseUrl = ($urlParts['scheme'] . '://' . $urlParts['host'] .
                $urlParts['path']);

        if ($baseUrlOnly)
            return $baseUrl;
        return ($baseUrl . $urlParts['query']);
    }

    /**
     * Prints form, optionally filling it with the arguments provided below.
     * Automatically checks if list requested has a custom form file and loads
     * it on demand.
     * @param string? $list current list selected
     * @param string? $email email currently entered
     * @param string? $message message currently entered
     * @param string? $userMessage message for the user
     * @param bool? $disabled whether the form is disabled or not
     * @see form.php
     * @see config.ini
     */
    private static function printForm($list = null, $email = null, $message = null, $userMessage = null, $disabled = false) {
        $formFilename = Config::get('form_file');
        $formFile = self::getListFolder($list) . $formFilename;

        if (!empty($formFilename) && file_exists($formFile)) {
            require_once $formFile; //customized file exists
        } else {
            require_once PHPMAILINGLIST_BASEPATH . 'form.php'; //take default
        }
    }

    /**
     * Prints members.
     * @param string $list
     * @see members.php
     * @see config.ini
     */
    private static function printMembers($list) {
        $membersData = self::getMembers($list);
        $members = array();

        foreach ($membersData as $member) {
            $members[] = $member[1];
        }

        $membersFilename = Config::get('members_file');
        $membersFile = self::getListFolder($list) . $membersFilename;

        if (!empty($membersFilename) && file_exists($membersFile)) {
            require_once $membersFile; //customized file exists
        } else {
            require_once PHPMAILINGLIST_BASEPATH . 'members.php'; //take default
        }
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
            throw new UserException('Failed to subscribe: ' . $e->getMessage());
        }

        if (self::isMember($list, $email)) {
            throw new UserException('Email is already included in this ' .
                    'mailing list.');
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
            throw new UserException('Email is already pending authorization ' .
                    'for subscription.');
        }

        $hash = md5($email . (string) time() . (string) rand(1, 256));

        if (fputs($authorizationHandle, "\n<$hash> : <$email>") === false) {
            throw new Exception('Failed to subscribe. Could not write to ' .
                    'authorization file.');
        }
        fclose($authorizationHandle);

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
        fclose($authorizationHandle);

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
        if (!preg_match($regexp, $authorizationFileContent, $matches))
            throw new Exception('Failed to read data from authorization file.');

        $email = $matches[2];
        $authorizationFileContent = preg_replace($regexp, '', $authorizationFileContent); //remove email

        $authorizationHandle = fopen($authorizationFile, 'wb');
        if (!$authorizationHandle)
            throw new Exception('Failed to open authorization file.');
        if (fputs($authorizationHandle, $authorizationFileContent) === false) {
            throw new Exception('Failed to authorize subscription: ' .
                    'Could not write to authorization file.');
        }

        fclose($authorizationHandle);

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

        if (!self::isMember($list, $email))
            throw new UserException('Email is not subscribed to this mailing list.');

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
     * @return string? error message in case of error, otherwise NULL.
     */
    private static function sendMessageToList($message, $list) {

        if (empty($message))
            throw new UserException('Failed to send message: No message specified.');

        $members = self::getMembers($list);
        if (count($members) < 1)
            throw new UserException('List does not have any members.');

        $from = $list . ' <' . Config::get('email_reply_to') . '>';
        $subject = 'Message from list "' . $list . '"';
        $message .= "\n\n**In order to reply to this message please go to:\n\n" .
                self::getCurrentUrl(true) . '?list=' . $list;
        $recipients = '';

        foreach ($members as $member) {
            $recipients .= ( ($recipients !== '' ? EMAIL_LIST_SEPERATOR : '') .
                    $member[1]);
        }

        try {
            Email::sendEmail($from, null, wordwrap($message .
                            self::getFooter(true)), $subject, null, $recipients);
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

        if ($lines === false)
            throw new Exception('Failed to open members file.');

        foreach ($lines as $line) {
            $member = explode(' : ', $line); //[0]=hash, [1]=email

            if (count($member) < 2)
                throw new Exception('Invalid syntax in members file.');

            //[0]=hash, [1]=email
            $members[] = array($member[0], self::removeEmailTags($member[1]));
        }

        return $members;
    }

    /**
     * Processes action for list specified.
     * @param string $action
     * @param string $list
     */
    public static function processRequest($action = null, $list = null) {

        $authHash = isset($_GET['auth']) ? $_GET['auth'] : null;
        $formEmail = isset($_POST['email']) ? mb_strtolower($_POST['email']) :
                null;
        $formMessage = isset($_POST['message']) ? $_POST['message'] : null;
        $replyToMsg = isset($_GET['replyToMsg']) ?
                urldecode(mb_substr($_GET['replyToMsg'], 0, Config::get('max_reply_to_msg_length'))) : null;

        try {

            self::checkAuth($authHash); //check authentication on demand
            self::checkForList($list); //auto-initialize list

            $securimage = new Securimage();

            if ($action === 'subc') {
                if ($securimage->check($_POST['captcha_code']) == false) {
                    throw new UserException('Invalid CAPTCHA given, please ' .
                            'try again.');
                }

                self::subscribe($formEmail, $list);
                self::printForm($list, null, null, 'An authorization request has been sent to<br/>' .
                        '<span style="font-weight:bold;">' . $formEmail .
                        '</span>.<br/>Please follow the instructions ' .
                        'in the mail<br/>in order to complete your' .
                        '<br/>subscription to the mailing list<br/>' .
                        '<span style="font-weight:bold;">' . $list .
                        '</span>.');
            } else if ($action === 'unsubc') {
                if ($securimage->check($_POST['captcha_code']) == false) {
                    throw new UserException('Invalid CAPTCHA given, please ' .
                            'try again.');
                }

                self::unsubscribe($formEmail, $list);
                self::printForm($list, null, null, 'Your email<br/>' .
                        '<span style="font-weight:bold;">' . $formEmail .
                        '</span><br/>has been removed from our mailing list.');
            } else if ($action === 'sendmsgc') {
                if ($securimage->check($_POST['captcha_code']) == false)
                    throw new UserException('Invalid CAPTCHA given, please try again.');

                $sendResult = self::sendMessageToList($formMessage, $list);

                if ($sendResult === null) { //success
                    self::printForm($list, null, null, 'Successfully sent message to members of list.');
                } else { //display error
                    self::printForm($list, $formEmail, $formMessage, $sendResult);
                }
            } else if ($action === 'authsubc') {
                $hash = isset($_GET['hash']) ? mb_strtolower($_GET['hash']) : null;
                self::subscribeAuthorize($hash, $list);
                self::printForm($list, null, null, 'Your email has been successfully authorized!<br/>' .
                        'You have now joined the mailing list<br/>' .
                        '<span style="font-weight:bold;">' . $list .
                        '</span><br/>Feel free to send messages or ' .
                        'administrate your account. Enjoy!');
            } else if ($action === 'showmembers') {
                self::printMembers($list);
            } else {
                self::printForm($list, $formEmail, $replyToMsg);
            }
        } catch (UserException $e) {
            self::printForm($list, $formEmail, $formMessage, $e->getMessage());
        } catch (Exception $e) {
            self::printError($e->getMessage());
        }
    }

}

?>
