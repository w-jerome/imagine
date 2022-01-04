<?php
require __DIR__ . '/src/imagine.php';

use Imagine\Imagine;

$image = new Imagine('./tests/assets/example-valid.jpg');
// $image->setName('thumbnail_500-500');
$image->setWidth(500);
$image->setHeight(500);
$image->setFit('contain');
$image->setPosition('center', 'center');
$image->setBackground('currentColor');
$image->setDestination(null);

header('Content-type:image/jpeg');

$image->render();
