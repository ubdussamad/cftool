<!DOCTYPE html>
<!-- <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> -->
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8">
    <meta name="description" content="Community Finding Tool | SCIS JNU">
    <meta name="keywords" content="Community Finding Graph Biology BioInformatics">
    <meta name="author" content="ubdussamad">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">




    <title> Community Finding Tool - SCIS, JNU </title>

    <?php
    if ($EN_SANITY_JS){ 
        echo "\n<script type='text/javascript' src='sanity.js'></script>";
    }

    if ($EN_AUTO_SORT_JS) {
        echo "\n<script type='text/javascript' src='https://www.kryogenix.org/code/browser/sorttable/sorttable.js'></script>";
    }
    
    if ($EN_EXTERN_FONTS) {
        echo "\n <style> @import url('https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300&display=swap'); \n @import url('https://fonts.googleapis.com/css2?family=Whisper&display=swap'); </style>";
        echo "\n" . '<link rel="preconnect" href="https://fonts.gstatic.com">' . "\n";
        echo '<link href="https://fonts.googleapis.com/css2?family=Zilla+Slab&display=swap" rel="stylesheet">' . "\n";
    }

    if ($EN_INTERNAL_STYLE) {
        echo "\n<link rel='stylesheet' href='style.css'>";
    }
    ?>

</head>
