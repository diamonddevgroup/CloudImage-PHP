<?php

include_once('phpimagemanipulation.class.php');
$dir = $_SERVER['DOCUMENT_ROOT'];

// Create the thumbnail
$thumb = new phpimagemanipulation;

$image_path = $dir . "/" . basename($_GET['image']);

if (isset($_REQUEST['image']) && isset($_REQUEST['param'])) {
    $params = explode(",", $_REQUEST['param']);
    foreach ($params as $value) {
        $vars = explode("_", $value);
        $param = $vars[0];
        $var = $vars[1];

        switch ($param) {
            case "p":
                $image_path = $dir . $var . "/" . basename($_GET['image']);
                break;
            case "s":
                if (intval($var) > 0 && intval($var) <= 1800) {
                    $thumb->Thumbsize = intval($var);
                }
                $thumb->Square = true;
                break;
            case "w":
                if (intval($var) > 0 && intval($var) <= 1800) {
                    $thumb->Thumbwidth = $var;
                }
                break;
            case "h":
                if (intval($var) > 0 && intval($var) <= 1800) {
                    $thumb->Thumbheight = $var;
                }
                break;
            case "c":
                if ($var == "fit") {
                    $thumb->Cropimage = array(intval($var), 0, 50, 50, 50, 50);
                } else if ($var == "fill") {
                    $thumb->Cropimage = array(intval($var), 0, 0, 0, 0, 0);
                } else {
                    
                }
                break;
            case "r":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Backgroundcolor = '#eee';
                    $thumb->Clipcorner = array(2, $var, 0, 1, 1, 1, 1);
                    $thumb->Maketransparent = array(1, 0, '#eee', 30);
                }
                break;
            case "q":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Quality = $var;
                }
                break;
            case "bg":
                $thumb->Backgroundcolor = '#' . $var;
                break;
            case "sh":
                if (intval($var) > 0 && intval($var) <= 1) {
                    $thumb->Shadow = $var == "1" ? true : false;
                }
                break;
            case "so":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->shadow_offset = $var;
                }
                break;
            case "fw":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Framewidth = $var;
                }
                break;
            case "fc":
                $thumb->Framecolor = '#' . $var;
                break;
            case "fo":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->frame_offset = $var;
                }
                break;
            case "bd":
                if (intval($var) > 0 && intval($var) <= 1) {
                    $thumb->Binder = $var;
                }
                break;
            case "bs":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Binderspacing = $var;
                }
                break;
            case "bo":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->bind_offset = $var;
                }
                break;
            case "wm":
                $thumb->Watermarkpng = $var;
                break;
            case "wt":
                if ($var == "top_left") {
                    $thumb->Watermarkposition = '0% 0%';
                } else if ($var == "top_right") {
                    $thumb->Watermarkposition = '100% 0%';
                } else if ($var == "top") {
                    $thumb->Watermarkposition = '50% 0%';
                } else if ($var == "mid_left") {
                    $thumb->Watermarkposition = '0% 50%';
                } else if ($var == "mid_right") {
                    $thumb->Watermarkposition = '100% 50%';
                } else if ($var == "bottom_left") {
                    $thumb->Watermarkposition = '100% 0%';
                } else if ($var == "bottom_right") {
                    $thumb->Watermarkposition = '100% 100%';
                } else {
                    $thumb->Watermarkposition = '50% 50%';
                }
                break;
            case "wt":
                $thumb->Watermarktransparency = $var;
                break;
            case "rt":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Rotate = $var;
                }
                break;
            case "fx":
                $thumb->Fliphorizontal = $var == "1" ? true : false;
                break;
            case "fy":
                $thumb->Flipvertical = $var == "1" ? true : false;
                break;
            case "bt":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Brightness = $var;
                }
                break;
            case "ct":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Contrast = $var;
                }
                break;
            case "gm":
                if (intval($var) > 0 && intval($var) <= 100) {
                    $thumb->Gamma = $var;
                }
                break;
            default:
                break;
        }
    }
}
if (file_exists($image_path)) {
    $thumb->Createthumb($image_path);
} else {
    http_response_code(404);
}