<?php
ini_set('display_errors', 'On');
require('simple_html_dom.php');

if (isset($_POST['scrape'])){

    $number_scraped = 0;

    // Connect to db
    $link = mysqli_connect("127.0.0.1", "testdb_admin", "majesticeagle", "testdb");
    if (!$link) {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        exit;
    }

    // Check maximum of 3 pages back
    for ($i = 1; $i <= 3; $i++){

        // Create DOM from URL
        $html = file_get_html("http://techcrunch.com/page/$i");

        // Find each article on main page
        foreach($html->find('li.river-block') as $li) {
            $id = $li->id;
            if ($id != 0){
                // Parse title
                $title = $li->find('h2', 0);
                $title_text = $title->plaintext;
                
                // Get article URL
                $url = $title->find('a', 0)->href;
                
                // Parse excerpt
                $description = $li->find('p.excerpt', 0);
                $description_text = "";
                if ($description){
                    $description->find('a', 0)->innertext = "";
                    $description->find('a', 0)->outertext = "";
                    $description_text = $description->plaintext;
                }

                // Go to article page to get full body text
                $page_html = file_get_html($url);
                $article = $page_html->find('.article-entry', 0);
                $body = "";

                if ($article){
                    foreach ($article->find('p') as $p){
                        $body .= '<p>' . $p->plaintext . '</p>';
                    }

                    $hero_img = $page_html->find('.img-hero', 0);
                    if ($hero_img){
                        $img_src = $hero_img->src;
                    }
                    else {
                        $img_src = $article->find('img', 0)->src;
                    }
                }
                else{
                    // Article is slideshow type
                    foreach ($page_html->find('.intro-text', 0)->find('p') as $p){
                        $body .= '<p>' . $p->plaintext . '</p>';
                    }
                    $img_src = $page_html->find('.slide', 0)->find('img', 0)->src;
                }

                $video = "";

                // If article already exits, no need to process rest of the articles
                if (!insertNew($link, $id, $title_text, $description_text, $body, $img_src, $video)){
                    break;
                } else{
                    $number_scraped++;
                }
            }
        }
    }
    echo "Scraped $number_scraped new article(s)";
}

// Reseting database
if (isset($_POST['reset'])){
    $link = mysqli_connect("127.0.0.1", "testdb_admin", "majesticeagle", "testdb");
    $query = "DELETE FROM Articles";
    $results = mysqli_query($link, $query);
    if ($results){
        echo "Cleared database";
    }
}

// Inserts article into db and returns true if new
function insertNew($link, $id, $title, $desc, $body, $img, $video){

    // Escape chars
    $title = mysqli_real_escape_string($link, $title);
    $desc = mysqli_real_escape_string($link, $desc);
    $body = mysqli_real_escape_string($link, $body);

    $query = "INSERT INTO Articles(articleId, title, description, body, image, video) VALUES ('$id','$title','$desc','$body','$img','$video')";

    $results = mysqli_query($link, $query);
    return $results;
}



?>

