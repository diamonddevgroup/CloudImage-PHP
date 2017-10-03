<?php

$dir = str_replace(chr(92), chr(47), getcwd() . '/');

if (isset($_GET['image']) && !empty($_GET['image'])) {
    include_once 'image.component.php';

    $width = 100;
    $height = 100;
    $action = 'crop';
    $crop_position = 'center';
    $watermark = '';
    $watermark_position = 'cc';
    $color = '0,0,0,0';
    $quality = 100;

    $image_path = $dir . "/" . basename($_GET['image']);

    $path = isset($_GET['param']) ? $_GET['param'] : '';

    $params = isset($_GET['param']) ? explode(",", $_GET['param']) : '';
    foreach ($params as $value) {
        $vars = explode("_", $value);
        $param = $vars[0];
        $var = $vars[1];
        switch ($param) {
            case "p":
                $image_path = $dir . $var . "/" . basename($_GET['image']);
                break;
            case "s":
                if (intval($var) > 0) {
                    $width = intval($var);
                    $height = intval($var);
                }
                break;
            case "w":
                if (intval($var) > 0) {
                    $width = $var;
                }
                break;
            case "h":
                if (intval($var) > 0) {
                    $height = $var;
                }
                break;
            case "c":
                if ($var == "fit") {
                    $action = 'resize';
                } else if ($var == "fill") {
                    $action = 'resize_crop';
                } else {
                    $action = 'crop';
                }
                break;
            case "q":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $quality = $var;
                }
                break;
            case "wm":
                $watermark = $var;
                break;
            case "wt":
                if ($var == "top_left") {
                    $watermark_position = 'tl';
                } else if ($var == "top_right") {
                    $watermark_position = 'tr';
                } else if ($var == "top") {
                    $watermark_position = 'tc';
                } else if ($var == "mid_left") {
                    $watermark_position = 'cl';
                } else if ($var == "mid_right") {
                    $watermark_position = 'cr';
                } else if ($var == "bottom_left") {
                    $watermark_position = 'bl';
                } else if ($var == "bottom_right") {
                    $watermark_position = 'br';
                } else if ($var == "bottom") {
                    $watermark_position = 'bc';
                } else {
                    $watermark_position = 'cc';
                }
                break;
        }
    }

    if (file_exists($image_path)) {
        $Image = new Image();

        $size = @getimagesize($image_path);
        $src_width = $size[0];
        $src_height = $size[1];

        if ($action == 'resize') {
            if ((int) $width > 0 && (int) $height > 0) {
                $Image->loadImage($image_path);
                $resp = $Image->isConvertPossible();
                if ($resp['status'] === true) {
                    $Image->resize($width, $height);
                }
            }
        } else if ($action == 'crop') {
            if ((int) $width > 0 & (int) $width < $src_width && (int) $height > 0 && (int) $height < $src_height) {
                $x = 0;
                $y = 0;
                $w = (int) $width;
                $h = (int) $height;
                switch ($crop_position) {
                    case 'center':
                        $x = round($src_width / 2) - round((int) $width / 2);
                        $y = round($src_height / 2) - round((int) $height / 2);
                        break;
                    case 'top':
                        $x = round($src_width / 2) - round((int) $width / 2);
                        break;
                    case 'bottom':
                        $x = round($src_width / 2) - round((int) $width / 2);
                        $y = $src_height - (int) $height;
                        break;
                    case 'left':
                        $y = round($src_height / 2) - round((int) $height / 2);
                        break;
                    case 'right':
                        $x = $src_width - (int) $width;
                        $y = round($src_height / 2) - round((int) $height / 2);
                        break;
                }
                $Image->loadImage($image_path);
                $Image->crop($x, $y, $w, $h, $w, $h);
            }
        } else if ($action == 'resize_crop') {
            if ((int) $width > 0 && (int) $height > 0) {
                $w = (int) $width;
                $h = (int) $height;
                $Image->loadImage($image_path);
                $Image->resize_crop($w, $h);
            }
        }

        if (!empty($watermark)) {
            $color_arr = explode(",", $color);
            $valid_color = true;
            if (count($color_arr) == 3) {
                if ((int) $color_arr[0] < 0 || (int) $color_arr[0] > 255 || (int) $color_arr[1] < 0 || (int) $color_arr[1] > 255 || (int) $color_arr[2] < 0 || (int) $color_arr[2] > 255) {
                    $valid_color = false;
                }
            } else {
                $valid_color = false;
            }
            if ($valid_color == true) {
                $Image->setColor($color_arr);
            }

            $Image->setFontSize(18)->setFont('fonts/arialbd.ttf');
            $Image->setWatermark($watermark, $watermark_position);
        }

        $Image->output($quality);
    }
}