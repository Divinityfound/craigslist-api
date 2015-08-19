##Craigslist API
###Scrape against craigslist for anything you'd like!

Craigslist doesn't use an actual API so you have to scrape against it and parse the data accordingly. This tool will make it easier to aggregate a lot of data into a useable fasion.

###Code

```php

    require_once(__DIR__ . '/../vendor/autoload.php');

    $craigslist = new \Divinityfound\CraigslistApi\Reader;

    echo '<pre>';
    print_r($craigslist->getSearchResults('omaha','cpg'));
    echo '</pre>';
    exit;
?>
```