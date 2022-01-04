# Imagine

🖼 Imagine (image-engine) is a PHP micro library to resize, thumbnail or apply filters to your images

## Installation

```console
composer require imagine/imagine
```

## Usage

```php
use Imagine\Imagine;

$image = new Imagine($_FILES['image']['tmp_name']); // string: path of image
$image->setName('thumbnail_200-290');               // string: name of image
$image->setWidth(200);                              // int: width of new image
$image->setHeight(290);                             // int: height of new image
$image->setQuality(90);                             // int: quality in percent of new image
$image->setFit('cover');                            // string: ('stretch', 'cover', 'contain') how the new image is processed for a thumbnail
$image->setPosition('center', 'center');            // string: ('left', 'top') ('center', 'bottom') ('right', 'center') ('right', 'top') where the new image is placed
$image->setBackground('currentColor');              // string/array: ('ffffff', '#000000', 'faa', 'currentColor', array("r" => 255, "g" => 255, "b" => 255, "a" => 1)) the background color of the image, "currentColor" uses the main color of the image, The array manage the alpha channel
$image->addFilter('grayscale');                     // string: ('negate', 'grayscale', 'edgedetect', 'emboss', 'mean_removal', 'blur') apply filtres to new image
$image->setType('png');                             // string: ('jpg', 'png') convert new image to new extension
$image->setOverride(false);                         // bolean: override or not destination file
$image->setDestination('./uploads/');               // string/null: the path of new image, null return the raw image stream directly
$image->setDebug(false);                            // boolean: return php error

$imageName = $image->render(); // make new image and return the new image name with extension or return false

if ($imageName) {
  var_dump($imageName); // "thumbnail_200-290.png"
} else {
  var_dump($imageName); // false
}
```

### Functions

```php
$image = new Imagine('./my-picture.jpg');
$image->setWidth(400);
$image->setBackground('currentColor');

$image->getName();
$image->getSrcWidth();
$image->getSrcHeight();
$image->getSrcType();
$image->getDistWidth();
$image->getDistHeight();
$image->getDistType();
$image->getQuality();
$image->getFit();
$image->getPosition();
$image->getFilters();
$image->getBackground();
$image->getBackgroundToHexa();
$image->getOverride();
$image->getDestination();
$image->getDebug();
```
