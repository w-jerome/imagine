<?php
namespace Imagine;

class Imagine
{
  private $name;
  private $src;
  private $srcWidth;
  private $srcHeight;
  private $srcMime;
  private $srcExtension;
  private $img;
  private $imgWidth;
  private $imgHeight;
  private $imgX = 0;
  private $imgY = 0;
  private $newExtension;
  private $thumbWidth;
  private $thumbHeight;
  private $quality = 100;
  private $processing = 'contain';
  private $position = 'center center';
  private $filters = array();
  private $background = array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 1);
  private $override = true;
  private $destination;
  private $debug = false;

  function __construct($imgSrc = null)
  {
    if (empty($imgSrc) || !is_string($imgSrc)) {
      return false;
    }

    $this->src = $imgSrc;
    $this->searchInfo();

    return $this;
  }

  public function name($name = null)
  {
    if (empty($name) || !is_string($name)) {
      return false;
    }

    $this->name = $name;
  }

  public function width($width = null)
  {
    if (!is_int($width)) {
      return false;
    }

    $this->thumbWidth = $width;
  }

  public function height($height = null)
  {
    if (!is_int($height)) {
      return false;
    }

    $this->thumbHeight = $height;
  }

  public function quality($quality = 100)
  {
    if (!is_int($quality) && !is_float($quality)) {
      return false;
    }

    if ($quality <= 0) {
      $quality = 0;
    } elseif ($quality >= 100) {
      $quality = 100;
    }

    $this->quality = $quality;
  }

  public function convert($extension = null)
  {
    $posibilities = array('jpg', 'png', 'gif');
    if (!is_string($extension) || !in_array($extension, $posibilities)) {
      return false;
    }

    $this->newExtension = $extension;
  }

  public function processing($processing = null)
  {
    $posibilities = array('stretch', 'contain', 'cover');
    if (!is_string($processing) || !in_array($processing, $posibilities)) {
      return false;
    }

    $this->processing = $processing;
  }

  public function position($position = null)
  {
    if (!is_string($position)) {
      return false;
    }

    $position = str_replace(';', ' ', $position);
    $position = str_replace('|', ' ', $position);
    $position = str_replace(',', ' ', $position);
    $position = str_replace('  ', ' ', $position);
    $position = explode(' ', $position);

    $position[0] = !empty($position[0]) ? trim($position[0]) : 'center';
    $position[1] = !empty($position[1]) ? trim($position[1]) : 'center';

    $x = 'center';
    $y = 'center';

    $xPosibilities = array('left', 'center', 'right');
    $yPosibilities = array('top', 'center', 'bottom');

    if (in_array($position[0], $xPosibilities)) {
      $position[0] = $position[0];
    }

    if (in_array($position[0], $yPosibilities)) {
      $position[1] = $position[0];
    }

    if (in_array($position[0], $xPosibilities)) {
      $x = $position[0];
    }

    if (in_array($position[1], $yPosibilities)) {
      $y = $position[1];
    }

    // On met à jour les données
    $this->position = $x . ' ' . $y;
  }

  public function filter($filter = null, $value = 100)
  {
    $posibilities = array('negate', 'grayscale', 'edgedetect', 'emboss', 'mean_removal', 'blur');

    if (!is_string($filter) || !in_array($filter, $posibilities) || !is_int($value)) {
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
  }

  public function background($background = null)
  {
    if (!is_string($background) && !is_array($background)) {
      return false;
    }

    if (
      is_array($background) &&
      array_key_exists('r', $background) &&
      array_key_exists('g', $background) &&
      array_key_exists('b', $background)
    ) {
      if (
        $background['r'] < 0 ||
        $background['r'] > 255 ||
        $background['g'] < 0 ||
        $background['g'] > 255 ||
        $background['b'] < 0 ||
        $background['b'] > 255
      ) {
        $background['r'] = 255;
        $background['g'] = 255;
        $background['b'] = 255;
      }

      if (array_key_exists('a', $background) && ($background['a'] < 0 || $background['a'] > 1)) {
        $background['a'] = 1;
      }

      $this->background = $background;
    } elseif ($background === 'transparent') {
      $this->background = array(
        'r' => 255,
        'g' => 255,
        'b' => 255,
        'a' => 0
      );
    } elseif ($background === 'currentColor' || $background === 'currentcolor') {
      try {
        $image = null;

        if ($this->srcExtension === 'jpg') {
          $image = @imagecreatefromjpeg($this->src);
        } elseif ($this->srcExtension === 'png') {
          $image = @imagecreatefrompng($this->src);
        } elseif ($this->srcExtension === 'gif') {
          $image = @imagecreatefromgif($this->src);
        }

        $thumb = @imagecreatetruecolor(1, 1);
        @imagecopyresampled($thumb, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));
        $mainColor = @strtolower(dechex(imagecolorat($thumb, 0, 0)));
        $mainColor = '#' . $mainColor;
        imagedestroy($image);
        imagedestroy($thumb);
      } catch (Exception $error) {
        $mainColor = '#ffffff';
      }

      $this->background = $this->hexToRGBA($mainColor);
    } elseif (preg_match("/^(#|)[a-fA-F0-9]{3,6}$/i", $background)) {
      $this->background = $this->hexToRGBA($background);
    } else {
      $this->background = array(
        'r' => 255,
        'g' => 255,
        'b' => 255,
        'a' => 1
      );
    }
  }

  public function override($override = true)
  {
    if (!is_bool($override)) {
      return false;
    }

    $this->override = $override;
  }

  public function destination($destination = null)
  {
    if (!is_string($destination) || empty($destination)) {
      return false;
    }

    if (is_dir($destination)) {
      $this->destination = $destination;
    } else {
      $this->destination = false;
      return false;
    }
  }

  public function debug($debug)
  {
    if (!is_bool($debug)) {
      return false;
    }

    $this->debug = $debug;
  }

  private function searchInfo()
  {
    $this->setSize();
    $this->setMime();
    $this->setExtension();
  }

  private function setSize()
  {
    if (!is_file($this->src)) {
      return false;
    }

    $info = getimagesize($this->src);

    // On met à jour les données
    $this->srcWidth = $info[0];
    $this->srcHeight = $info[1];
  }

  private function setMime()
  {
    if (!is_file($this->src)) {
      return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    // On met à jour les données
    $this->srcMime = finfo_file($finfo, $this->src);

    // On supprime les données temporaires
    finfo_close($finfo);
  }

  private function setExtension()
  {
    if (!$this->srcMime) {
      return false;
    }

    if ($this->srcMime === 'image/jpeg') {
      $this->srcExtension = 'jpg';
    } elseif ($this->srcMime === 'image/png') {
      $this->srcExtension = 'png';
    } elseif ($this->srcMime === 'image/gif') {
      $this->srcExtension = 'gif';
    }
  }

  private function destroyTempImg()
  {
    @imagedestroy($this->img);
    @imagedestroy($this->src);
  }

  public function render()
  {
    if (!is_file($this->src)) {
      return false;
    }

    if (!is_string($this->name)) {
      return false;
    }

    if ($this->destination === false) {
      return false;
    }

    if (!is_int($this->srcWidth) || !is_int($this->srcHeight)) {
      return false;
    }

    $this->caclculSize();
    $this->caclculPosition();

    $this->img = $this->debug
      ? imagecreatetruecolor($this->thumbWidth, $this->thumbHeight)
      : @imagecreatetruecolor($this->thumbWidth, $this->thumbHeight);

    if (!$this->img) {
      //Impossible de créer l'image temporaire
      return false;
    }

    //On crée l'image source pour la modifier
    if ($this->srcMime == 'image/jpeg') {
      $this->src = $this->debug ? imagecreatefromjpeg($this->src) : @imagecreatefromjpeg($this->src);
    } elseif ($this->srcMime == 'image/png') {
      $this->src = $this->debug ? imagecreatefrompng($this->src) : @imagecreatefrompng($this->src);
    } elseif ($this->srcMime == 'image/gif') {
      $this->src = $this->debug ? imagecreatefromgif($this->src) : @imagecreatefromgif($this->src);
    }

    //On vérifie que l'image source à bien était crée
    if (!$this->src) {
      //Impossible de créer l'image source
      @imagedestroy($this->img);
      return false;
    }

    /*
     * Début du choix de la couleur de fond
     */

    if ($this->newExtension === 'jpg') {
      $this->background['a'] = 1;
    }

    if ($this->background['a'] < 1) {
      imagealphablending($this->img, false);
      $alpha = (1 - $this->background['a']) * 127;
      $transparency = imagecolorallocatealpha(
        $this->img,
        $this->background['r'],
        $this->background['g'],
        $this->background['b'],
        $alpha
      );

      imagefill($this->img, 0, 0, $transparency);
      imagesavealpha($this->img, true);

      $black = imagecolorallocate($this->img, 0, 255, 0);
      imagefilledrectangle($this->img, 0, 0, 0, 0, $black);
    } else {
      $color = imagecolorallocate($this->img, $this->background['r'], $this->background['g'], $this->background['b']);
      imagefilledrectangle($this->img, 0, 0, $this->thumbWidth, $this->thumbHeight, $color);
    }

    unset($color);

    //On execute le redimmenssionnement et on vérifie que tous c'est bien passé
    $sampled = null;
    if ($this->debug) {
      $sampled = imagecopyresampled(
        $this->img,
        $this->src,
        $this->imgX,
        $this->imgY,
        0,
        0,
        $this->imgWidth,
        $this->imgHeight,
        $this->srcWidth,
        $this->srcHeight
      );
    } else {
      $sampled = @imagecopyresampled(
        $this->img,
        $this->src,
        $this->imgX,
        $this->imgY,
        0,
        0,
        $this->imgWidth,
        $this->imgHeight,
        $this->srcWidth,
        $this->srcHeight
      );
    }

    //Impossible de générer la nouvelle image à partir de la source
    if (!$sampled) {
      //On détruit les images temporaire
      $this->destroyTempImg();
      return false;
    }

    /*
     * On applique les filtres
     */

    if (is_array($this->filters) && !empty($this->filters)) {
      $posibilities = array(
        'negate' => IMG_FILTER_NEGATE,
        'grayscale' => IMG_FILTER_GRAYSCALE,
        'edgedetect' => IMG_FILTER_EDGEDETECT,
        'emboss' => IMG_FILTER_EMBOSS,
        'mean_removal' => IMG_FILTER_MEAN_REMOVAL,
        'blur' => IMG_FILTER_GAUSSIAN_BLUR
      );

      foreach ($this->filters as $filterTemp) {
        $filter = key($filterTemp);
        $value = $filterTemp[$filter];

        if (in_array($filter, $posibilities)) {
          $filterInt = $posibilities[$filter];

          if ($filter === 'blur') {
            for ($i = 0; $i < $value; $i++) {
              $check = $this->debug ? imagefilter($this->img, $filterInt) : @imagefilter($this->img, $filterInt);
            }
          } elseif (is_int($value)) {
            $check = $this->debug ? imagefilter($this->img, $filterInt) : @imagefilter($this->img, $filterInt);
          }
        }
      }
    }

    /*
     * On crée l'image final avec l'extension voulu
     */

    if ($this->newExtension === null) {
      $this->newExtension = $this->srcExtension;
    }

    $imageDestinationWithName = null;

    if (is_string($this->destination) && !empty($this->destination)) {
      $imageDestinationWithName = $this->destination . DIRECTORY_SEPARATOR . $this->name . $this->newExtension;
    }

    $imageCreate = null;

    if ($this->newExtension === 'jpg') {
      $imageCreate = $this->debug
        ? imagejpeg($this->img, $imageDestinationWithName, $this->quality)
        : @imagejpeg($this->img, $imageDestinationWithName, $this->quality);
    } elseif ($this->newExtension === 'png') {
      $quality = ($this->quality * 9) / 100;
      $imageCreate = $this->debug
        ? imagepng($this->img, $imageDestinationWithName, $quality)
        : @imagepng($this->img, $imageDestinationWithName, $quality);
    } elseif ($this->newExtension === 'gif') {
      $imageCreate = $this->debug
        ? imagegif($this->img, $imageDestinationWithName, $this->quality)
        : @imagegif($this->img, $imageDestinationWithName, $this->quality);
    } else {
      //On détruit les images temporaire
      $this->destroyTempImg();
      return false;
    }

    //On détruit les images temporaire
    $this->destroyTempImg();

    if ($imageCreate) {
      return $this->name . $this->newExtension;
    } else {
      return false;
    }
  }

  private function caclculSize()
  {
    // On vérifit si c'est un redimmenssionnement
    if (is_int($this->thumbWidth) xor is_int($this->thumbHeight)) {
      //On enregistre la taille une fois redimmenssionné
      if (is_int($this->thumbWidth)) {
        $this->thumbHeight = $this->srcHeight * ($this->thumbWidth / $this->srcWidth);
      } else {
        $this->thumbWidth = $this->srcWidth * ($this->thumbHeight / $this->srcHeight);
      }

      $this->imgWidth = $this->thumbWidth;
      $this->imgHeight = $this->thumbHeight;
    } elseif (is_int($this->thumbWidth) && is_int($this->thumbHeight)) {
      // On vérifit si c'est une vignette

      if ($this->processing === 'stretch') {
        $this->imgWidth = $this->thumbWidth;
        $this->imgHeight = $this->thumbHeight;
      } elseif ($this->processing === 'contain' || $this->processing === 'cover') {
        $posibilities = array(
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
          (in_array(true, $posibilities['contain']) && $this->processing === 'contain') ||
          (in_array(true, $posibilities['cover']) && $this->processing === 'cover')
        ) {
          // On redimmensionne 'img' d'abord par sa largeur
          $this->imgWidth = ($this->srcWidth * $this->thumbHeight) / $this->srcHeight;
          $this->imgHeight = ($this->srcHeight * $this->imgWidth) / $this->srcWidth;
        } else {
          // Si c'est le cas 2

          // On redimmensionne 'img' d'abord par sa hauteur
          $this->imgHeight = ($this->srcHeight * $this->thumbWidth) / $this->srcWidth;
          $this->imgWidth = ($this->srcWidth * $this->imgHeight) / $this->srcHeight;
        }
      }
    } else {
      // Sinon on donne les même dimension que l'image original

      $this->imgWidth = $this->thumbWidth = $this->srcWidth;
      $this->imgHeight = $this->thumbHeight = $this->srcHeight;
    }
  }

  private function caclculPosition()
  {
    // On extrait les données
    $position = explode(' ', $this->position);
    $x = 0;
    $y = 0;

    // On vérifit que c'est bien une "contain" ou "cover"
    if ($this->processing === 'contain' || $this->processing === 'cover') {
      // On calcul X
      if ($position[0] === 'left') {
        $x = 0;
      } elseif ($position[0] === 'center') {
        $x = ($this->thumbWidth - $this->imgWidth) / 2;
      } elseif ($position[0] === 'right') {
        $x = $this->thumbWidth - $this->imgWidth;
      }

      // On calcul Y
      if ($position[1] === 'top') {
        $y = 0;
      } elseif ($position[1] === 'center') {
        $y = ($this->thumbHeight - $this->imgHeight) / 2;
      } elseif ($position[1] === 'bottom') {
        $y = $this->thumbHeight - $this->imgHeight;
      }
    }

    // On met à jour les données
    $this->imgX = $x;
    $this->imgY = $y;
  }

  private function hexToRGBA($hex = null)
  {
    if (!is_string($hex)) {
      return false;
    }

    $r = 255;
    $g = 255;
    $b = 255;
    $a = 1;

    $hex = str_replace('#', '', $hex);

    $hex = mb_strlen($hex) === 3 ? $hex . $hex : $hex;

    if (strlen($hex) === 6) {
      $r = hexdec(substr($hex, 0, 2));
      $g = hexdec(substr($hex, 2, 2));
      $b = hexdec(substr($hex, 4, 2));
    } else {
      $r = 255;
      $g = 255;
      $b = 255;
    }

    return array(
      'r' => $r,
      'g' => $g,
      'b' => $b,
      'a' => $a
    );
  }

  public function getColor()
  {
    return sprintf('#%02x%02x%02x', $this->background['r'], $this->background['g'], $this->background['b']);
  }
}
