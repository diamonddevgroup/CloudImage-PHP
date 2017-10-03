<?php

/**
 * Image processing class
 *
 * @package framework.components
 * @since 1.0.0
 */
class Image {

    /**
     * RGB color
     * 
     * @var array
     * @access private
     */
    private $color = array(255, 255, 255);

    /**
     * Font path
     *
     * @var string
     * @access private
     */
    private $font;

    /**
     * Font size
     *
     * @var int
     * @access private
     */
    private $fontSize;

    /**
     * Image resource identifier
     *
     * @var resource
     * @access private
     */
    private $image;

    /**
     * One of the IMAGETYPE_* constants indicating the type of the image.
     *
     * @var int
     * @access private
     */
    private $imageType;

    /**
     * Image width
     * 
     * @var int
     * @access private
     */
    private $width;

    /**
     * Image height
     *
     * @var int
     * @access private
     */
    private $height;
    private $file;

    /**
     * Constructor - automatically called when you create a new instance of a class with new
     *
     * @access public
     * @return self
     */
    public function __construct() {
        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            $this->error = "GD extension is not loaded";
            $this->errorCode = 200;
        }
    }

    /**
     * Get color
     *
     * @access public
     * @return array
     */
    public function getColor() {
        return $this->color;
    }

    /**
     * Get font
     * 
     * @access public
     * @return string
     */
    public function getFont() {
        return $this->font;
    }

    /**
     * Get font size
     * 
     * @access public
     * @return number
     */
    public function getFontSize() {
        return $this->fontSize;
    }

    /**
     * Get image height
     *
     * @access public
     * @return int|false Return the height of the image or FALSE on errors.
     */
    public function getHeight() {
        return imagesy($this->getImage());
    }

    /**
     * Get image resource
     *
     * @access public
     * @return resource
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Get the size of an image
     *
     * @access public
     * @return Returns an array with 7 elements.
     */
    public function getImageSize() {
        return getimagesize($this->file['tmp_name']);
    }

    /**
     * Get the type of image resource
     * 
     * @access public
     * @return number
     */
    public function getImageType() {
        return $this->imageType;
    }

    /**
     * Get image width
     *
     * @access public
     * @return int|false Return the width of the image or FALSE on errors.
     */
    public function getWidth() {
        return imagesx($this->getImage());
    }

    /**
     * Check if system memory is enough for image processing
     *
     * @access public
     * @return array
     */
    public function isConvertPossible() {
        $status = true;
        if (function_exists('memory_get_usage') && ini_get('memory_limit')) {
            $info = $this->getImageSize();
            $MB = 1024 * 1024;
            $K64 = 64 * 1024;
            $tweak_factor = 1.6;
            $channels = isset($info['channels']) ? $info['channels'] : 3;
            $memory_needed = memory_get_usage() + ( round(($info[0] * $info[1] * $info['bits'] * $channels / 8 + $K64) * $tweak_factor));
            $memory_limit = ini_get('memory_limit');
            if ($memory_limit != '') {
                $memory_limit = substr($memory_limit, 0, -1) * $MB;
            }
            if ($memory_needed > $memory_limit) {
                $status = false;
            }
        }
        return compact('status', 'memory_needed', 'memory_limit');
    }

    /**
     * Load locale image file for later processing
     *
     * @param string $path The path to image
     * @access public
     * @return self
     */
    public function loadImage($path = NULL) {
        if (!is_null($path)) {
            $this->file = array(
                'tmp_name' => $path,
                'name' => basename($path)
            );
        }
        $info = $this->getImageSize();

        $this->width = $info[0];
        $this->height = $info[1];
        $this->setImageType($info[2]);
        $file = $path;

        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->setImage(@imagecreatefromjpeg($file));
                break;
            case IMAGETYPE_GIF:
                $this->setImage(@imagecreatefromgif($file));
                break;
            case IMAGETYPE_PNG:
                $this->setImage(@imagecreatefrompng($file));
                break;
        }
        return $this;
    }

    /**
     * Write text to the image
     *
     * @param string $text The text string in UTF-8 encoding.
     * @param string $position Accept: 'tl', 'tr', 'tc', 'bl', 'br', 'bc', 'cl', 'cr', 'cc'. <b>t</b> stands for Top, <b>b</b> stands for Bottom, <b>l</b> stands for Left, <b>r</b> stands for Right, <b>c</b> stands for Center.
     * @access public
     * @return self
     */
    public function setWatermark($text, $position) {
        $rgb = $this->getColor();

        $color = imagecolorallocate($this->getImage(), $rgb[0], $rgb[1], $rgb[2]);

        $tb = imagettfbbox($this->getFontSize(), 0, $this->getFont(), $text);

        switch ($position) {
            case 'tl':
                $x = $tb[0];
                $y = $this->getFontSize();
                break;
            case 'tr':
                $x = floor($this->getWidth() - $tb[2]);
                $y = $this->getFontSize();
                break;
            case 'tc':
                $x = ceil(($this->getWidth() - $tb[2]) / 2);
                $y = $this->getFontSize();
                break;
            case 'bl':
                $x = $tb[0];
                $y = floor($this->getHeight() - $this->getFontSize());
                break;
            case 'br':
                $x = floor($this->getWidth() - $tb[2]);
                $y = floor($this->getHeight() - $this->getFontSize());
                break;
            case 'bc':
                $x = ceil(($this->getWidth() - $tb[2]) / 2);
                $y = floor($this->getHeight() - $this->getFontSize());
                break;
            case 'cl':
                $x = $tb[0];
                $y = ceil($this->getHeight() / 2);
                break;
            case 'cr':
                $x = floor($this->getWidth() - $tb[2]);
                $y = ceil($this->getHeight() / 2);
                break;
            case 'cc':
            default:
                $x = ceil(($this->getWidth() - $tb[2]) / 2);
                $y = ceil($this->getHeight() / 2);
                break;
        }

        imagettftext($this->getImage(), $this->getFontSize(), 0, $x, $y, $color, $this->getFont(), $text);
        return $this;
    }

    /**
     * Set font path
     *
     * @param string $path The path to font file
     * @access public
     * @return self
     */
    public function setFont($path) {
        $this->font = $path;
        return $this;
    }

    /**
     * Set font size
     *
     * @param int $size
     * @access public
     * @return self
     */
    public function setFontSize($size) {
        $this->fontSize = $size;
        return $this;
    }

    /**
     * Set RGB color
     *
     * @param array $color Expect numeric array, eg. array(255, 255, 255)
     * @access public
     * @return self
     */
    public function setColor($color) {
        if (is_array($color) && count($color) === 3) {
            $this->color = $color;
        }
        return $this;
    }

    /**
     * Set image resource
     * 
     * @param resource $resource
     * @access public
     * @return self
     */
    public function setImage($resource) {
        if (is_resource($resource)) {
            $this->image = $resource;
        }
        return $this;
    }

    /**
     * Set the type of image resource
     *  
     * @param int $value
     * @return self
     */
    public function setImageType($value) {
        if (is_int($value)) {
            $this->imageType = $value;
        }
        return $this;
    }

    /**
     * Outputs image without saving
     * 
     * @param string $image_type
     * @param number $compression
     */
    public function output($compression = 100) {
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                header("Content-Type: image/jpeg");
                imageinterlace($this->getImage(), true);
                imagejpeg($this->getImage(), NULL, $compression);
                imagedestroy($this->getImage());
                break;
            case IMAGETYPE_GIF:
                header("Content-Type: image/gif");
                imagegif($this->getImage());
                imagedestroy($this->getImage());
                break;
            case IMAGETYPE_PNG:
                header("Content-Type: image/png");
                imagepng($this->getImage());
                imagedestroy($this->getImage());
                break;
        }

        exit;
    }

    /**
     * Image resize then crop to fixed size
     *
     * @access public
     * @return self
     */
    public function resize_crop($dst_w, $dst_h) {
        $sw = (int) imagesx($this->getImage());
        $sh = (int) imagesy($this->getImage());

        $yOff = 0;
        $xOff = 0;
        if ($sw < $sh) {
            $scale = $dst_w / $sw;
            $yOff = $sh / 2 - $dst_h / $scale / 2;
        } else {
            $scale = $dst_h / $sh;
            $xOff = $sw / 2 - $dst_w / $scale / 2;
        }

        $new_image = imagecreatetruecolor($dst_w, $dst_h);
        switch ($this->imageType) {
            case IMAGETYPE_PNG:
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $dst_w, $dst_h, $transparent);
                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_GIF:
                $transparent_index = imagecolortransparent($this->getImage());
                if ($transparent_index >= 0) {
                    $transparent_color = imagecolorsforindex($this->getImage(), $transparent_index);
                    $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($new_image, 0, 0, $transparent_index);
                    imagecolortransparent($new_image, $transparent_index);
                }
                break;
        }

        imagecopyresampled($new_image, $this->getImage(), 0, 0, $xOff, $yOff, $dst_w, $dst_h, $dst_w / $scale, $dst_h / $scale);

        $this->width = $dst_w;
        $this->height = $dst_h;
        $this->setImage($new_image);

        return $this;
    }

    public function resize($width, $height) {
        $new_image = imagecreatetruecolor($width, $height);
        switch ($this->imageType) {
            case IMAGETYPE_PNG:
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_GIF:
                $transparent_index = imagecolortransparent($this->getImage());
                if ($transparent_index >= 0) {
                    $transparent_color = imagecolorsforindex($this->getImage(), $transparent_index);
                    $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($new_image, 0, 0, $transparent_index);
                    imagecolortransparent($new_image, $transparent_index);
                }
                break;
        }
        imagecopyresampled($new_image, $this->getImage(), 0, 0, 0, 0, $width, $height, $this->width, $this->height);
        $this->width = $width;
        $this->height = $height;
        $this->setImage($new_image);

        return $this;
    }

    public function crop($src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $dst_x = 0, $dst_y = 0) {
        $new_image = imagecreatetruecolor($dst_w, $dst_h);

        switch ($this->imageType) {
            case IMAGETYPE_PNG:
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                imagefilledrectangle($new_image, 0, 0, $dst_w, $dst_h, $transparent);
                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_GIF:
                $transparent_index = imagecolortransparent($this->getImage());
                if ($transparent_index >= 0) {
                    $transparent_color = imagecolorsforindex($this->getImage(), $transparent_index);
                    $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($new_image, 0, 0, $transparent_index);
                    imagecolortransparent($new_image, $transparent_index);
                }
                break;
        }

        imagecopyresampled($new_image, $this->getImage(), $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        $this->width = $dst_w;
        $this->height = $dst_h;
        $this->setImage($new_image);
        return $this;
    }

}
