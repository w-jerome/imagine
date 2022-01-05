<?php

/**
 * PHP micro library to resize, thumbnail or apply filters to your images
 *
 * @package Imagine\Imagine
 **/

namespace Imagine;

class Imagine
{
    private $name = '';
    private $src = null;
    private $srcPath = '';
    private $srcWidth = 0;
    private $srcHeight = 0;
    private $srcMime = '';
    private $srcExtension = '';
    private $srcDPI = array(0, 0);
    private $dist = null;
    private $distPath = '';
    private $distWidth = 0;
    private $distHeight = 0;
    private $distExtension = '';
    private $distDPI = array(0, 0);
    private $distX = 0;
    private $distY = 0;
    private $thumbWidth = 0;
    private $thumbHeight = 0;
    private $quality = 100;
    private $fit = 'stretch';
    private $position = array('center', 'center');
    private $filters = array();
    private $background = array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 1);
    private $isOverride = true;
    private $isDebug = true;
    private const TYPES_ALLOWED = array(
        'jpg',
        'jpeg',
        'png',
        'gif',
    );
    private const FITS_ALLOWED = array(
        'stretch',
        'contain',
        'cover',
    );
    private const FILTERS_ALLOWED = array(
        'negate' => IMG_FILTER_NEGATE,
        'grayscale' => IMG_FILTER_GRAYSCALE,
        'edgedetect' => IMG_FILTER_EDGEDETECT,
        'emboss' => IMG_FILTER_EMBOSS,
        'mean_removal' => IMG_FILTER_MEAN_REMOVAL,
        'blur' => IMG_FILTER_GAUSSIAN_BLUR,
    );

    /**
     * Adds the file to be processed
     *
     * @param string $imgSrc Path of the file.
     *
     * @return Imagine
     */
    public function __construct(string $imgSrc = '')
    {
        if (empty($imgSrc)) {
            throw new \Exception('No image');
            return $this;
        }

        if (!@getimagesize($imgSrc)) {
            throw new \Exception('Image Corrupted');
            return $this;
        }

        $this->srcPath = $imgSrc;

        $this->setSrcMime();
        $this->setSrc();

        if (empty($this->src)) {
            if ($this->isDebug) {
                throw new \Exception('There was a problem when generating the source file');
            }
            return $this;
        }

        $this->setSrcSize();
        $this->setSrcExtension();
        $this->setSrcDPI();

        return $this;
    }

    /**
     * Set source image mime
     *
     * @return void
     */
    private function setSrcMime()
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $this->srcMime = finfo_file($finfo, $this->srcPath);

        finfo_close($finfo);
    }

    /**
     * Set source image
     *
     * @return void
     */
    private function setSrc()
    {
        if ($this->srcMime === 'image/jpeg') {
            $this->src = imagecreatefromjpeg($this->srcPath);
        } elseif ($this->srcMime === 'image/png') {
            $this->src = imagecreatefrompng($this->srcPath);
        } elseif ($this->srcMime === 'image/gif') {
            $this->src = imagecreatefromgif($this->srcPath);
        }
    }

    /**
     * Set source image width and height
     *
     * @return void
     */
    private function setSrcSize()
    {
        $info = getimagesize($this->srcPath);

        if (empty($info)) {
            if ($this->isDebug) {
                throw new \Exception('There was an error when filling in the image size');
            }
            return false;
        }

        $this->srcWidth = $info[0];
        $this->srcHeight = $info[1];
    }

    /**
     * Set source image extension
     *
     * @return void
     */
    private function setSrcExtension()
    {
        if ($this->srcMime === 'image/jpeg') {
            $info = @pathinfo($this->srcPath);

            if (!empty($info)) {
                $this->srcExtension = $info['extension']; // It can be "jpeg"... Honestly
            } else {
                $this->srcExtension = 'jpg';
            }
        } elseif ($this->srcMime === 'image/png') {
            $this->srcExtension = 'png';
        } elseif ($this->srcMime === 'image/gif') {
            $this->srcExtension = 'gif';
        }
    }

    /**
     * Set source image DPI
     *
     * @return boolean
     */
    private function setSrcDPI()
    {
        $dpi = @imageresolution($this->src);

        if (empty($dpi)) {
            if ($this->isDebug) {
                throw new \Exception('There was an error while searching for the resolution');
            }
        }

        $this->srcDPI = $dpi ?? array(72, 72);

        return true;
    }

    /**
     * Set filename
     *
     * @param string $name Destination filename
     *
     * @return boolean
     */
    public function setName(string $name = '')
    {
        if (empty($name)) {
            return false;
        }

        $this->name = $name;

        return true;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set width
     *
     * @param int $width Destination file width
     *
     * @return int
     */
    public function setWidth(int $width = 0)
    {
        if ($width <= 0) {
            $width = 0;
        }

        $this->thumbWidth = $width;

        return $this->thumbWidth;
    }

    /**
     * Get image source width
     *
     * @return int
     */
    public function getSrcWidth()
    {
        return $this->srcWidth;
    }

    /**
     * Get image destination width
     *
     * @return int
     */
    public function getDistWidth()
    {
        return $this->distWidth;
    }

    /**
     * Set height
     *
     * @param int $height Destination file height
     *
     * @return int
     */
    public function setHeight(int $height = 0)
    {
        if ($height <= 0) {
            $height = 0;
        }

        $this->thumbHeight = $height;

        return $this->thumbHeight;
    }

    /**
     * Get image source height
     *
     * @return int
     */
    public function getSrcHeight()
    {
        return $this->srcHeight;
    }

    /**
     * Get image destination height
     *
     * @return int
     */
    public function getDistHeight()
    {
        return $this->distHeight;
    }

    /**
     * Get source image mime
     *
     * @return string
     */
    public function getSrcMime()
    {
        return $this->srcMime;
    }

    /**
     * Set new file type
     *
     * @param string $extension Destination file type
     *
     * @return void
     */
    public function setExtension(string $extension = '')
    {
        if (!in_array($extension, self::TYPES_ALLOWED)) {
            return false;
        }

        $this->distExtension = $extension;

        return true;
    }

    /**
     * Get source file type
     *
     * @return string
     */
    public function getSrcExtension()
    {
        return $this->srcExtension;
    }

    /**
     * Get destination file type
     *
     * @return string
     */
    public function getDistExtension()
    {
        return $this->distExtension;
    }

    /**
     * Set destination file DPI
     *
     * @param int $dpiX Destination file DPI x
     * @param int $dpiY Destination file DPI y
     *
     * @return int
     */
    public function setDPI(int $dpiX = 72, int $dpiY = 72)
    {
        $dpiX = ($dpiX <= 0) ? 72 : $dpiX;
        $dpiY = ($dpiY <= 0) ? 72 : $dpiY;

        $this->distDPI = array($dpiX, $dpiY);

        return $this->distDPI;
    }

    /**
     * Get source file DPI
     *
     * @return array
     */
    public function getSrcDPI()
    {
        return $this->srcDPI;
    }

    /**
     * Get destination file DPI
     *
     * @return int
     */
    public function getDistDPI()
    {
        return $this->distDPI;
    }

    /**
     * Set quality
     *
     * @param int $quality Destination file quality in percent
     *
     * @return int
     */
    public function setQuality(int $quality = 100)
    {
        if ($quality < 0) {
            $quality = 0;
        } elseif ($quality > 100) {
            $quality = 100;
        }

        $this->quality = $quality;

        return $this->quality;
    }

    /**
     * Get quality
     *
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * If the destination image is a thumbnail, make the image fit, crop or restrict to the edge of the image
     *
     * @param string $fit Destination file fit type
     *
     * @return boolean
     */
    public function setFit(string $fit = '')
    {
        if (!in_array($fit, self::FITS_ALLOWED)) {
            return false;
        }

        $this->fit = $fit;

        return true;
    }

    /**
     * Get fit
     *
     * @return string
     */
    public function getFit()
    {
        return $this->fit;
    }

    /**
     * If the destination image is a thumbnail, choose where to display the image
     *
     * @param 'left'|'center'|'right' $x Destination file x position
     * @param 'top'|'center'|'bottom' $y Destination file y position
     *
     * @return boolean
     */
    public function setPosition(string $x = 'center', string $y = 'center')
    {
        $xAllowed = array('left', 'center', 'right');
        $yAllowed = array('top', 'center', 'bottom');

        if (!in_array($x, $xAllowed) || !in_array($y, $yAllowed)) {
            return false;
        }

        $this->position = array($x, $y);

        return true;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set background color
     *
     * @param string|array $background Background color
     *
     * @return boolean
     */
    public function setBackground($background = null)
    {
        if (!is_string($background) && !is_array($background)) {
            return false;
        }

        $bgDefault = array(
            'r' => 255,
            'g' => 255,
            'b' => 255,
            'a' => 1,
        );

        if (is_array($background)) {
            $bg = array_merge($bgDefault, $background);

            $this->background['r'] = $bg['r'] >= 0 && $bg['r'] <= 255 ? (int) $bg['r'] : 255;
            $this->background['g'] = $bg['g'] >= 0 && $bg['g'] <= 255 ? (int) $bg['g'] : 255;
            $this->background['b'] = $bg['b'] >= 0 && $bg['b'] <= 255 ? (int) $bg['b'] : 255;
            $this->background['a'] = $bg['a'] >= 0 && $bg['a'] <= 1 ? (float) $bg['a'] : 1;
        } elseif ($background === 'transparent') {
            $this->background = array(
                'r' => 255,
                'g' => 255,
                'b' => 255,
                'a' => 0,
            );

            return true;
        } elseif ($background === 'currentColor' || $background === 'currentcolor') {
            try {
                $image = !empty($this->src) ? $this->src : null;

                if (empty($image)) {
                    if ($this->isDebug) {
                        throw new \Exception('There was an error when filling in the background color');
                    }
                    return false;
                }

                $thumb = @imagecreatetruecolor(1, 1);
                @imagecopyresampled($thumb, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));

                $mainColor = @strtolower(dechex(imagecolorat($thumb, 0, 0)));
                $mainColor = '#' . $mainColor;

                imagedestroy($thumb);

                $this->background = $this->getHexaToRGBA($mainColor);

                return true;
            } catch (\Exception $error) {
                return false;
            }
        } elseif (preg_match("/^(#|)[a-fA-F0-9]{3,6}$/i", $background)) {
            $this->background = $this->getHexaToRGBA($background);
            return true;
        }

        return false;
    }

    /**
     * Get background color
     *
     * @return array
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Get background color to hexa
     *
     * @return string
     */
    public function getBackgroundToHexa()
    {
        return str_pad(dechex($this->background['r']), 2, '0', STR_PAD_LEFT) .
            str_pad(dechex($this->background['g']), 2, '0', STR_PAD_LEFT) .
            str_pad(dechex($this->background['b']), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Adds filters to the image, you can add several filters
     *
     * @param string $filter Filter type
     * @param int $value Filter value in percent
     *
     * @return boolean
     */
    public function addFilter(string $filter = '', int $value = 100)
    {
        $allowed = array('negate', 'grayscale', 'edgedetect', 'emboss', 'mean_removal', 'blur');

        if (!in_array($filter, $allowed)) {
            return false;
        }

        if ($value <= 0) {
            $value = 0;
        } elseif ($value >= 100) {
            $value = 100;
        }

        if ($filter === 'blur') {
            if ($value >= 100) {
                $value = 10;
            }
        }

        $this->filters[] = array($filter => $value);

        return true;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * If the destination file exists, then we overwrite it
     *
     * @param bool $isOverride Is override
     *
     * @return boolean
     */
    public function setIsOverride(bool $isOverride = true)
    {
        $this->isOverride = $isOverride;

        return $this->isOverride;
    }

    /**
     * Get override
     *
     * @return boolean
     */
    public function getIsOverride()
    {
        return $this->isOverride;
    }

    /**
     * The path of destination image, null return the raw image stream directly
     *
     * @param string|null $destination Destination path
     *
     * @return boolean
     */
    public function setDestination($destination = null)
    {
        if (is_string($destination)) {
            if (!is_dir($destination)) {
                return false;
            }

            $this->distPath = $destination;

            return true;
        }

        if (is_null($destination)) {
            $this->distPath = null;

            return true;
        }

        return false;
    }

    /**
     * Get the path of destination image, null return the raw image stream directly
     *
     * @return void
     */
    public function getDestination()
    {
        return $this->distPath;
    }

    /**
     * Display or not PHP error
     *
     * @param bool $isDebug Is debug
     *
     * @return boolean
     */
    public function setIsDebug(bool $isDebug = false)
    {
        $this->isDebug = $isDebug;

        return $this->isDebug;
    }

    /**
     * Get if debug is activate or not
     *
     * @return boolean
     */
    public function getIsDebug()
    {
        return $this->isDebug;
    }

    /**
     * Destroy GD ressources
     *
     * @return void
     */
    private function destroyTempImg()
    {
        @imagedestroy($this->dist);
        @imagedestroy($this->src);
    }

    /**
     * Launch image generation
     *
     * @return string|bool Returns the name of the file, otherwise it returns false
     */
    public function render()
    {
        if (!is_file($this->srcPath)) {
            if ($this->isDebug) {
                throw new \Exception('The source image not exist');
            }
            return false;
        }

        if (is_string($this->distPath) && empty($this->distPath)) {
            if ($this->isDebug) {
                throw new \Exception('The destination path does not exist');
            }
            return false;
        }

        if (is_string($this->distPath) && empty($this->name)) {
            if ($this->isDebug) {
                throw new \Exception('Image name not register');
            }
            return false;
        }

        if ($this->distPath === '') {
            if ($this->isDebug) {
                throw new \Exception('Bug with destination path');
            }
            return false;
        }

        if (!is_int($this->srcWidth) || !is_int($this->srcHeight)) {
            if ($this->isDebug) {
                throw new \Exception('Bug with source image sizes');
            }
            return false;
        }

        $this->calculDistSize();
        $this->calculDistPosition();

        // If source image is not create
        if (empty($this->src)) {
            if ($this->isDebug) {
                throw new \Exception('Can\'t create the source image from the path');
            }
            return false;
        }

        $this->dist = @imagecreatetruecolor($this->thumbWidth, $this->thumbHeight);

        if (empty($this->dist)) {
            if ($this->isDebug) {
                throw new \Exception('There is a problem when processing the destination file');
            }
            return false;
        }

        $this->replaceDistDPIIfEmpty();
        $dpi = @imageresolution($this->dist, $this->distDPI[0], $this->distDPI[1]);

        if (empty($dpi)) {
            if ($this->isDebug) {
                throw new \Exception('There is a problem when processing the destination file');
            }
            return false;
        }

        // If destination image is "jpg", force no transparent background
        if ($this->distExtension === 'jpg' || $this->distExtension === 'jpeg') {
            $this->background['a'] = 1;
        }

        // If the transparent background is set, then apply it to the destination image
        if ($this->background['a'] < 1) {
            imagealphablending($this->dist, false);
            $alpha = (1 - $this->background['a']) * 127;
            $transparency = imagecolorallocatealpha(
                $this->dist,
                $this->background['r'],
                $this->background['g'],
                $this->background['b'],
                $alpha
            );

            imagefill($this->dist, 0, 0, $transparency);
            imagesavealpha($this->dist, true);

            $greenScreen = imagecolorallocate($this->dist, 0, 255, 0);
            imagefilledrectangle($this->dist, 0, 0, 0, 0, $greenScreen);
        } else {
            $color = imagecolorallocate(
                $this->dist,
                $this->background['r'],
                $this->background['g'],
                $this->background['b']
            );
            imagefilledrectangle($this->dist, 0, 0, $this->thumbWidth, $this->thumbHeight, $color);
        }

        unset($color);

        // Copy the source image to the destination image
        $isSampled = false;
        if ($this->isDebug) {
            $isSampled = imagecopyresampled(
                $this->dist,
                $this->src,
                $this->distX,
                $this->distY,
                0,
                0,
                $this->distWidth,
                $this->distHeight,
                $this->srcWidth,
                $this->srcHeight
            );
        } else {
            $isSampled = @imagecopyresampled(
                $this->dist,
                $this->src,
                $this->distX,
                $this->distY,
                0,
                0,
                $this->distWidth,
                $this->distHeight,
                $this->srcWidth,
                $this->srcHeight
            );
        }

        if (!$isSampled) {
            if ($this->isDebug) {
                throw new \Exception('Can\'t create the temp destination image');
            }
            $this->destroyTempImg();
            return false;
        }

        // Apply the filters
        foreach ($this->filters as $filter => $value) {
            if (!in_array($filter, self::FILTERS_ALLOWED)) {
                continue;
            }

            $filterConstant = self::FILTERS_ALLOWED[$filter];

            if ($filter === 'blur') {
                for ($i = 0; $i < $value; $i++) {
                    $check = imagefilter($this->dist, $filterConstant);
                    if (!$check && $this->isDebug) {
                        throw new \Exception('Can\'t apply filter');
                    }
                }
            } else {
                $check = imagefilter($this->dist, $filterConstant);
                if (!$check && $this->isDebug) {
                    throw new \Exception('Can\'t apply filter');
                }
            }
        }

        // Set the final extension if there is no conversion done on the file
        $this->replaceDistExtensionIfEmpty();

        $destinationFilePath = $this->distPath;
        $destinationFileName = $this->name . '.' . $this->distExtension;

        if (is_string($this->distPath) && !empty($this->distPath)) {
            $destinationFilePath =
              preg_replace('/(\/)+$/', '', $this->distPath) . DIRECTORY_SEPARATOR . $destinationFileName;

            // Stop the process if the file exists and the override is disabled
            if (file_exists($destinationFilePath) && !$this->isOverride) {
                $this->destroyTempImg();
                return false;
            }
        }

        $imageCreate = false;

        if ($this->distExtension === 'jpg' || $this->distExtension === 'jpeg') {
            $imageCreate = imagejpeg($this->dist, $destinationFilePath, $this->quality);
        } elseif ($this->distExtension === 'png') {
            $quality = ($this->quality * 9) / 100;
            $imageCreate = imagepng($this->dist, $destinationFilePath, $quality);
        } elseif ($this->distExtension === 'gif') {
            $imageCreate = imagegif($this->dist, $destinationFilePath, $this->quality);
        } else {
            $this->destroyTempImg();
            return false;
        }

        if (!$imageCreate) {
            if ($this->isDebug) {
                throw new \Exception('Can\'t create destination image');
            }
            $this->destroyTempImg();
            return false;
        }

        $this->destroyTempImg();

        if ($imageCreate) {
            return $destinationFileName;
        } else {
            return false;
        }
    }

    /**
     * Set destination sizes
     *
     * @return void
     */
    private function calculDistSize()
    {
        // On vérifit si c'est un redimmenssionnement
        if ($this->thumbWidth > 0 xor $this->thumbHeight > 0) {
            // On enregistre la taille une fois redimmenssionné
            if (is_int($this->thumbWidth)) {
                $this->thumbHeight = $this->srcHeight * ($this->thumbWidth / $this->srcWidth);
            } else {
                $this->thumbWidth = $this->srcWidth * ($this->thumbHeight / $this->srcHeight);
            }

            $this->distWidth = $this->thumbWidth;
            $this->distHeight = $this->thumbHeight;
        } elseif ($this->thumbWidth > 0 && $this->thumbHeight > 0) {
            // On vérifit si c'est une vignette

            if ($this->fit === 'stretch') {
                $this->distWidth = $this->thumbWidth;
                $this->distHeight = $this->thumbHeight;
            } elseif ($this->fit === 'contain' || $this->fit === 'cover') {
                $fitAllowed = array(
                    'contain' => array(
                    $this->thumbWidth > $this->thumbHeight &&
                    $this->srcWidth > $this->srcHeight &&
                    ($this->srcWidth * $this->thumbHeight) / $this->srcHeight < $this->thumbWidth
                        ? true
                        : false,
                    $this->thumbWidth > $this->thumbHeight && $this->srcWidth <= $this->srcHeight ? true : false,
                    $this->thumbWidth <= $this->thumbHeight &&
                    $this->srcWidth < $this->srcHeight &&
                    ($this->srcWidth * $this->thumbHeight) / $this->srcHeight < $this->thumbWidth
                        ? true
                        : false,
                    $this->thumbWidth === $this->thumbHeight && $this->srcWidth === $this->srcHeight ? true : false
                    ),
                    'cover' => array(
                    $this->thumbWidth > $this->thumbHeight &&
                    $this->srcWidth > $this->srcHeight &&
                    ($this->srcWidth * $this->thumbHeight) / $this->srcHeight > $this->thumbWidth
                        ? true
                        : false,
                    $this->thumbWidth <= $this->thumbHeight && $this->srcWidth > $this->srcHeight ? true : false,
                    $this->thumbWidth < $this->thumbHeight &&
                    $this->srcWidth <= $this->srcHeight &&
                    ($this->srcWidth * $this->thumbHeight) / $this->srcHeight > $this->thumbWidth
                        ? true
                        : false,
                    $this->thumbWidth === $this->thumbHeight && $this->srcWidth === $this->srcHeight ? true : false
                    )
                );

                // Si c'est le cas 1
                if (
                    (in_array(true, $fitAllowed['contain']) && $this->fit === 'contain') ||
                    (in_array(true, $fitAllowed['cover']) && $this->fit === 'cover')
                ) {
                    // On redimmensionne 'img' d'abord par sa largeur
                    $this->distWidth = ($this->srcWidth * $this->thumbHeight) / $this->srcHeight;
                    $this->distHeight = ($this->srcHeight * $this->distWidth) / $this->srcWidth;
                } else {
                    // Si c'est le cas 2

                    // On redimmensionne 'img' d'abord par sa hauteur
                    $this->distHeight = ($this->srcHeight * $this->thumbWidth) / $this->srcWidth;
                    $this->distWidth = ($this->srcWidth * $this->distHeight) / $this->srcHeight;
                }
            }
        } else {
            // Sinon on donne les même dimension que l'image original

            $this->distWidth = $this->thumbWidth = $this->srcWidth;
            $this->distHeight = $this->thumbHeight = $this->srcHeight;
        }
    }

    /**
     * Set destination position in thumbnail
     *
     * @return void
     */
    private function calculDistPosition()
    {
        // Extract datas
        $x = 0;
        $y = 0;

        // We do the calculation only if we are in "contain" or "cover".
        if ($this->fit === 'contain' || $this->fit === 'cover') {
            // X calcul
            if ($this->position[0] === 'left') {
                $x = 0;
            } elseif ($this->position[0] === 'center') {
                $x = ($this->thumbWidth - $this->distWidth) / 2;
            } elseif ($this->position[0] === 'right') {
                $x = $this->thumbWidth - $this->distWidth;
            }

            // Y Calcul
            if ($this->position[1] === 'top') {
                $y = 0;
            } elseif ($this->position[1] === 'center') {
                $y = ($this->thumbHeight - $this->distHeight) / 2;
            } elseif ($this->position[1] === 'bottom') {
                $y = $this->thumbHeight - $this->distHeight;
            }
        }

        $this->distX = $x;
        $this->distY = $y;
    }

    /**
     * Set the final extension if there is no conversion done on the file
     */
    private function replaceDistExtensionIfEmpty()
    {
        if (empty($this->distExtension)) {
            $this->distExtension = $this->srcExtension;
        }
    }

    /**
     * Set destination position in thumbnail
     *
     * @return void
     */
    private function replaceDistDPIIfEmpty()
    {
        if (empty($this->distDPI[0]) && empty($this->distDPI[1])) {
            $this->distDPI = $this->srcDPI;
        }
    }

    /**
     * Convert Hexadecimal to RGBA
     *
     * @param string $hex Hexadecimal color
     *
     * @return array
     */
    private function getHexaToRGBA(string $hex = '')
    {
        $r = 255;
        $g = 255;
        $b = 255;
        $a = 1;

        if (empty($hex)) {
            return array(
                'r' => $r,
                'g' => $g,
                'b' => $b,
                'a' => $a
            );
        }

        $hex = str_replace('#', '', $hex);
        $hex = mb_strlen($hex) === 3 ? $hex . $hex : $hex;

        $r = strlen($hex) === 6 ? hexdec(substr($hex, 0, 2)) : $r;
        $g = strlen($hex) === 6 ? hexdec(substr($hex, 2, 2)) : $g;
        $b = strlen($hex) === 6 ? hexdec(substr($hex, 4, 2)) : $b;

        return array(
            'r' => $r,
            'g' => $g,
            'b' => $b,
            'a' => $a
        );
    }
}
