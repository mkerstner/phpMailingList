<?php
/**
 * @filesource admin.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 * @copyright Matthias Kerstner <matthias@kerstner.at>
 *
 * @param string $list
 * @param bool $disabled
 * @param array $members
 * @param array $pendingMembers
 * @param string? $email
 * @param string? $message
 * @param string? $userMessage
 *
 * This file is part of phpMailingList.
 * @link https://www.kerstner.at/phpmailinglist
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

    <?php
    require_once '_navigation.php';
    ?>

    <div class="container-fluid">

        <?php if (isset($userMessage) && !empty($userMessage)) : ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php echo $userMessage; ?></div>
        <?php endif; ?>

        <form action="?list=<?php echo $list ?>&showModule=admin&locale=<?php echo Config::getLocale(); ?>"
              id="MembershipForm" name="membershipForm"
              method="post" enctype="multipart/form-data">

            <div class="form-group">
                <p class="text-left" style="font-weight:bold;"><?php echo Config::__('AdministrateListHere'); ?>:</p>
                <div class="input-group">
                    <span class="input-group-addon" id="sizing-addon2">Email</span>
                    <input type="text" class="form-control" 
                           placeholder="<?php echo Config::__('EgJohnAtDoe'); ?>" 
                           aria-describedby="sizing-addon2" 
                           name="pmlEmail"
                           required
                           id="pmlEmail"
                           <?php echo (($disabled) ? 'disabled="true"' : ''); ?>
                           value="<?php echo $email; ?>" />
                </div>
            </div>

            <div class="form-group">
                <input name="action" value="subc" id="subc" 
                       checked="checked" type="radio"
                       <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                <label for="subc"><?php echo Config::__('Subscribe'); ?></label>
                <input name="action" value="unsubc" id="unsubc"
                       type="radio" <?php echo (($disabled) ? 'disabled="true"' : ''); ?> />
                <label for="unsubc"><?php echo Config::__('Unsubscribe'); ?></label>
            </div>

            <div class="form-group">
                <?php if (!Config::get('require_authentication')) : ?>
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
                <?php endif; ?>
            </div>

            <div class="form-group text-center">
                <button type="submit" 
                        class="btn btn-lg btn-primary btn-block"
                        title="<?php echo Config::__('Submit'); ?>"
                        <?php echo (($disabled) ? 'disabled="true"' : ''); ?>><?php echo Config::__('Submit'); ?></button>
            </div>

        </form>
    </div>

    <div class="container-fluid">
        <p style="font-weight: bold;"><?php echo Config::__('MembersOfList') . ' ' . $list; ?>:</p>
        <ul>
            <?php
            if (!count($members)) {
                echo '<li>' . Config::__('ListHasNoMembers') . '</li>';
            } else {
                foreach ($members as $v) {
                    if ($v !== '') {
                        echo '<li>' . $v[1] . '</li>';
                    }
                }
            }
            ?>
        </ul>

        <p style="font-weight: bold;"><?php echo Config::__('PendingAuthorizationsOfList') . ' ' . $list; ?>:</p>
        <ul>
            <?php
            if (!count($pendingMembers)) {
                echo '<li>' . Config::__('ListHasNoMembers') . '</li>';
            } else {
                foreach ($pendingMembers as $v) {
                    if ($v !== '') {
                        echo '<li>' . $v[1] . '</li>';
                    }
                }
            }
            ?>
        </ul>
    </div>    

</body>
</html>
