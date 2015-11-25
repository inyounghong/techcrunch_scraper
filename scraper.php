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
                // Parse article info
                $title = $li->find('h2', 0);
                $title_text = $title->plaintext;
                $url = $title->find('a', 0)->href;
                $description = getDescription($li);

                // Go to article page to get full body text
                $page_html = file_get_html($url);
                $article = $page_html->find('.article-entry', 0);
                $body = "";

                if ($article){
                    foreach ($article->find('p') as $p){
                        $p->find('.aside', 0)->innertext = "";
                        $p->find('.aside', 0)->outertext = "";
                        $body .= '<p>' . $p->plaintext . '</p>';
                    }
                    // Get img
                    $img_src = getImageSrc($page_html, $article);
                }
                else{
                    // Article is slideshow type
                    foreach ($page_html->find('.intro-text', 0)->find('p') as $p){
                        $body .= '<p>' . $p->plaintext . '</p>';
                    }
                    $img_src = $page_html->find('.slide', 0)->find('img', 0)->src;
                }

                $video = getVideo($article);

                // If article already exits, no need to process rest of the articles
                if (!insertNew($link, $id, $title_text, $description, $body, $img_src, $video)){
                    break;
                } else{
                    $number_scraped++;
                }
            }
        }
    }

    // Video articles
    $html = file_get_html("http://feeds.feedburner.com/techcrunch/podcast/crunch-report");
    $i = 0;

    // Loop through each video article
    foreach ($html->find('item') as $li){
        $title = $li->find('title', 0)->plaintext;
        $desc = $li->find('description', 0)->plaintext;
        $url = $li->find('enclosure');
        $video = $url[0]->url;
        $id_array = explode("_", end(explode("/", $video)));
        $id = $id_array[0];

        if ($i > 4 || !insertNew($link, $id, $title, $desc, "", "", $video) ){
            break;
        } else{
            $number_scraped++;
            $i++;
        }
    }

    echo "Scraped $number_scraped new article(s)";
    mysqli_close($link);
}

// Reseting database
if (isset($_POST['reset'])){
    $link = mysqli_connect("127.0.0.1", "testdb_admin", "majesticeagle", "testdb");

    // Run delete query
    $query = "DELETE FROM Articles";
    $results = mysqli_query($link, $query);
    if ($results){
        echo "Cleared database";
    }
}

function getDescription($li){
    $desc = $li->find('p.excerpt', 0);
    if ($desc){
        $desc->find('a', 0)->innertext = "";
        $desc->find('a', 0)->outertext = "";
        return $desc->plaintext;
    }
    return "None";
}

// Returns video for article
function getVideo($article){
    if (!$article) return "";
    $video = $article->find(".youtube-player", 0);
    if ($video){
        $videos = explode("?", $video->src);
        return $videos[0];
    }
    return "";
}

// Returns image src for article
function getImageSrc($page, $article){
    $hero_img = $page->find('.img-hero', 0);
    if ($hero_img){
        return $hero_img->src;
    }
    else {
        $img = $article->find('img', 0);
        if ($img){
            return $article->find('img', 0)->src;
        }
        return "";
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

