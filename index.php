<!DOCTYPE html>
<html>
<head>
    <style>
        body{
            padding: 50px;
        }

        img{
            max-height: 200px;
        }

    </style>
</head>
    <body>
        <?php ini_set('display_errors', 'On'); ?>

        <form action="" method="post">
            <input type="text" name="search_query" placeholder="Search Query ie 'company'">
            <input type="submit" value="Search" name="search">
        </form>

        <form action="" method="post">
            <input type="submit" value="Scrape Techcrunch" name="scrape">
        </form>

        <form action="" method="post">
            <input type="submit" value="Reset Database" name="reset">
        </form>

        <?php include "scraper.php" ?>
        <?php include "search.php" ?>

    </body>
</html>