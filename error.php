<?php
/**
 * @filesource error.php
 * @author Matthias Kerstner <matthias@kerstner.at>
 *
 * @param string? $message
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
        <title>Error - phpMailingList</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" title="CSS Stylesheet" href="style.css" />
    </head>
    <body>

        <div class="outerContainer">

            <div class="header">
                <h1>Error</h1>
            </div>

            <div class="error"><?php echo $message; ?></div>

            <br/><br/>
            <div class="footer">
                powered by <a href="http://www.kerstner.at/phpmailinglist">phpMailingList</a>
            </div>

        </div>

    </body>
</html>