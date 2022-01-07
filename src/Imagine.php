<?php

/**
 * PHP micro library to resize, thumbnail or apply filters to your images
 *
 * @package Imagine\Imagine
 **/

namespace Imagine;

class Imagine
{
    private $src = null;
    private $srcPath = '';
    private $srcWidth = 0;
    private $srcHeight = 0;
    private $srcMime = '';
    private $srcType = '';
    private $srcDPI = array(0, 0);
    private $dist = null;
    private $distWidth = 0;
    private $distHeight = 0;
    private $distType = '';
    private $distDPI = array(0, 0);
    private $thumbWidth = 0;
    private $thumbHeight = 0;
    private $quality = 100;
    private $fit = 'stretch';
    private $position = array('center', 'center');
    private $filters = array();
    private $background = array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 1);
    private $isOverride = true;
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
            throw new \Exception('There was a problem when generating the source file');
            return $this;
        }

        $this->setSrcSize();
        $this->setSrcType();
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

        if (empty($info) || (!is_int($info[0]) || !is_int($info[1]))) {
            throw new \Exception('Bug with source image sizes');
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
    private function setSrcType()
    {
        if ($this->srcMime === 'image/jpeg') {
            $extension = pathinfo($this->srcPath, PATHINFO_EXTENSION);

            if (!empty($extension) && $extension === 'jpeg') {
                $this->srcType = $extension; // It can be "jpeg"... Honestly
            } else {
                $this->srcType = 'jpg';
            }
        } elseif ($this->srcMime === 'image/png') {
            $this->srcType = 'png';
        } elseif ($this->srcMime === 'image/gif') {
            $this->srcType = 'gif';
        }
    }

    /**
     * Set source image DPI
     *
     * @return bool
     */
    private function setSrcDPI()
    {
        $dpi = imageresolution($this->src);

        if (empty($dpi)) {
            throw new \Exception('There was an error while searching for the resolution');
            return false;
        }

        $this->srcDPI = $dpi ?? array(72, 72);

        return true;
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
        $size = $this->calculDistSizeFromThumbSize();

        return $size[0];
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
        $size = $this->calculDistSizeFromThumbSize();

        return $size[1];
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
    public function setType(string $extension = '')
    {
        if (!in_array($extension, self::TYPES_ALLOWED)) {
            return false;
        }

        $this->distType = $extension;

        return true;
    }

    /**
     * Get source file type
     *
     * @return string
     */
    public function getSrcType()
    {
        return $this->srcType;
    }

    /**
     * Get destination file type
     *
     * @return string
     */
    public function getDistType()
    {
        if (empty($this->distType)) {
            return $this->srcType;
        }

        return $this->distType;
    }

    /**
     * Set destination file DPI
     *
     * @param int $dpiX Destination file DPI x
     * @param int $dpiY Destination file DPI y
     *
     * @return int
     */
    public function setDPI(int $dpiX = 0, int $dpiY = 0)
    {
        $dpiX = ($dpiX <= 0) ? 72 : $dpiX;
        $dpiY = ($dpiY <= 0) ? $dpiX : $dpiY;

        return $this->distDPI = array($dpiX, $dpiY);
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
        if (empty($this->distDPI[0]) || empty($this->distDPI[1])) {
            return $this->srcDPI;
        }

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
     * @return bool
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
     * @return bool
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
     * @return bool
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
                $image = $this->src;

                if (empty($image)) {
                    throw new \Exception('There was an error when filling in the background color');
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
     * @param int $filterConstant Filter contant
     * @param int $value Filter value in percent
     *
     * @return bool
     */
    public function addFilter(int $filterConstant = -1, $params = null)
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
     * @return bool
     */
    public function setIsOverride(bool $isOverride = true)
    {
        $this->isOverride = $isOverride;

        return $this->isOverride;
    }

    /**
     * Get override
     *
     * @return bool
     */
    public function getIsOverride()
    {
        return $this->isOverride;
    }

    public function save(string $destination = '', bool $destroySrcGD = true, bool $destroyDistGD = true)
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

    public function displayOnBrowser(bool $destroySrcGD = true, bool $destroyDistGD = true)
    {
        return $this->render(null, $destroySrcGD, $destroyDistGD);
    }

    /**
     * Launch image generation
     *
     * @return string|bool Returns the name of the file, otherwise it returns false
     */
    private function render($destination = '', bool $destroySrcGD = true, bool $destroyDistGD = true)
    {
        $size = $this->calculDistSizeFromThumbSize();

        $this->thumbWidth = $size[0];
        $this->thumbHeight = $size[1];
        $this->distWidth = $size[2];
        $this->distHeight = $size[3];

        $this->dist = imagecreatetruecolor($this->thumbWidth, $this->thumbHeight);

        if (empty($this->dist)) {
            throw new \Exception('There is a problem when processing the destination file');
            return false;
        }

        if (empty($this->distDPI[0]) || empty($this->distDPI[1])) {
            $this->distDPI = $this->srcDPI;
        }

        $dpi = imageresolution($this->dist, $this->distDPI[0], $this->distDPI[1]);

        if (empty($dpi)) {
            throw new \Exception('There is a problem when processing the destination file');
            return false;
        }

        // If destination image is "jpg", force no transparent background
        if ($this->distType === 'jpg' || $this->distType === 'jpeg') {
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
        } else {
            $bgColor = imagecolorallocate(
                $this->dist,
                $this->background['r'],
                $this->background['g'],
                $this->background['b']
            );
            imagefilledrectangle($this->dist, 0, 0, $this->thumbWidth, $this->thumbHeight, $bgColor);
            unset($bgColor);
        }
        // unset($bgColor);

        // Copy the source image to the destination image
        $position = array(0, 0); // x, y

        // We do the calculation only if we are in "contain" or "cover".
        if ($this->fit === 'contain' || $this->fit === 'cover') {
            // X calcul
            if ($this->position[0] === 'left') {
                $position[0] = 0;
            } elseif ($this->position[0] === 'center') {
                $position[0] = ($this->thumbWidth - $this->distWidth) / 2;
            } elseif ($this->position[0] === 'right') {
                $position[0] = $this->thumbWidth - (int) $this->distWidth;
            }

            // Y Calcul
            if ($this->position[1] === 'top') {
                $position[1] = 0;
            } elseif ($this->position[1] === 'center') {
                $position[1] = ($this->thumbHeight - $this->distHeight) / 2;
            } elseif ($this->position[1] === 'bottom') {
                $position[1] = $this->thumbHeight - (int) $this->distHeight;
            }
        }

        $isSampled = imagecopyresampled(
            $this->dist,
            $this->src,
            $position[0],
            $position[1],
            0,
            0,
            $this->distWidth,
            $this->distHeight,
            $this->srcWidth,
            $this->srcHeight
        );

        if (!$isSampled) {
            $this->destroyTempImg();
            throw new \Exception('Can\'t create the temp destination image');
            return false;
        }

        // Apply the filters
        foreach ($this->filters as $filter) {
            $check = true;

            if (isset($filter['params'])) {
                $check = imagefilter($this->dist, $filter['type'], $filter['params']);
            } else {
                $check = imagefilter($this->dist, $filter['type']);
            }

            if (!$check) {
                throw new \Exception('Can\'t apply filter ' . $filter['type']);
            }
        }

        // Set the final extension if there is no conversion done on the file
        if (empty($this->distType)) {
            $this->distType = $this->srcType;
        }

        $isCreate = false;

        if ($this->distType === 'jpg' || $this->distType === 'jpeg') {
            $isCreate = imagejpeg($this->dist, $destination, $this->quality);
        } elseif ($this->distType === 'png') {
            $isCreate = imagepng($this->dist, $destination, ($this->quality * 9) / 100);
        } elseif ($this->distType === 'gif') {
            $isCreate = imagegif($this->dist, $destination, $this->quality);
        } else {
            $this->destroyTempImg();
            return false;
        }

        $this->destroyTempImg();

        if (!$isCreate) {
            throw new \Exception('Can\'t create destination image');
            return false;
        }

        return true;
    }

    /**
     * Set destination sizes
     *
     * @return void
     */
    private function calculDistSizeFromThumbSize()
    {
        $thumbWidth = $this->thumbWidth;
        $thumbHeight = $this->thumbHeight;
        $distWidth = $this->distWidth;
        $distHeight = $this->distHeight;

        // On vérifit si c'est un redimmenssionnement
        if ($thumbWidth > 0 xor $thumbHeight > 0) {
            // On enregistre la taille une fois redimmenssionné
            if ($thumbWidth) {
                $thumbHeight = $this->srcHeight * ($thumbWidth / $this->srcWidth);
            } else {
                $thumbWidth = $this->srcWidth * ($thumbHeight / $this->srcHeight);
            }

            $distWidth = $thumbWidth;
            $distHeight = $thumbHeight;
        } elseif ($thumbWidth > 0 && $thumbHeight > 0) {
            // On vérifit si c'est une vignette

            if ($this->fit === 'stretch') {
                $distWidth = $thumbWidth;
                $distHeight = $thumbHeight;
            } elseif ($this->fit === 'contain' || $this->fit === 'cover') {
                $fitAllowed = array(
                    'contain' => array(
                        $thumbWidth > $thumbHeight &&
                        $this->srcWidth > $this->srcHeight &&
                        ($this->srcWidth * $thumbHeight) / $this->srcHeight < $thumbWidth
                            ? true
                            : false,
                        $thumbWidth > $thumbHeight && $this->srcWidth <= $this->srcHeight ? true : false,
                        $thumbWidth <= $thumbHeight &&
                        $this->srcWidth < $this->srcHeight &&
                        ($this->srcWidth * $thumbHeight) / $this->srcHeight < $thumbWidth
                            ? true
                            : false,
                        $thumbWidth === $thumbHeight && $this->srcWidth === $this->srcHeight ? true : false
                    ),
                    'cover' => array(
                        $thumbWidth > $thumbHeight &&
                        $this->srcWidth > $this->srcHeight &&
                        ($this->srcWidth * $thumbHeight) / $this->srcHeight > $thumbWidth
                            ? true
                            : false,
                        $thumbWidth <= $thumbHeight && $this->srcWidth > $this->srcHeight ? true : false,
                        $thumbWidth < $thumbHeight &&
                        $this->srcWidth <= $this->srcHeight &&
                        ($this->srcWidth * $thumbHeight) / $this->srcHeight > $thumbWidth
                            ? true
                            : false,
                        $thumbWidth === $thumbHeight && $this->srcWidth === $this->srcHeight ? true : false
                    )
                );

                // Si c'est le cas 1
                if (
                    (in_array(true, $fitAllowed['contain']) && $this->fit === 'contain') ||
                    (in_array(true, $fitAllowed['cover']) && $this->fit === 'cover')
                ) {
                    // On redimmensionne 'img' d'abord par sa largeur
                    $distWidth = ($this->srcWidth * $thumbHeight) / $this->srcHeight;
                    $distHeight = ($this->srcHeight * $distWidth) / $this->srcWidth;
                } else {
                    // Si c'est le cas 2

                    // On redimmensionne 'img' d'abord par sa hauteur
                    $distHeight = ($this->srcHeight * $thumbWidth) / $this->srcWidth;
                    $distWidth = ($this->srcWidth * $distHeight) / $this->srcHeight;
                }
            }
        } else {
            // Sinon on donne les même dimension que l'image original

            $distWidth = $thumbWidth = $this->srcWidth;
            $distHeight = $thumbHeight = $this->srcHeight;
        }

        return array(
            $thumbWidth,
            $thumbHeight,
            $distWidth,
            $distHeight,
        );
    }

    /**
     * Destroy GD ressources
     *
     * @param bool $isSrcMustDestroy Destroy dist GD
     * @param bool $isDistMustDestroy Destroy dist GD
     *
     * @return void
     */
    private function destroyTempImg(bool $isSrcMustDestroy = true, bool $isDistMustDestroy = true)
    {
        if ($isSrcMustDestroy) {
            @imagedestroy($this->src);
        }

        if ($isDistMustDestroy) {
            @imagedestroy($this->dist);
        }
    }
}
