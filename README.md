# Imagine

🖼 Imagine (image-engine) is a PHP micro library to resize, thumbnail or apply filters to your images

## Installation

```console
composer require wjerome/imagine
```

## Usage

```php
require __DIR__ . '/vendor/autoload.php';

use Imagine\Imagine;

$image = new Imagine($_FILES['image']['tmp_name']); // string: path of image
$image->name('thumbnail_200-200');                  // string: name of image
$image->width(200);                                 // int: width of new image
$image->height(290);                                // int: height of new image
$image->quality(100);                               // int: quality in percent of new image
$image->processing('cover');                        // string: (stretch, cover, contain) how the new image is processed for a thumbnail
$image->position('center center');                  // string: (left top, center bottom, right center, top, right) where the new image is placed
$image->background('currentColor');                 // string/array: ('ffffff', '#000000', 'faa', 'currentColor', array("r" => 255, "g" => 255, "b" => 255, "a" => 1)) the background color of the image, "currentColor" uses the main color of the image, The array manage the alpha channel
$image->filter('grayscale');                        // string: (negate, grayscale, edgedetect, emboss, mean_removal, blur) apply filtres to new image
$image->convert('png');                             // string: (jpg, png) convert new image to new extension
$image->destination('./uploads/');                  // string/null: the path of new image, null return the raw image stream directly
$image->debug(true);                                // boolean: return php error

$imageName = $image->render(); // make new image and return the new image name with extension or return false

if ($imageName) {
  var_dump($imageName); // "thumbnail_200-200.png"
} else {
  var_dump($imageName); // false
}
```
