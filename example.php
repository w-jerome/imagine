<?php
require __DIR__ . '/vendor/autoload.php';

use Imagine\Imagine;

$image = new Imagine("./example.jpg");
$image->name("thumbnail_500-500");
$image->width(500);
$image->height(500);
$image->processing("contain");
$image->position("center center");
$image->background("currentColor");
$image->destination(null);
$image->debug(true);

header("Content-type:image/jpeg");

$image->render();
