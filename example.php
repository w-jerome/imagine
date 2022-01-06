<?php
require __DIR__ . '/src/imagine.php';

use Imagine\Imagine;

$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(500);
$image->setHeight(500);
$image->setFit('contain');
$image->setPosition('center', 'center');
$image->setBackground('currentColor');

// echo '<pre>';
// var_dump($image);
// echo '</pre>';

header('Content-type:image/jpeg');
$image->displayOnBrowser();
// $image->save('./example.jpg');
