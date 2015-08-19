<?php
    require_once(__DIR__ . '/../vendor/autoload.php');

    $craigslist = new \Divinityfound\CraigslistApi\Reader;

    echo '<pre>';
    print_r($craigslist->getSearchResults('omaha','cpg'));
    echo '</pre>';
    exit;
?>