<?php
function badRequestHandler() {
    http_response_code(400);
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml">';
    echo '<title> Bad Request </title>';
    echo "<h1> Bad Request </h1>  <h4> Invalid POST data. </h4> <a href='" . $_SERVER['PHP_ROOT'] ."'> Back </a> <hr/>";
    $indicesServer = array('SERVER_ADDR','SERVER_SOFTWARE') ;
    echo $_SERVER[$indicesServer[1]]. " Server at " . $_SERVER[$indicesServer[0]];

    die();
}


