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
                </a>
            </li>
            <?php endwhile ?>
        </ul>
    </body>
</html>
