<?php
/**
 * Index.php for example application
 *
 * @category Index.php
 * @package  Main
 * @author   David Brown <dmlb2000@gmail.com>
 * @license  https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License, version 2.1
 * @link     https://github.com/pacifica/pacifica-search
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>List of Posts</title>
    </head>
    <body>
        <h1>List of Posts</h1>
        <ul>
            <?php $x = 0; while($x <= 5): ?>
            <li>
                <a href="/show.php?id=<?= $x ?>">
                    <?= $x ?>
                    <? $x += 1; ?>
                </a>
            </li>
            <?php endwhile ?>
        </ul>
    </body>
</html>
