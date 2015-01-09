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
 * @param string? $gaTrackingId
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
        <title><?php echo Config::__('MailingList') . ': ' . $list; ?> | phpMailingList</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

        <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
        <link rel="stylesheet" type="text/css" title="CSS Stylesheet" href="style.css" />        

        <script type="text/javascript">
            jQuery(document).ready(function () {
                $("#accordion").accordion({
                    'autoHeight': false,
                    'collapsible': true,
                    'active': <?php echo ($formModule == 'admin' ? 0 : 1) ?>
                });
<?php if (!empty($message)) : ?>
                    var msg = '<?php echo htmlentities(str_replace("'", "\'", preg_replace('/<br(\s)*(\/)?>/', '\n', $message))); ?>';
                    $('textarea[name="message"]').html(msg);
<?php endif; ?>
            });
        </script>

        <?php if (!empty($gaTrackingId)) : ?>
            <script>
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                            m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

                ga('create', '<?php echo $gaTrackingId; ?>', 'auto');
                ga('send', 'pageview');

            </script>
        <?php endif; ?>

    </head>
    <body>

        <div class="outerContainer">

            <h1><?php echo Config::__('MailingList') ?>: <span class="header-list"><?php echo $list; ?></span></h1>

            <?php
            if (!empty($userMessage)) {
                echo '<div class="sectionOuter usermessage borderBox">' .
                Config::__($userMessage) . '</div>';
            }
            ?>

            <div id="languageSwitcher">
                <?php

                /**
                 * Return link with @locale set.
                 * @param string $locale
                 * @return string 
                 * @todo move me to proper source file
                 */
                function getLocaleUrl($locale) {
                    $url = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://'
                            . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

                    if (mb_strpos($url, 'locale=') !== false) {
                        $url = preg_replace('/locale=([^&]*|$)/i', 'locale=' . $locale, $url);
                    } else {
                        $url .= '&locale=' . $locale;
                    }

                    return $url;
                }
                ?>
                <a href="<?php echo getLocaleUrl('en_US'); ?>" class="<?php echo mb_strpos(Config::getLocale(), 'en_') !== false ? 'active' : '' ?>">EN</a> | 
                <a href="<?php echo getLocaleUrl('de_AT'); ?>" class="<?php echo mb_strpos(Config::getLocale(), 'de_') !== false ? 'active' : '' ?>">DE</a>
            </div>

            <div id="accordion" class="outerContainer">

                <h3><a href="#"><?php echo Config::__('Membership'); ?></a></h3>

                <div class="sectionOuter administration">
                    <form action="?list=<?php echo $list ?>&showModule=admin&locale=<?php echo Config::getLocale(); ?>" 
                          method="post" enctype="multipart/form-data">
                        <div>
                            <fieldset>
                                <legend>Email</legend>
                                <input name="email" size="40" type="text" 
                                       style="width:475px; border:0;"
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
                                         width="100px" height="50px" /><br/>
                                    <span style="font-size: smaller;">
                                        <?php
                                        if ($disabled) {
                                            echo '&nbsp;';
                                        } else {
                                            echo '<a href="#" onclick="document.getElementById(\'administrationCaptcha\').src = ' .
                                            '\'lib/phpcaptcha/securimage_show.php?\' + Math.random(); return false">'
                                            . Config::__('ReloadImage') . '</a>&nbsp;&nbsp;';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="captchaRight">
                                    <input type="text" id="administrationCaptchaCode" 
                                           name="captcha_code"
                                           size="10" maxlength="6" style="width: 120px; margin-left: 20px;"
                                           title="<?php echo Config::__('EnterCAPTCHAResultHere'); ?>"
                                           <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                </div>
                            </div>
                            <div class="biggerLine">
                                <input value="<?php echo Config::__('Submit'); ?>" 
                                       type="submit" 
                                       title="<?php echo Config::__('Submit'); ?>"
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                <input value="<?php echo Config::__('ShowMembers'); ?>" 
                                       type="button" 
                                       title="<?php echo Config::__('ShowMembers'); ?>"
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?>
                                       onclick="window.open('?list=<?php echo $list; ?>&action=showmembers', 'Members', '');" />
                            </div>
                        </div>
                    </form>
                </div>

                <h3><a href="#"><?php echo Config::__('SendMessage'); ?></a></h3>
                <div class="sectionOuter sendmessage">
                    <form action="?list=<?php echo $list ?>&showModule=send&locale=<?php echo Config::getLocale(); ?>" 
                          method="post" enctype="multipart/form-data">

                        <input name="action" type="hidden" value="sendmsgc" />

                        <div style="text-align: center;">
                            <fieldset>
                                <legend><?php echo Config::__('Message'); ?></legend>
                                <textarea name="message" rows="7" cols="20" style="width:475px; border: 0;" <?php echo (($disabled) ? 'disabled="true"' : ''); ?>></textarea>
                            </fieldset>
                        </div>
                        <div style="text-align: center;">
                            <fieldset>
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
                                             width="100px" height="50px" /><br/>
                                        <span style="font-size: smaller;">
                                            <?php
                                            if ($disabled) {
                                                echo '&nbsp;';
                                            } else {
                                                echo '<a href="#" onclick="document.getElementById(\'sendMessageCaptcha\').src = ' .
                                                '\'lib/phpcaptcha/securimage_show.php?\' + Math.random(); return false">'
                                                . Config::__('ReloadImage') . '</a>&nbsp;&nbsp;';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="captchaRight">
                                        <input type="text" id="sendMessageCaptchaCode" 
                                               name="captcha_code"
                                               size="10" maxlength="6" style="width: 120px; margin-left: 20px;"
                                               title="<?php echo Config::__('EnterCAPTCHAResultHere'); ?>"
                                               <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                                    </div>
                                </div>
                            </div>
                            <div class="biggerLine">
                                <input type="submit" 
                                       value="<?php echo Config::__('SendMessage'); ?>" 
                                       title="<?php echo Config::__('SendMessage'); ?>"
                                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>

        <hr style="border: 1px dashed #fff; margin: 10px 10px;"/>

        <br/>
        <div class="footer">
            powered by <a href="http://www.kerstner.at/phpmailinglist"
                          target="_blank">phpMailingList</a>
            <br/><br/><br/>
        </div>

        </div>

    </body>
</html>