<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Listing.php';

$group = 0;
if (!empty($_GET['group'])) {
    $group = (int)$_GET['group'];
}

$listing = new Listing();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Listing</title>
    <!--    <link rel="stylesheet" href="style.css">-->
    <style>
        .content {
            margin: 0 auto;
        }

        .listing {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: max-content;
            grid-gap: 100px;
        }

        .active a {
            color: green;
        }

        li {
            list-style: circle;
        }
    </style>
</head>
<body>
<section>
    <div class="content">
        <div class="listing">
            <div class="groups">
                <a href="?">Все товары</a>
                <?php echo $listing->printGroupLists($group) ?>
            </div>
            <div class="products">
                <?php echo $listing->printProductsList($group) ?>
            </div>
        </div>
    </div>
</section>
</body>
</html>
