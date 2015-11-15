<?php
/**
 * @filesource error.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 *
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
    require '_navigation.php';
    ?>

    <div class="container-fluid">

        <div class="page-header">
            <h1><?php echo Config::__('WellThatsEmbarassing'); ?></h1>
        </div>

        <?php if (isset($userMessage) && !empty($userMessage)) : ?>
            <div class="alert alert-warning" role="alert">
                <?php echo $userMessage; ?></div>
            <?php endif; ?>

        <?php
        require '_footer.php';
        ?>

    </div> <!-- /container -->
</body>
</html>
