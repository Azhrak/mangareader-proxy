<?php
define('MANGAURL', 'http://www.mangareader.net/');
define('WWWROOT', 'http://'.dirname($_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']).'/');
define('THISURL', 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

$manga_id = (isset($_GET['id'])) ? $_GET['id'] : die('Invalid manga id.');
$chapter = (isset($_GET['c']) && is_numeric($_GET['c'])) ? (int)$_GET['c'] : 1;
$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;

$bookmark = array();
if (isset($_COOKIE['bookmark']) && !empty($_COOKIE['bookmark'])) {
    $bookmark = explode(';', $_COOKIE['bookmark']);
}

if (isset($_GET['bookmark'])) {
    if (!setcookie('bookmark', $manga_id.';'.$chapter.';'.$page, time()+60*60*24*120, dirname($_SERVER['SCRIPT_NAME']).'/')) {
        die('Cannot set cookie.');
    }
    header('Location: '.WWWROOT . $manga_id .'/'. $chapter .'/'. $page);
    exit();
}

$html = file_get_contents(MANGAURL . $manga_id .'/'. $chapter .'/'. $page);

$img_url = '';
if (preg_match('/id="img" [^>]*?src="([^"]+)"/', $html, $match)) {
    $img_url = $match[1];
}
else {
    die('Image not found.');
}

if (preg_match('/of ([\d]+)<\/div>/', $html, $match)) {
    $last_page = $match[1];
    if ($last_page <= $page) {
        $page = 0;
        $chapter++;
    }
}
$url_next_page = WWWROOT . $manga_id .'/'. $chapter .'/'. ($page+1) . '/';

$title = ucfirst($manga_id);
if (!empty($chapter)) $title .= ' - Chapter '.$chapter;
if (!empty($page)) $title .= ' - Page '.$page;

?>
<!DOCTYPE html>
<html lang="en" xml:lang="en" xmlns:og="http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml">
<head>

    <title>Manga: <?php echo $title ?></title>
    
    <style type="text/css">
    body, html {
        margin: 0px;
        padding: 0px;
        background-color: black;
        color: #EEE;
        font-family: verdana;
        font-size: 20px;
    }
    #save {
        color: rgb(72, 219, 51);
    }
    #goto_save {
        color: rgb(212, 219, 51)
    }
    img {
        width: 100%;
        border: none;
    }
    #title {
        font-weight: bold;
        margin: 0.5em;
    }
    #bookmark {
        float: right;
    }
    #bookmark a {
        margin: 0 1em;
    }
    </style>
</head>
<body>

    <div id="bookmark">
    <?php if (!empty($bookmark)) : ?>
        <a id="goto_save" href="<?php echo WWWROOT . $bookmark[0] .'/'. $bookmark[1] .'/'. $bookmark[2] ?>">Load Save</a> |
    <?php endif; ?>
        <a id="save" href="<?php echo (substr(THISURL,-1)=='/') ? THISURL : THISURL.'/'?>bookmark">Save Page</a>
    </div>

    <div id="title"><?php echo $title?></div>

    <a href="<?php echo $url_next_page?>"><img src="<?php echo $img_url?>"></a>

</body>
</html>
