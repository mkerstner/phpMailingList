<?php
/**
 * @filesource form.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 * @copyright Matthias Kerstner <matthias@kerstner.at>
 *
 * @param string $list
 * @param string $redirectURL
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

    <?php
    require_once '_navigation.php';
    ?>

    <div class="container-fluid">

        <?php if (isset($userMessage) && !empty($userMessage)) : ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php echo $userMessage; ?></div>
        <?php endif; ?>

        <form class="form-signin" name="pml_login" id="pml_login" 
              method="POST" 
              action="<?php echo $redirectURL; ?>">
            <h2 class="form-signin-heading"><?php echo Config::__('PleaseSignIn'); ?></h2>
            <label for="pml_login_password" class="sr-only">Password</label>
            <input type="password" id="pml_login_password" name="pml_login_password" class="form-control" placeholder="<?php echo Config::__('Password'); ?>" required>
            <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo Config::__('SignIn'); ?></button>
        </form>

        <?php
        require_once '_footer.php';
        ?>

    </div> <!-- /container -->
</body>
</html>
