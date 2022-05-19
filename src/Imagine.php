<?php

/**
 * PHP micro library to resize, thumbnail or apply filters to your images
 *
 * @package Imagine\Imagine
 **/

declare(strict_types=1);

namespace Imagine;

class Imagine
{
    private $src = null;
    private $srcPath = '';
    private $srcWidth = 0;
    private $srcHeight = 0;
    private $srcMime = '';
    private $srcType = '';
    private $srcDPI = array('x' => 0, 'y' => 0);
    private $dist = null;
    private $distWidth = 0;
    private $distHeight = 0;
    private $distMime = '';
    private $distType = '';
    private $distDPI = array('x' => 0, 'y' => 0);
    private $thumbWidth = 0;
    private $thumbHeight = 0;
    private $quality = 100;
    private $cropType = 'none';
    private $cropAuto = array(
        'mode' => IMG_CROP_SIDES,
        'threshold' => 0.5,
        'color' => -1,
    );
    private $cropSize = array(
        'x' => 0,
        'y' => 0,
        'width' => 0,
        'height' => 0,
    );
    private $fit = 'contain';
    private $position = array('x' => 'center', 'y' => 'center');
    private $filters = array();
    private $background = array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0);
    private $isInterlace = false;
    private $isOverride = true;
    private const TYPES_ALLOWED = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
    );
    private const CROPS_ALLOWED = array(
        'none',
        'auto',
        'manual',
    );
    private const FITS_ALLOWED = array(
        'stretch',
        'contain',
        'cover',
    );

    /**
     * Save the path of the image to be processed
     *
     * @param string $imgSrc Le chemin de l'image source
     * @return Imagine
     */
    public function __construct(string $imgSrc = '')
    {
        if (empty($imgSrc)) {
            throw new \Exception('No image');
            return $this;
        }

        if (!@\getimagesize($imgSrc)) {
            throw new \Exception('Image Corrupted');
            return $this;
        }

        $this->srcPath = $imgSrc;

        $this->setSrcMime();
        $this->setSrc();

        if (empty($this->src)) {
            throw new \Exception('There was a problem when generating the source file');
            return $this;
        }

        $this->setSrcSize();
        $this->setSrcType();
        $this->setSrcDPI();


        /*
         * By default the quality is 100%, but if we process a PNG file,
         * it will go through the 'imagepng()' function and the 100% quality makes
         * the destination image much heavier than the source image (up to 11 times the original file size).
         * So to avoid abuse, by default PNGs have a quality of 0% (which corresponds to a compression of '9')
         */
        if ($this->srcMime === 'image/png') {
            $this->setQuality(0);
        }

        return $this;
    }

    /**
     * Save the MIME of the source image
     *
     * @return boolean
     */
    private function setSrcMime(): bool
    {
        $info = \finfo_open(FILEINFO_MIME_TYPE);

        if (!$info) {
            throw new \Exception('There is a problem to get the information from the source image');
            return false;
        }

        $file = \finfo_file($info, $this->srcPath);

        if (!$file) {
            \finfo_close($info);
            throw new \Exception('There is a problem to get the information from the source image');
            return false;
        }

        $this->srcMime = $file;

        \finfo_close($info);

        return true;
    }

    /**
     * Save the source image as a GD resource
     *
     * @return boolean
     */
    private function setSrc(): bool
    {
        if ($this->srcMime === 'image/jpeg') {
            $this->src = \imagecreatefromjpeg($this->srcPath);
        } elseif ($this->srcMime === 'image/png') {
            $this->src = \imagecreatefrompng($this->srcPath);
        } elseif ($this->srcMime === 'image/gif') {
            $this->src = \imagecreatefromgif($this->srcPath);
        } elseif ($this->srcMime === 'image/webp') {
            $this->src = \imagecreatefromwebp($this->srcPath);
        } elseif ($this->srcMime === 'image/bmp') {
            $this->src = \imagecreatefrombmp($this->srcPath);
        }

        if (empty($this->src)) {
            throw new \Exception('There is a problem to create the GD resource from the source image');
            return false;
        }

        return true;
    }

    /**
     * Saves the size of the source image
     *
     * @return boolean
     */
    private function setSrcSize(): bool
    {
        $info = \getimagesize($this->srcPath);

        if (empty($info) || (!is_int($info[0]) || !is_int($info[1]))) {
            throw new \Exception('There is a problem to get the size of the source image');
            return false;
        }

        $this->srcWidth = (int) $info[0];
        $this->srcHeight = (int) $info[1];

        return true;
    }

    /**
     * Save the type of the source image
     *
     * @return boolean
     */
    private function setSrcType(): bool
    {
        if ($this->srcMime === 'image/jpeg') {
            $extension = \pathinfo($this->srcPath, PATHINFO_EXTENSION);

            if (!empty($extension) && $extension === 'jpeg') {
                $this->srcType = $extension; // It can be "jpeg"... Honestly
            } else {
                $this->srcType = 'jpg';
            }
        } elseif ($this->srcMime === 'image/png') {
            $this->srcType = 'png';
        } elseif ($this->srcMime === 'image/gif') {
            $this->srcType = 'gif';
        } elseif ($this->srcMime === 'image/webp') {
            $this->srcType = 'webp';
        } elseif ($this->srcMime === 'image/bmp') {
            $this->srcType = 'bmp';
        }

        if (empty($this->srcType)) {
            throw new \Exception('There is a problem to get the type of the source image');
            return false;
        }

        return true;
    }

    /**
     * Save the DPI of the source image
     *
     * @return boolean
     */
    private function setSrcDPI(): bool
    {
        $dpi = \imageresolution($this->src);

        if (empty($dpi)) {
            throw new \Exception('There was an error while searching for the resolution');
            return false;
        }

        $this->srcDPI = array(
            'x' => $dpi[0],
            'y' => $dpi[1],
        );

        return true;
    }

    /**
     * Saves the width of the destination image
     *
     * @param integer $width The width of the destination image
     * @return boolean
     */
    public function setWidth(int $width = 0): bool
    {
        $this->thumbWidth = $width <= 0 ? 0 : $width;

        return true;
    }

    /**
     * Returns the width of the source image
     *
     * @return integer
     */
    public function getSrcWidth(): int
    {
        return $this->srcWidth;
    }

    /**
     * Returns the width of the destination image
     *
     * @return integer
     */
    public function getDistWidth(): int
    {
        if ($this->distWidth) {
            return $this->distWidth;
        }

        $size = self::calculDistSizeFromThumbSize(
            $this->srcWidth,
            $this->srcHeight,
            $this->thumbWidth,
            $this->thumbHeight,
            $this->fit
        );

        return $size['thumbWidth'];
    }

    /**
     * Saves the height of the destination image
     *
     * @param integer $height The height of the destination image
     * @return boolean
     */
    public function setHeight(int $height = 0): bool
    {
        $this->thumbHeight = $height <= 0 ? 0 : $height;

        return true;
    }

    /**
     * Returns the height of the destination image
     *
     * @return integer
     */
    public function getSrcHeight(): int
    {
        return $this->srcHeight;
    }

    /**
     * Returns the height of the destination image
     *
     * @return integer
     */
    public function getDistHeight(): int
    {
        if ($this->distHeight) {
            return $this->distHeight;
        }

        $size = self::calculDistSizeFromThumbSize(
            $this->srcWidth,
            $this->srcHeight,
            $this->thumbWidth,
            $this->thumbHeight,
            $this->fit
        );

        return $size['thumbHeight'];
    }

    /**
     * Returns the MIME of the source image
     *
     * @return string
     */
    public function getSrcMime(): string
    {
        return $this->srcMime;
    }

    /**
     * Returns the MIME of the destination image
     *
     * @return string
     */
    public function getDistMime(): string
    {
        if (empty($this->distMime)) {
            return $this->srcMime;
        }

        return $this->distMime;
    }

    /**
     * Convert the type of the destination image
     *
     * @param string $type The type of the destination image (jpg|jpeg|png|gif|webp|bmp)
     * @return boolean
     */
    public function setType(string $type = ''): bool
    {
        if (!in_array($type, array_keys(self::TYPES_ALLOWED))) {
            return false;
        }

        $this->distType = $type;
        $this->distMime = self::TYPES_ALLOWED[$type];

        return true;
    }

    /**
     * Returns the type of the source image
     *
     * @return string
     */
    public function getSrcType(): string
    {
        return $this->srcType;
    }

    /**
     * Returns the type of the destination image
     *
     * @return string
     */
    public function getDistType(): string
    {
        if (empty($this->distType)) {
            return $this->srcType;
        }

        return $this->distType;
    }

    /**
     * Saves the DPI of the destination image
     *
     * @param integer $dpiX DPI in width
     * @param integer $dpiY DPI in height
     * @return boolean
     */
    public function setDPI(int $dpiX = 0, int $dpiY = 0): bool
    {
        $dpiX = ($dpiX <= 0) ? 72 : $dpiX;
        $dpiY = ($dpiY <= 0) ? $dpiX : $dpiY;

        $this->distDPI = array(
            'x' => $dpiX,
            'y' => $dpiY,
        );

        return true;
    }

    /**
     * Returns the DPI of the source image
     *
     * @return array
     */
    public function getSrcDPI(): array
    {
        return $this->srcDPI;
    }

    /**
     * Returns the DPI of the destination image
     *
     * @return array
     */
    public function getDistDPI(): array
    {
        if (empty($this->distDPI['x']) || empty($this->distDPI['y'])) {
            return $this->srcDPI;
        }

        return $this->distDPI;
    }

    /**
     * Saves the quality of the image
     *
     * @param integer $quality Quality applied in percentage
     * @return boolean
     */
    public function setQuality(int $quality = 100): bool
    {
        if ($quality < 0) {
            $quality = 0;
        } elseif ($quality > 100) {
            $quality = 100;
        }

        $this->quality = $quality;

        return true;
    }

    /**
     * Returns the quality of the destination image in percent
     *
     * @return integer
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * Crop the destination image by calculating the unused pixels
     *
     * @param integer $mode The crop mode from PHP
     * @param float $threshold
     *  The percentage of tolerance used when comparing the image color (only in IMG_CROP_THRESHOLD)
     * @param int $color The color used when comparing the crop (only in IMG_CROP_THRESHOLD)
     * @return bool
     */
    public function setCropAuto(int $mode = IMG_CROP_SIDES, float $threshold = 0.5, int $color = -1): bool
    {
        if ($mode === IMG_CROP_THRESHOLD && $color < 0) {
            throw new \Exception('In threshold mode, $color must be greater than or equal to 0');
            return false;
        }

        $this->cropType = 'auto';

        $this->cropAuto = array(
            'mode' => $mode,
            'threshold' => $threshold,
            'color' => $color,
        );

        return true;
    }

    /**
     * Returns the auto-crop settings for the destination image
     *
     * @return array
     */
    public function getCropAuto(): array
    {
        return $this->cropAuto;
    }

    /**
     * Crop the destination image by passing the position and size in pixels
     *
     * @param integer $x X Position
     * @param integer $y Y Position
     * @param integer $width The width
     * @param integer $height The height
     * @return boolean
     */
    public function setCropFromPixel(int $x = 0, int $y = 0, int $width = 0, int $height = 0): bool
    {
        $this->cropType = 'manual';

        $this->cropSize = array(
            'x' => $x < 0 ? 0 : $x,
            'y' => $y < 0 ? 0 : $y,
            'width' => $width < 0 ? 0 : $width,
            'height' => $height < 0 ? 0 : $height,
        );

        return true;
    }

    /**
     * Crop the destination image by passing the position and size in percent
     *
     * @param integer $x X Position
     * @param integer $y Y Position
     * @param integer $width The width
     * @param integer $height The height
     * @return boolean
     */
    public function setCropFromPercent(int $x = 0, int $y = 0, int $width = 0, int $height = 0): bool
    {
        $this->cropType = 'manual';

        $x = $x < 0 ? 0 : $x;
        $x = $x > 100 ? 100 : $x;
        $y = $y < 0 ? 0 : $y;
        $y = $y > 100 ? 100 : $y;
        $width = $width < 0 ? 0 : $width;
        $width = $width > 100 ? 100 : $width;
        $height = $height < 0 ? 0 : $height;
        $height = $height > 100 ? 100 : $height;

        $this->cropSize = array(
            'x' => ($x / 100) * $this->srcWidth,
            'y' => ($y / 100) * $this->srcHeight,
            'width' => ($width / 100) * $this->srcWidth,
            'height' => ($height / 100) * $this->srcHeight,
        );

        return true;
    }

    /**
     * Returns the way the destination image will be cropped.
     *
     * @return string
     */
    public function getCropType(): string
    {
        return $this->cropType;
    }

    /**
     * Returns the position and size of the cropping of the destination image
     *
     * @return array
     */
    public function getCropSize(): array
    {
        return $this->cropSize;
    }

    /**
     * Saves the way to stretch the destination image in the thumbnail
     *
     * @param string $fit The way to stretch the image (stretch|contain|cover)
     * @return boolean
     */
    public function setFit(string $fit = ''): bool
    {
        if (!in_array($fit, self::FITS_ALLOWED)) {
            return false;
        }

        $this->fit = $fit;

        return true;
    }

    /**
     * Returns the way to stretch the destination image
     *
     * @return string
     */
    public function getFit(): string
    {
        return $this->fit;
    }

    /**
     * Saves the position of the destination image in the thumbnail
     *
     * @param string $x The horizontal position (left|center|right)
     * @param string $y The vertical position (top|center|bottom)
     * @return boolean
     */
    public function setPosition(string $x = 'center', string $y = 'center'): bool
    {
        $xAllowed = array('left', 'center', 'right');
        $yAllowed = array('top', 'center', 'bottom');

        if (!in_array($x, $xAllowed) || !in_array($y, $yAllowed)) {
            return false;
        }

        $this->position = array(
            'x' => $x,
            'y' => $y,
        );

        return true;
    }

    /**
     * Returns the position of the destination image in the thumbnail
     *
     * @return array
     */
    public function getPosition(): array
    {
        return $this->position;
    }

    /**
     * Saves the background color of the destination image with "rgba" values
     *
     * @param integer $r Red
     * @param integer $g Green
     * @param integer $b Blue
     * @param float $a Alpha
     * @return boolean
     */
    public function setBackgroundFromRGBA(int $r = 255, int $g = 255, int $b = 255, float $a = 0): bool
    {
        $this->background['r'] = $r >= 0 && $r <= 255 ? $r : 255;
        $this->background['g'] = $g >= 0 && $g <= 255 ? $g : 255;
        $this->background['b'] = $b >= 0 && $b <= 255 ? $b : 255;
        $this->background['a'] = $a >= 0 && $a <= 1 ? $a : 0;

        return true;
    }

    /**
     * Saves the background color of the destination image with a hexadecimal code
     *
     * @param string $background Hexadecimal code
     * @return boolean
     */
    public function setBackgroundFromHexa(string $background = ''): bool
    {
        if (!is_string($background)) {
            return false;
        }

        $this->background = self::getHexaToRGBA($background);

        return true;
    }

    /**
     * Saves the background color of the destination image as transparent
     *
     * @return boolean
     */
    public function setBackgroundTransparent(): bool
    {
        $this->background = array(
            'r' => 255,
            'g' => 255,
            'b' => 255,
            'a' => 0,
        );

        return true;
    }

    /**
     * Saves the background color of the destination image with the main color
     *
     * @return boolean
     */
    public function setBackgroundMainColor(): bool
    {
        try {
            if (empty($this->src)) {
                throw new \Exception('There was an error when filling in the background color');
                return false;
            }

            $thumb = @\imagecreatetruecolor(1, 1);
            @\imagecopyresampled($thumb, $this->src, 0, 0, 0, 0, 1, 1, $this->srcWidth, $this->srcHeight);

            $index = @\imagecolorat($thumb, 0, 0);

            if ($index === false) {
                @\imagedestroy($thumb);
                throw new \Exception('There was an error when filling in the background color');
                return false;
            }

            $mainColor = @strtolower(@dechex($index));

            if (empty($mainColor)) {
                @\imagedestroy($thumb);
                throw new \Exception('There was an error when filling in the background color');
                return false;
            }

            $mainColor = '#' . (string) $mainColor;

            @\imagedestroy($thumb);

            $this->background = self::getHexaToRGBA($mainColor);

            return true;
        } catch (\Exception $error) {
            return false;
        }

        return false;
    }

    /**
     * Returns the background color of the destination image as an "rgba" array
     *
     * @return array
     */
    public function getBackground(): array
    {
        return $this->background;
    }

    /**
     * Returns the background color of the destination image as a hexadecimal code
     *
     * @return string
     */
    public function getBackgroundToHexa(): string
    {
        return str_pad(dechex($this->background['r']), 2, '0', STR_PAD_LEFT) .
            str_pad(dechex($this->background['g']), 2, '0', STR_PAD_LEFT) .
            str_pad(dechex($this->background['b']), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Adds a GD filter to the destination image
     *
     * @param integer $filterConstant The GD filter constant (https://www.php.net/manual/fr/function.imagefilter.php)
     * @param mixed $params Filter parameters
     * @return boolean
     */
    public function addFilter(int $filterConstant = -1, mixed $params = null): bool
    {
        if (is_int($filterConstant) && $filterConstant === -1) {
            return false;
        }

        $filter = array(
            'type' => $filterConstant,
        );

        if (!is_null($params)) {
            $filter['params'] = $params;
        }

        $this->filters[] = $filter;

        return true;
    }

    /**
     * Returns the list of filters applied to the destination image
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Saves if the destination image is to be displayed gradually (only jpg)
     *
     * @param boolean $isInterlace If the destination image is to be displayed gradually
     * @return boolean
     */
    public function setIsInterlace(bool $isInterlace = true): bool
    {
        $this->isInterlace = $isInterlace;

        return true;
    }

    /**
     * Returns if the destination image is to be displayed gradually
     *
     * @return boolean
     */
    public function getIsInterlace(): bool
    {
        return $this->isInterlace;
    }

    /**
     * Saves whether to override the destination image or not
     *
     * @param boolean $isOverride Override or not the destination image
     * @return boolean
     */
    public function setIsOverride(bool $isOverride = true): bool
    {
        $this->isOverride = $isOverride;

        return true;
    }

    /**
     * Returns whether or not to override the destination image
     *
     * @return boolean
     */
    public function getIsOverride(): bool
    {
        return $this->isOverride;
    }

    /**
     * Apply the settings to the destination image and save it to a file
     *
     * @param string $destination The path of the destination file
     * @param boolean $destroySrcGD Destroyed from the GD resource of the source image when finished
     * @param boolean $destroyDistGD Destroyed from the GD resource of the destination image when finished
     * @return boolean
     */
    public function save(string $destination = '', bool $destroySrcGD = true, bool $destroyDistGD = true): bool
    {
        if (empty($destination)) {
            throw new \Exception('The destination path does not exist');
            return false;
        }

        if (!is_dir(dirname($destination))) {
            throw new \Exception('The destination path does not exist');
            return false;
        }

        if (file_exists($destination) && !$this->isOverride) {
            throw new \Exception('File rewriting is disabled');
            return false;
        }

        return $this->render($destination, $destroySrcGD, $destroyDistGD);
    }

    /**
     * Applies the settings to the destination image and displays it in the browser
     *
     * @param boolean $destroySrcGD Destroyed from the GD resource of the source image when finished
     * @param boolean $destroyDistGD Destroyed from the GD resource of the destination image when finished
     * @return boolean
     */
    public function displayOnBrowser(bool $destroySrcGD = true, bool $destroyDistGD = true): bool
    {
        return $this->render(null, $destroySrcGD, $destroyDistGD);
    }

    /**
     * Apply the settings to the destination image
     *
     * @param string|null $destination The path of the destination file
     * @param boolean $destroySrcGD Destroyed from the GD resource of the source image when finished
     * @param boolean $destroyDistGD Destroyed from the GD resource of the destination image when finished
     * @return boolean
     */
    private function render($destination = '', bool $destroySrcGD = true, bool $destroyDistGD = true): bool
    {
        if ($this->cropType === 'none') {
            $src = &$this->src;
        } else {
            $src = $this->src;
        }

        $srcWidth = $this->srcWidth;
        $srcHeight = $this->srcHeight;

        if ($this->cropType === 'auto') {
            $srcCropped = imagecropauto(
                $src,
                $this->cropAuto['mode'],
                $this->cropAuto['threshold'],
                $this->cropAuto['color']
            );

            if (!$srcCropped) {
                imagedestroy($src);
                unset($src);
                $this->destroyTempImg($destroySrcGD, false);
                throw new \Exception('There was a problem while cropping the image');
                return false;
            }

            $src = &$srcCropped;
            $srcWidth = (int) imagesx($src);
            $srcHeight = (int) imagesy($src);
        } elseif ($this->cropType === 'manual') {
            $srcCropped = imagecrop($src, $this->cropSize);

            if (!$srcCropped) {
                imagedestroy($src);
                unset($src);
                $this->destroyTempImg($destroySrcGD, false);
                throw new \Exception('There was a problem while cropping the image');
                return false;
            }

            $src = &$srcCropped;
            $srcWidth = (int) imagesx($src);
            $srcHeight = (int) imagesy($src);
        }

        $outerSize = self::calculDistSizeFromThumbSize(
            $srcWidth,
            $srcHeight,
            $this->thumbWidth,
            $this->thumbHeight,
            $this->fit
        );

        $this->thumbWidth = $outerSize['thumbWidth'];
        $this->thumbHeight = $outerSize['thumbHeight'];
        $this->distWidth = $outerSize['distWidth'];
        $this->distHeight = $outerSize['distHeight'];

        $this->dist = \imagecreatetruecolor($this->thumbWidth, $this->thumbHeight);

        if (empty($this->dist)) {
            if ($this->cropType !== 'none') {
                @imagedestroy($src);
                unset($src);
            }
            $this->destroyTempImg($destroySrcGD, false);
            throw new \Exception('There is a problem when processing the destination file');
            return false;
        }

        if (empty($this->distDPI['x']) || empty($this->distDPI['y'])) {
            $this->setDPI($this->srcDPI['x'], $this->srcDPI['y']);
        }

        // Set the final extension if there is no conversion done on the file
        if (empty($this->distType)) {
            $this->setType($this->srcType);
        }

        $dpi = \imageresolution($this->dist, $this->distDPI['x'], $this->distDPI['y']);

        if (empty($dpi)) {
            if ($this->cropType !== 'none') {
                @imagedestroy($src);
                unset($src);
            }
            $this->destroyTempImg($destroySrcGD, $destroyDistGD);
            throw new \Exception('There is a problem when processing the destination file');
            return false;
        }

        // If destination image is "jpg", force no transparent background
        if ($this->distMime === 'image/jpeg' || $this->distMime === 'image/bmp') {
            $this->background['a'] = 1;
        }

        // If the transparent background is set, then apply it to the destination image
        if ($this->background['a'] < 1) {
            \imagealphablending($this->dist, false);
            $alpha = (1 - $this->background['a']) * 127;
            $transparency = \imagecolorallocatealpha(
                $this->dist,
                $this->background['r'],
                $this->background['g'],
                $this->background['b'],
                $alpha
            );

            \imagefill($this->dist, 0, 0, $transparency);
            \imagesavealpha($this->dist, true);
        } else {
            $bgColor = \imagecolorallocate(
                $this->dist,
                $this->background['r'],
                $this->background['g'],
                $this->background['b']
            );
            \imagefilledrectangle($this->dist, 0, 0, $this->thumbWidth, $this->thumbHeight, $bgColor);
            unset($bgColor);
        }

        // Copy the source image to the destination image
        $positionX = 0;
        $positionY = 0;

        // We do the calculation only if we are in "contain" or "cover".
        if ($this->fit === 'contain' || $this->fit === 'cover') {
            // X calcul
            if ($this->position['x'] === 'left') {
                $positionX = 0;
            } elseif ($this->position['x'] === 'center') {
                $positionX = (int) (($this->thumbWidth - $this->distWidth) / 2);
            } elseif ($this->position['x'] === 'right') {
                $positionX = (int) ($this->thumbWidth - (int) $this->distWidth);
            }

            // Y Calcul
            if ($this->position['y'] === 'top') {
                $positionY = 0;
            } elseif ($this->position['y'] === 'center') {
                $positionY = (int) (($this->thumbHeight - $this->distHeight) / 2);
            } elseif ($this->position['y'] === 'bottom') {
                $positionY = (int) ($this->thumbHeight - (int) $this->distHeight);
            }
        }

        $isSampled = \imagecopyresampled(
            $this->dist,
            $src,
            $positionX,
            $positionY,
            0,
            0,
            $this->distWidth,
            $this->distHeight,
            $srcWidth,
            $srcHeight
        );

        if ($this->cropType !== 'none') {
            @imagedestroy($src);
            unset($src);
        }

        if (!$isSampled) {
            $this->destroyTempImg($destroySrcGD, $destroyDistGD);
            throw new \Exception('Can\'t create the temp destination image');
            return false;
        }

        // Apply the filters
        foreach ($this->filters as $filter) {
            $check = true;

            if (isset($filter['params'])) {
                $check = \imagefilter($this->dist, $filter['type'], $filter['params']);
            } else {
                $check = \imagefilter($this->dist, $filter['type']);
            }

            if (!$check) {
                $this->destroyTempImg($destroySrcGD, $destroyDistGD);
                throw new \Exception('Can\'t apply filter ' . $filter['type']);
                return false;
            }
        }

        if ($this->distMime === 'image/jpeg' && $this->isInterlace) {
            $isInterlace = \imageinterlace($this->dist, true);

            if (!$isInterlace) {
                $this->destroyTempImg($destroySrcGD, $destroyDistGD);
                throw new \Exception('There was a problem to interlace');
                return false;
            }
        }

        $isCreate = false;

        if ($this->distMime === 'image/jpeg') {
            $isCreate = \imagejpeg($this->dist, $destination, $this->quality);
        } elseif ($this->distType === 'png') {
            $isCreate = \imagepng($this->dist, $destination, (int) (((-$this->quality + 100) * 9) / 100));
        } elseif ($this->distType === 'gif') {
            $isCreate = \imagegif($this->dist, $destination);
        } elseif ($this->distType === 'webp') {
            $isCreate = \imagewebp($this->dist, $destination, $this->quality);
        } elseif ($this->distType === 'bmp') {
            $isCreate = \imagebmp($this->dist, $destination, $this->quality < 100);
        } else {
            $this->destroyTempImg($destroySrcGD, $destroyDistGD);
            return false;
        }

        $this->destroyTempImg($destroySrcGD, $destroyDistGD);

        if (!$isCreate) {
            throw new \Exception('Can\'t create destination image');
            return false;
        }

        return true;
    }

    /**
     * Returns the size of the destination image based on the size of the source image and the size of the thumbnail
     *
     * @param int $srcWidth The width of the source image
     * @param int $srcHeight The height of the source image
     * @param int $thumbWidth The width of the thumbnail
     * @param int $thumbHeight The height of the thumbnail
     * @param string $fit The way to stretch the image (stretch|contain|cover)
     * @return array
     */
    private static function calculDistSizeFromThumbSize(
        int $srcWidth = 0,
        int $srcHeight = 0,
        int $thumbWidth = 0,
        int $thumbHeight = 0,
        string $fit = ''
    ): array {
        $distWidth = 0;
        $distHeight = 0;

        // On vérifit si c'est un redimmenssionnement
        if ($thumbWidth > 0 xor $thumbHeight > 0) {
            // On enregistre la taille une fois redimmenssionné
            if ($thumbWidth) {
                $thumbHeight = $srcHeight * ($thumbWidth / $srcWidth);
            } else {
                $thumbWidth = $srcWidth * ($thumbHeight / $srcHeight);
            }

            $distWidth = $thumbWidth;
            $distHeight = $thumbHeight;
        } elseif ($thumbWidth > 0 && $thumbHeight > 0) {
            // On vérifit si c'est une vignette
            if ($fit === 'stretch') {
                $distWidth = $thumbWidth;
                $distHeight = $thumbHeight;
            } elseif ($fit === 'contain' || $fit === 'cover') {
                $isArrayContain = array(
                    $thumbWidth > $thumbHeight &&
                    $srcWidth > $srcHeight &&
                    ($srcWidth * $thumbHeight) / $srcHeight < $thumbWidth,
                    $thumbWidth > $thumbHeight &&
                    $srcWidth <= $srcHeight,
                    $thumbWidth <= $thumbHeight &&
                    $srcWidth < $srcHeight &&
                    ($srcWidth * $thumbHeight) / $srcHeight < $thumbWidth,
                    $thumbWidth === $thumbHeight &&
                    $srcWidth === $srcHeight,
                );

                $isArrayCover = array(
                    $thumbWidth > $thumbHeight &&
                    $srcWidth > $srcHeight &&
                    ($srcWidth * $thumbHeight) / $srcHeight > $thumbWidth,
                    $thumbWidth <= $thumbHeight &&
                    $srcWidth > $srcHeight,
                    $thumbWidth < $thumbHeight &&
                    $srcWidth <= $srcHeight &&
                    ($srcWidth * $thumbHeight) / $srcHeight > $thumbWidth,
                    $thumbWidth === $thumbHeight &&
                    $srcWidth === $srcHeight,
                );

                // Si c'est le cas 1
                if (
                    (in_array(true, $isArrayContain) && $fit === 'contain') ||
                    (in_array(true, $isArrayCover) && $fit === 'cover')
                ) {
                    // On redimmensionne 'img' d'abord par sa largeur
                    $distWidth = ($srcWidth * $thumbHeight) / $srcHeight;
                    $distHeight = ($srcHeight * $distWidth) / $srcWidth;
                } else {
                    // Si c'est le cas 2
                    // On redimmensionne 'img' d'abord par sa hauteur
                    $distHeight = ($srcHeight * $thumbWidth) / $srcWidth;
                    $distWidth = ($srcWidth * $distHeight) / $srcHeight;
                }
            }
        } else {
            // Sinon on donne les même dimension que l'image original
            $distWidth = $thumbWidth = $srcWidth;
            $distHeight = $thumbHeight = $srcHeight;
        }

        return array(
            'thumbWidth' => (int) round($thumbWidth),
            'thumbHeight' => (int) round($thumbHeight),
            'distWidth' => (int) round($distWidth),
            'distHeight' => (int) round($distHeight),
        );
    }

    /**
     * Returns the hexadecimal code as an array "rgba"
     *
     * @param string $hexa Hexadecimal code
     * @return array
     */
    private static function getHexaToRGBA(string $hexa = ''): array
    {
        $bg = array(
            'r' => 255,
            'g' => 255,
            'b' => 255,
            'a' => 1,
        );

        if (empty($hexa)) {
            return $bg;
        }

        $hexa = str_replace('#', '', $hexa);
        $hexa = mb_strlen($hexa) === 3 ? $hexa . $hexa : $hexa;

        $bg['r'] = strlen($hexa) === 6 ? hexdec(substr($hexa, 0, 2)) : $bg['r'];
        $bg['g'] = strlen($hexa) === 6 ? hexdec(substr($hexa, 2, 2)) : $bg['g'];
        $bg['b'] = strlen($hexa) === 6 ? hexdec(substr($hexa, 4, 2)) : $bg['b'];

        return $bg;
    }

    /**
     * Destroyed GD resources
     *
     * @param boolean $isSrcMustDestroy Destroyed from the GD resource of the source image
     * @param boolean $isDistMustDestroy Destroyed from the GD resource of the destination image
     * @return boolean
     */
    private function destroyTempImg(bool $isSrcMustDestroy = true, bool $isDistMustDestroy = true): bool
    {
        if ($isSrcMustDestroy) {
            @\imagedestroy($this->src);
            $this->src = null;
        }

        if ($isDistMustDestroy) {
            @\imagedestroy($this->dist);
            $this->dist = null;
        }

        return !$isSrcMustDestroy && !$isDistMustDestroy ? false : true;
    }
}
