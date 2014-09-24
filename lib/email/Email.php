<?php

/**
 * @filesource Email.php
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
require_once 'lib/swiftmailer/swift_required.php';

define('EMAIL_LIST_SEPERATOR', ';');

abstract class Email {

    /**
     * Checks email specified for syntactical validity and on success returns
     * arrays with email and recipient name split. Otherwise throws Exception.
     * @param <string|array> $email
     * @return <array> in format [email=>name] whereas name defaults to email if
     *                 no name was specified. Throws Exception on error.
     */
    public static function verifyAndSplitEmail($email) {
        if (empty($email)) {
            throw new Exception('Empty email verification list specified.');
        }

        $emailList = is_array($email) ? $email : array($email);
        $filteredEmailList = array();
        
        foreach ($emailList as $v) {

            if (trim($v) === '') {
                continue; //ignore empty entries
            }

            $split = explode('@', $v);

            if (count($split) !== 2) {
                throw new Exception('Invalid email: \'' . $v . '\'');
            }

            foreach ($split as $sK => $sV) { //remove ambigious symbols
                $split[$sK] = preg_replace(array('/"/', '/</', '/>/'), array('', '', ''), $sV);
            }

            $name = mb_strrchr($split[0], ' ');
            $address = '';

            if (!$name) { //only address specified
                $name = '';
                $address = '<' . trim($split[0] . '@' . $split[1]) . '>';
            } else {
                $address = '<' . trim($name . '@' . $split[1]) . '>';
                $name = trim(preg_replace('/' . $name . '/', '', $split[0]));
            }

            //simple email syntax check, nothing fancy...
            if (!mb_ereg_match('^<[A-Za-z0-9._-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}>$', $address)) {
                throw new Exception('Invalid email: ' . $address);
            }

            if (empty($name)) {
                $name = $address; //set name to address if empty (not specified)
            }

            $address = preg_replace(array('/</', '/>/'), array('', ''), $address);
            $domain = mb_substr($address, mb_strpos($address, '@') + 1);

            if (!checkdnsrr($domain)) {
                throw new Exception('Invalid domain "' . $domain . '" in email.');
            }

            $filteredEmailList[] = array($address => $name);
        }

        return $filteredEmailList;
    }

    /**
     * Sends mail, optionally with attachments. In case you want to send mail to
     * BCC recipients only simply specify empty $to (null or empty string) and
     * use list for $bcc instead. Note that either $to or $bcc must *not* be
     * empty, otherwise an Exception will be thrown. Also, if no valid email
     * could be found in either of the two lists an Exception will be thrown.
     * Delegates control to Swiftmailer for sending mails.
     * @param <string> $from from email address
     * @param <string> $to recipient list seperated by EMAIL_LIST_SEPERATOR
     * @param <string> $body message body of email to be sent
     * @param <string?> $subject subject of mail to be sent
     * @param <array?> $attachments absolute path to files to be attached
     * @param <string?> $bcc BCC address(es)
     * @see lib/swiftmailer
     */
    public static function sendEmail($from, $to, $body, $subject = null, $attachments = null, $bcc = null) {
        try {
            $fromChecked = self::verifyAndSplitEmail($from);
            $recipients = !empty($to) ? self::verifyAndSplitEmail(explode(
                                    EMAIL_LIST_SEPERATOR, $to)) : array();
            $bccChecked = !empty($bcc) ? self::verifyAndSplitEmail(explode(
                                    EMAIL_LIST_SEPERATOR, $bcc)) : array();

            if (count($recipients) < 1 && count($bccChecked) < 1) {
                throw new Exception('No valid email specified.');
            }

            $mailerTransport = Swift_SmtpTransport::newInstance(
                            Config::get('email_smtp_server'), Config::get('email_smtp_port'));

            if (Config::get('email_auth') != '') { //Authentication required
                $mailerTransport->setUsername(Config::get('email_user'))->
                        setPassword(Config::get('email_pass'));
            }

            $mailer = Swift_Mailer::newInstance($mailerTransport);
            $mail = Swift_Message::newInstance();

            $mail->setFrom($fromChecked[0]);

            foreach ($recipients as $v) {
                foreach ($v as $kR => $vR) {
                    $mail->addTo($kR, $vR); //add TO consecutively
                }
            }

            foreach ($bccChecked as $v) {
                foreach ($v as $kR => $vR) {
                    $mail->addBcc($kR, $vR); //add BCC consecutively
                }
            }

            $mail->setSubject((!empty($subject) ? $subject : ''));

            if (is_array($attachments)) { //set attachments by path specified
                foreach ($attachments as $v) {
                    $mail->attach(Swift_Attachment::fromPath($v)->
                                    setDisposition('inline'));
                }
            }

            $mail->setBody($body, 'text/plain');

            $failed = array();

            if ($mailer->send($mail, $failed))
                return; //success

            $errMsg = 'Failed to send email.';
            if (count($failed) > 0) { //recipient error
                $errMsg .= ' Failed to send email to the following recipients: "' .
                        implode(', ', $failed) . '"';
            }

            throw new Exception($errMsg);
        } catch (Exception $e) {
            throw new Exception('Failed to send email: ' . $e->getMessage());
        }
    }

}
