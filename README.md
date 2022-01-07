# Imagine

🖼 Imagine (image-engine) is a PHP micro library to resize, thumbnail or apply filters to your images

## Installation

```console
composer require imagine/imagine
```

## Usage

```php
use Imagine\Imagine;

try {
  $image = new Imagine($_FILES['image']['tmp_name']);
  $image->setWidth(200);
  $image->setHeight(290);

  if ($image->save('./uploads/')) {
    // true
  } else {
    // false
  }
} catch (Exception $e) {
  echo 'Exception reçue : ',  $e->getMessage(), "\n";
}
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
$image->setDPI(72);
$image->setQuality(90);
$image->setFit('cover');
$image->setPosition('center', 'center');
$image->setBackground('currentColor');
$image->addFilter('grayscale');
$image->setIsOverride(false);

// Getter
$image->getSrcWidth();
$image->getSrcHeight();
$image->getSrcMime();
$image->getSrcType();
$image->getSrcDPI();
$image->getDistWidth();
$image->getDistHeight();
$image->getDistType();
$image->getDistDPI();
$image->getQuality();
$image->getFit();
$image->getPosition();
$image->getBackground();
$image->getBackgroundToHexa();
$image->getFilters();
$image->getIsOverride();
$image->getDestination();

// Save file
$image->save('./uploads/');

// Or render in browser
header('Content-type:image/png');
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
$image->setFit('contain');
$image->setBackground('transparent');
$image->save('./doc/img/example-06.png');
```

![example 06](/doc/img/example-06.png)

### Background color main color

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->setFit('contain');
$image->setBackground('currentColor');
$image->save('./doc/img/example-07.jpg');
```

![example 07](/doc/img/example-07.jpg)

### Background color with array

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setHeight(300);
$image->setFit('contain');
$image->setBackground(array(
  'r' => 255,
  'g' => 0,
  'b' => 0,
  'a' => 1,
));
$image->setType('jpg');
$image->save('./doc/img/example-08.jpg');
```

![example 08](/doc/img/example-08.jpg)

### Background color with hexa

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setHeight(300);
$image->setFit('contain');
$image->setBackground('#ffaaff');
$image->setType('jpg');
$image->save('./doc/img/example-09.jpg');
```

![example 09](/doc/img/example-09.jpg)

### Image position in thumbnail

```php
$image = new Imagine('./tests/assets/file-valid.jpg');
$image->setWidth(300);
$image->setHeight(300);
$image->setFit('contain');
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

### Convert MIME file

```php
$image = new Imagine('./tests/assets/file-transparent.png');
$image->setWidth(300);
$image->setType('jpg'); // 'jpg'|'png'
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
