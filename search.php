<?php

// On search
if (isset($_POST['search'])){

    // Connect to mysql
    $link = mysqli_connect("127.0.0.1", "testdb_admin", "majesticeagle", "testdb");

    if (!$link) {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }

    // Select query
    $q = mysqli_real_escape_string($link, $_POST['search_query']);
    $query = "SELECT * FROM Articles WHERE MATCH(title, body, description) AGAINST ('$q' IN BOOLEAN MODE)";
    $r = mysqli_query($link, $query);

    // Display number of results
    echo mysqli_num_rows($r) . " result(s).";
    if (mysqli_num_rows($r) == 0){
        echo " Query is too general or too specific.";
    }

    if ($r) {
        // Display each result
        while ($row = mysqli_fetch_array($r)) {
            echo  "<div class='article'>
                        <h1>{$row['title']}</h1>
                        <p><b>Description</b>: {$row['description']}</p>
                        <p><b>Body</b>: {$row['body']}</p>
                        <img src='{$row['image']}'>
                        ";
            if ($row['video']){
                echo "<p><b>Video</b>: <a href='{$row['video']}'>{$row['video']}</a></p>";
            } else{
                echo "<p><b>Video</b>: None</p>";
            }
            echo "</div>";
        }
    }
    else{
        // If error with select
        echo 'Could not retrieve data because ' . mysqli_error($link) . '<br>the query was ' . $query;
    }

    mysqli_close($link);
}

?>