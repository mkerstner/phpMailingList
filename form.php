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

        <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
        <link rel="stylesheet" type="text/css" title="CSS Stylesheet" href="style.css" />

        <script type="text/javascript">
            jQuery(document).ready(function(){
                $("#accordion").accordion({
                    'autoHeight': false,
                    'collapsible': true,
                    'active': 1
                });
            });
        </script>

    </head>
    <body>

        <div class="outerContainer">

            <h1><?php echo $list; ?></h1>

            <?php
            if (!empty($userMessage)) {
                echo '<div class="sectionOuter usermessage borderBox">' .
                Config::__($userMessage) . '</div>';
            }
            ?>

            <div id="accordion" class="outerContainer">

                <h3><a href="#"><?php echo Config::__('Membership'); ?></a></h3>

                <div class="sectionOuter administration">
                    <form action="?list=<?php echo $list ?>" method="post" enctype="multipart/form-data">
                        <div>
                            <fieldset style="width:500px;">
                                <legend>Email</legend>
                                <input name="email" size="30" type="text" style="width:500px;border:0;"
                                <?php echo (($disabled) ? 'disabled="true"' : ''); ?>
                                       value="<?php echo $email; ?>"/> <br/>
                            </fieldset>
                        </div>
                        <div class="sectionBorder">
                            <div class="biggerLine">
                                <input name="action" value="subc" id="subc" 
                                       checked="checked" type="radio"
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                <label for="subc"><?php echo Config::__('Subscribe'); ?></label>
                                <input name="action" value="unsubc" id="unsubc"
                                       type="radio" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                <label for="unsubc"><?php echo Config::__('Unsubscribe'); ?></label><br/>
                            </div>
                            <div class="captchaOuter">
                                <div class="captchaLeft">
                                    <img id="administrationCaptcha" src="lib/phpcaptcha/securimage_show.php"
                                         alt="<?php echo Config::__('EnterCAPTCHAResultRight'); ?>"
                                         title="<?php echo Config::__('EnterCAPTCHAResultRight'); ?>"
                                         width="100px" height="50px" />
                                </div>
                                <div class="captchaRight">
                                    <input type="text" id="administrationCaptchaCode" name="captcha_code"
                                           size="10" maxlength="6" style="width: 120px;"
                                           title="<?php echo Config::__('EnterCAPTCHAResultHere'); ?>"
                                           <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                    <span style="font-size: smaller;">
                                        <?php
                                        if ($disabled) {
                                            echo '&nbsp;';
                                        } else {
                                            echo '<a href="#" onclick="document.getElementById(\'administrationCaptcha\').src = ' .
                                            '\'lib/phpcaptcha/securimage_show.php?\' + Math.random(); return false">'
                                            . Config::__('ReloadImage') . '</a>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="biggerLine">
                                <input value="<?php echo Config::__('Submit'); ?>" type="submit" title="<?php echo Config::__('Submit'); ?>"
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                <input value="<?php echo Config::__('ShowMembers'); ?>" type="button" title="<?php echo Config::__('ShowMembers'); ?>"
                                <?php echo (($disabled) ? 'disabled="true"' : ''); ?>
                                       onclick="window.open('?list=<?php echo $list; ?>&action=showmembers', 'Members', '');" />
                            </div>
                        </div>
                    </form>
                </div>

                <h3><a href="#"><?php echo Config::__('SendMessage'); ?></a></h3>
                <div class="sectionOuter sendmessage">
                    <form action="?list=<?php echo $list ?>" method="post" enctype="multipart/form-data">

                        <input name="action" type="hidden" value="sendmsgc" />

                        <div style="text-align: center;">
                            <fieldset style="width:500px;">
                                <legend><?php echo Config::__('Message'); ?></legend>
                                <textarea name="message" rows="7" cols="20" style="width:500px; border: 0;"
                                          <?php echo (($disabled) ? 'disabled="true"' : ''); ?>><?php echo (!empty($message) ? $message : ''); ?></textarea>
                            </fieldset>
                        </div>
                        <div style="text-align: center;">
                            <fieldset style="width:500px;">
                                <legend><?php echo Config::__('Attachments'); ?></legend>
                                <input type="file" name="attachments[]" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> style="width: 100%;" />
                                <input type="file" name="attachments[]" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> style="width: 100%;" />
                                <input type="file" name="attachments[]" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> style="width: 100%;" />
                            </fieldset>
                        </div>
                        <div class="sectionBorder">
                            <div class="biggerLine">
                                <div class="captchaOuter">
                                    <div class="captchaLeft">
                                        <img id="sendMessageCaptcha" src="lib/phpcaptcha/securimage_show.php"
                                             alt="<?php echo Config::__('EnterCAPTCHAResultRight'); ?>"
                                             title="<?php echo Config::__('EnterCAPTCHAResultRight'); ?>"
                                             width="100px" height="50px" />
                                    </div>
                                    <div class="captchaRight">
                                        <input type="text" id="sendMessageCaptchaCode" name="captcha_code"
                                               size="10" maxlength="6" style="width: 120px;"
                                               title="<?php echo Config::__('EnterCAPTCHAResultHere'); ?>"
                                               <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                        <span style="font-size: smaller;">
                                            <?php
                                            if ($disabled) {
                                                echo '&nbsp;';
                                            } else {
                                                echo '<a href="#" onclick="document.getElementById(\'sendMessageCaptcha\').src = ' .
                                                '\'lib/phpcaptcha/securimage_show.php?\' + Math.random(); return false">'
                                                . Config::__('ReloadImage')
                                                . '</a>';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="biggerLine">
                                <input type="submit" value="<?php echo Config::__('SendMessage'); ?>" title="<?php echo Config::__('SendMessage'); ?>"
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>

        <hr style="border: 1px dashed #fff; margin: 20px 20px;"/>

        <br/>
        <div class="footer">
            powered by <a href="http://www.kerstner.at/phpmailinglist"
                          target="_blank">phpMailingList</a>
            <br/><br/><br/>
        </div>

        </div>

    </body>
</html>