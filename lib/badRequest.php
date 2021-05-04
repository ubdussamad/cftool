<?php
function badRequestHandler() {
    header("HTTP/1.1 400 Bad Request");
    echo "<h1>Bad input, Please go back!</h1>";
    echo "<hr><footer> Apache/2.4.46 (Ubuntu) </footer>";
    die();
}
?>