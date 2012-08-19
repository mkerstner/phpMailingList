<?php
/**
 * @filesource form.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 * @copyright Matthias Kerstner <matthias@kerstner.at>
 *
 * @param string $list
 * @param bool $disabled
 * @param string? $email
 * @param string? $message
 * @param string? $userMessage
 *
 * This file is part of phpMailingList.
 * @link http://www.kerstner.at/phpmailinglist
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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <title>Mailing list "<?php echo $list; ?>" - phpMailingList</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" title="CSS Stylesheet" href="style.css" />
    </head>
    <body>

        <div class="outerContainer">

            <?php
            if (!empty($userMessage)) {
                echo '<div class="sectionOuter usermessage borderBox">' .
                $userMessage . '</div>';
            }
            ?>

            <br/><br/>

            <div class="sectionOuter administration">
                <form action="?list=<?php echo $list ?>" method="post">
                    <div>
                        <h1>Email Administration</h1>
                    </div>
                    <div>
                        <input name="email" size="30" type="text" style="width:500px;"
                        <?php echo (($disabled) ? 'disabled="true"' : ''); ?>
                               value="<?php echo $email; ?>"/> <br/>
                    </div>
                    <div class="sectionBorder">
                        <div class="biggerLine">
                            <input name="action" value="subc" id="subc" 
                                   checked="checked" type="radio"
                                   <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                            <label for="subc">Subscribe</label>
                            <input name="action" value="unsubc" id="unsubc"
                                   type="radio" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                            <label for="unsubc">Unsubscribe</label><br/>
                        </div>
                        <div class="captchaOuter">
                            <div class="captchaLeft">
                                <img id="administrationCaptcha" src="lib/phpcaptcha/securimage_show.php"
                                     alt="CAPTCHA Image - Please enter result on the right."
                                     title="CAPTCHA Image - Please enter result on the right."
                                     width="100px" height="50px" />
                            </div>
                            <div class="captchaRight">
                                <input type="text" id="administrationCaptchaCode" name="captcha_code"
                                       size="10" maxlength="6" style="width: 120px;"
                                       title="Please enter CAPTCHA result here."
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                <span style="font-size: smaller;">
                                    <?php
                                    if ($disabled) {
                                        echo '&nbsp;';
                                    } else {
                                        echo '<a href="#" onclick="document.getElementById(\'administrationCaptcha\').src = ' .
                                        '\'lib/phpcaptcha/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="biggerLine">
                            <input value="Submit" type="submit" title="Submit"
                            <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                            <input value="Show Members" type="button" title="Show Members"
<?php echo (($disabled) ? 'disabled="true"' : ''); ?>
                                   onclick="window.open('?list=<?php echo $list; ?>&action=showmembers', 'Members', '');" />
                        </div>
                    </div>
                </form>
            </div>

            <br/>

            <div class="sectionOuter sendmessage">
                <form action="?list=<?php echo $list ?>" method="post">

                    <input name="action" type="hidden" value="sendmsgc" />

                    <div style="text-align: center;">
                        <h1>Send Message to List <br/><span class="highlight"><?php echo $list; ?></span></h1>
                    </div>
                    <div style="text-align: center;">
                        <textarea name="message" rows="7" cols="20" style="width:500px;"
<?php echo (($disabled) ? 'disabled="true"' : ''); ?>><?php echo (!empty($message) ? $message : ''); ?></textarea>
                    </div>
                    <div class="sectionBorder">
                        <div class="biggerLine">
                            <div class="captchaOuter">
                                <div class="captchaLeft">
                                    <img id="sendMessageCaptcha" src="lib/phpcaptcha/securimage_show.php"
                                         alt="CAPTCHA Image - Please enter result on the right."
                                         title="CAPTCHA Image - Please enter result on the right."
                                         width="100px" height="50px" />
                                </div>
                                <div class="captchaRight">
                                    <input type="text" id="sendMessageCaptchaCode" name="captcha_code"
                                           size="10" maxlength="6" style="width: 120px;"
                                           title="Please enter CAPTCHA result here."
                                        <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                    <span style="font-size: smaller;">
                                        <?php
                                        if ($disabled) {
                                            echo '&nbsp;';
                                        } else {
                                            echo '<a href="#" onclick="document.getElementById(\'sendMessageCaptcha\').src = ' .
                                            '\'lib/phpcaptcha/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="biggerLine">
                            <input type="submit" value="Send Message" title="Send Message"
<?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                        </div>
                    </div>
                </form>
            </div>

            <br/>
            <div class="footer">
                powered by <a href="http://www.kerstner.at/phpmailinglist"
                              target="_blank">phpMailingList</a>
            </div>

        </div>

    </body>
</html>