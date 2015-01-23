<?php

//Unsetting the session variables
$file = 'boxcarChildren.json';
if (file_exists($file)) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Length: ' . filesize($file));
    readfile($file);
}


