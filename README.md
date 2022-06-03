# Imagine

🖼 Imagine (image-engine) is a PHP micro library to resize, thumbnail or apply filters to your images

## Required

- PHP >= 7.2
- GD

## File types supported

- jpg
- png
- gif
- webp
- bmp

## Installation

```console
composer require wjerome/imagine
```

## Usage

```php
use Imagine\Imagine;

try {
  $image = new Imagine($_FILES['image']['tmp_name']);
  $image->setWidth(200);
  $image->setHeight(290);
  $image->save('./uploads/my-image.jpg');
} catch (Exception $e) {
  echo 'Exception: ' . $e->getMessage();
}
```

```php
// Chaining methods
(new Imagine('./my-image.jpg'))
  ->setWidth(200)
  ->setHeight(200)
  ->setQuality(90)
  ->setFit('cover')
  ->save('./uploads/my-image.jpg');
```

### Functions

```php
// File upload
$image = new Imagine($_FILES['image']['tmp_name']);

// Or a file in a folder
$image = new Imagine('./my-picture.jpg');

// Setter
$image->setWidth(200);
$image->setHeight(290);
$image->setType('png');
$image->setDPI(96);
$image->setQuality(90);
$image->setCropAuto();
$image->setCropFromPixel(0, 0, 300, 300);
$image->setCropFromPercent(0, 0, 100, 100);
$image->setFit('cover');
$image->setPosition('left', 'top');
$image->setBackgroundFromRGBA(255, 255, 255, 1);
$image->setBackgroundFromHexa('#ffaaff');
$image->setBackgroundTransparent();
$image->setBackgroundMainColor();
$image->addFilter(IMG_FILTER_GRAYSCALE);
$image->setIsInterlace(true);
$image->setIsOverride(false);

// Getter
$image->getSrcWidth();
$image->getSrcHeight();
$image->getSrcMime();
$image->getSrcType();
$image->getSrcDPI();
$image->getDistWidth();
$image->getDistHeight();
$image->getDistMime();
$image->getDistType();
$image->getDistDPI();
$image->getQuality();
$image->getCropAuto();
$image->getCropType();
$image->getCropSize();
$image->getFit();
$image->getPosition();
$image->getBackground();
$image->getBackgroundFromHexa();
$image->getFilters();
$image->getIsInterlace();
$image->getIsOverride();

// Save file
$image->save('./uploads/my-image.jpg');

// Or render in browser
$image->displayOnBrowser();
```

## Examples

### Resize width

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->save('./doc/img/example-01.jpg');
```

![example 01](/doc/img/example-01.jpg)

### Resize height

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setHeight(300);
$image->save('./doc/img/example-02.jpg');
```

![example 02](/doc/img/example-02.jpg)

### Create thumbnail fit "stretch"

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->save('./doc/img/example-03.jpg');
```

![example 03](/doc/img/example-03.jpg)

### Create thumbnail fit "contain"

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->setFit('contain');
$image->save('./doc/img/example-04.jpg');
```

![example 04](/doc/img/example-04.jpg)

### Create thumbnail fit "cover"

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->setFit('cover');
$image->save('./doc/img/example-05.jpg');
```

![example 05](/doc/img/example-05.jpg)

### Background color transparent

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setHeight(300);
$image->setBackgroundTransparent();
$image->save('./doc/img/example-06.png');
```

![example 06](/doc/img/example-06.png)

### Background color main color

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->setBackgroundMainColor();
$image->save('./doc/img/example-07.jpg');
```

![example 07](/doc/img/example-07.jpg)

### Background color with array

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setHeight(300);
$image->setBackgroundFromRGBA(255, 0, 0, 1);
$image->setType('jpg');
$image->save('./doc/img/example-08.jpg');
```

![example 08](/doc/img/example-08.jpg)

### Background color with hexa

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setHeight(300);
$image->setBackgroundFromHexa('#ffaaff');
$image->setType('jpg');
$image->save('./doc/img/example-09.jpg');
```

![example 09](/doc/img/example-09.jpg)

### Image position in thumbnail

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->setPosition('left', 'top');
$image->save('./doc/img/example-10.jpg');
```

![example 10](/doc/img/example-10.jpg)

### Quality

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setQuality(50); // percent
$image->save('./doc/img/example-11.jpg');
```

![example 11](/doc/img/example-11.jpg)

#### Note: 1

In PNG the quality is not a percentage, it is a value between `0` and `9`.
`0` corresponds to no compression and `9` corresponds to the maximum compression. Here are the values to fill in `$image->setQuality()` :

- `setQuality(0)` : compression `9`
- `setQuality(1) -> setQuality(11)` : compression `8`
- `setQuality(12) -> setQuality(22)` : compression `7`
- `setQuality(23) -> setQuality(33)` : compression `6`
- `setQuality(34) -> setQuality(44)` : compression `5`
- `setQuality(45) -> setQuality(55)` : compression `4`
- `setQuality(56) -> setQuality(66)` : compression `3`
- `setQuality(67) -> setQuality(77)` : compression `2`
- `setQuality(78) -> setQuality(88)` : compression `1`
- `setQuality(89) -> setQuality(100)` : compression `0` (Be careful, the file size can be important)

[For more information](https://www.php.net/manual/en/function.imagepng.php)

#### Note: 2

By default the quality is 100%, but if we process a PNG file, it will go through the `imagepng()` function and the 100% quality makes the destination image much heavier than the source image (up to 11 times the original file size). So to avoid abuse, by default PNGs have a quality of 0% (which corresponds to a compression of `9`).

### Crop auto

Crop the destination image by calculating the unused pixels

```php
$image = new Imagine('./tests/assets/file-transparent-border.png');
$image->setBackgroundFromHexa('#ccc');
$image->setWidth(300);
$image->setCropAuto();
$image->save('./doc/img/example-16.jpg');
```

![example 16](/doc/img/example-16.jpg)

### Manual cropping (in pixel)

Crop the destination image by passing the position and size in pixels

```php
$image = new Imagine('./tests/assets/file-transparent-border.png');
$image->setBackgroundFromHexa('#ccc');
$image->setCropFromPixel(300, 150, 300, 150);
$image->save('./doc/img/example-17.jpg');
```

![example 17](/doc/img/example-17.jpg)

### Manual cropping (in percent)

Crop the destination image by passing the position and size in percent

```php
$image = new Imagine('./tests/assets/file-transparent-border.png');
$image->setBackgroundFromHexa('#ccc');
$image->setWidth(300);
$image->setCropFromPercent(25, 25, 50, 50);
$image->save('./doc/img/example-18.jpg');
```

![example 18](/doc/img/example-18.jpg)

### Convert MIME file

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setType('jpg'); // jpg|jpeg|png|gif|webp|bmp
$image->save('./doc/img/example-12.jpg');
```

![example 12](/doc/img/example-12.jpg)

### Add grayscale filter

[The list of available filters](https://www.php.net/manual/function.imagefilter.php)

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->addFilter(IMG_FILTER_GRAYSCALE);
$image->save('./doc/img/example-13.jpg');
```

![example 13](/doc/img/example-13.jpg)

### Add grayscale and blur filter

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->addFilter(IMG_FILTER_GRAYSCALE);
$image->addFilter(IMG_FILTER_GAUSSIAN_BLUR);
$image->addFilter(IMG_FILTER_GAUSSIAN_BLUR); // More blur
$image->addFilter(IMG_FILTER_GAUSSIAN_BLUR); // More more blur
$image->save('./doc/img/example-14.jpg');
```

![example 14](/doc/img/example-14.jpg)

### Display progressively the `jpg` and `jpeg` images

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setIsInterlace(true);
$image->save('./doc/img/example-15.jpg');
```

![example 15](/doc/img/example-15.jpg)

### Display on browser

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->displayOnBrowser();
```

### Create multiple images with a single resource

#### Reset settings each time a file is written

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->addFilter(IMG_FILTER_GRAYSCALE);
$image->setWidth(300);
$image->setHeight(300);
$image->setQuality(80);
$image->saveAndReset('./doc/img/example-16-1.jpg');
$image->setWidth(500);
$image->setFit('cover');
$image->saveAndReset('./doc/img/example-16-2.jpg');
$image->setWidth(1000);
$image->setQuality(100);
$image->save('./doc/img/example-16-3.jpg');
```

![example 16-1](/doc/img/example-16-1.jpg)
![example 16-2](/doc/img/example-16-2.jpg)
![example 16-3](/doc/img/example-16-3.jpg)

#### Reset settings each time a file is written

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->addFilter(IMG_FILTER_GRAYSCALE);
$image->setWidth(300);
$image->setHeight(300);
$image->setQuality(80);
$image->saveAndContinue('./doc/img/example-16-4.jpg');
$image->setWidth(500);
$image->setFit('cover');
$image->saveAndContinue('./doc/img/example-16-5.jpg');
$image->setWidth(1000);
$image->setQuality(100);
$image->save('./doc/img/example-16-6.jpg');
```

![example 16-4](/doc/img/example-16-4.jpg)
![example 16-5](/doc/img/example-16-5.jpg)
![example 16-6](/doc/img/example-16-6.jpg)