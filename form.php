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

<?php
require_once '_head.php';
?>

<body>

    <script type="text/javascript">
        $(document).ready(function () {
<?php if (!empty($message)) : ?>
                var msg = '<?php echo htmlentities(str_replace("'", "\'", preg_replace('/<br(\s)*(\/)?>/', '\n', $message))); ?>';
                $('textarea[name="pmlMessage"]').html(msg);
<?php endif; ?>

            $('form[id="SendMessageForm"]').submit(function (evt) {

<?php
if (Config::get('google_analytics_event_send_category') !== '' &&
        Config::get('google_analytics_event_send_action') !== '' &&
        Config::get('google_analytics_event_send_label') !== '') :
    ?>
                    if (typeof ga !== 'undefined') {
                    ga('send', 'event',
                            '<?php echo Config::get('google_analytics_event_send_category'); ?>',
                            '<?php echo Config::get('google_analytics_event_send_action'); ?>',
                            '<?php echo Config::get('google_analytics_event_send_label'); ?>'
    <?php echo Config::get('google_analytics_event_send_value') !== '' ? (',' . (int) Config::get('google_analytics_event_send_value')) : ''; ?>);
                    }
<?php endif; ?>

                return true;
            });
            });</script>

    <script type="text/javascript">
<?php
if (isset($_GET['msgId']) &&
        isset($_GET['utm_source']) &&
        isset($_GET['utm_medium']) &&
        isset($_GET['utm_campaign'])) :
    ?>
                if (typeof ga !== 'undefined') {
                ga('send', 'event',
                        '<?php echo Config::get('google_analytics_event_openreply_category'); ?>',
                        '<?php echo Config::get('google_analytics_event_openreply_action'); ?>',
                        '<?php echo Config::get('google_analytics_event_openreply_label'); ?>'
    <?php echo Config::get('google_analytics_event_openreply_value') !== '' ? (',' . (int) Config::get('google_analytics_event_openreply_value')) : (',\'' . (int) $_GET['msgId'] . '\''); ?>);
            }
<?php endif; ?>
    </script>

    <?php
    require_once '_navigation.php';
    ?>

    <div class="container-fluid">

        <?php if (isset($userMessage) && !empty($userMessage)) : ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php echo $userMessage; ?></div>
        <?php endif; ?>

        <form action="?list=<?php echo $list ?>&showModule=send&locale=<?php echo Config::getLocale(); ?>"
              id="SendMessageForm" name="sendMessageForm"
              method="post" enctype="multipart/form-data">

            <input name="action" type="hidden" value="sendmsgc" />

            <div class="form-group">
                <label for="pmlMessage"><?php echo Config::__('YourMessageToList'); ?> <?php echo $list; ?>:</label>
                <textarea class="form-control" rows="14" cols="50" placeholder="<?php echo Config::__('YourMessage'); ?>..." id="pmlMessage" name="pmlMessage" required></textarea>
            </div>

            <?php if (Config::get('enable_attachments')) : ?>
                <div class="form-group">
                    <label><?php echo Config::__('OptionalAttachments'); ?>:</label>
                    <input type="file" name="attachments[]" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> style="width: 100%;" />
                    <input type="file" name="attachments[]" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> style="width: 100%;" />
                    <input type="file" name="attachments[]" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> style="width: 100%;" />
                </div>
            <?php endif; ?>

            <?php if (!Config::get('require_authentication')) : ?>
                <div class="form-group">
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
                    </div>
                <?php endif; ?>

                <div class="form-group text-center">
                    <button type="submit" 
                            class="btn btn-lg btn-primary btn-block"
                            title="<?php echo Config::__('SendMessage'); ?>"
                            <?php echo (($disabled) ? 'disabled="true"' : ''); ?>><?php echo Config::__('SendMessage'); ?></button>
                </div>
            </div>
        </form>

        <?php
        require_once '_footer.php';
        ?>

    </div> <!-- /container -->
</body>
</html>
