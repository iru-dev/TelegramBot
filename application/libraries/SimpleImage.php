<?php

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/

class SimpleImage
{

    var $image;
    var $image_type;

    function load($filename)
    {

        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {

            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {

            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {

            $this->image = imagecreatefrompng($filename);
        }
    }
    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng($this->image, $filename);
        }
        if ($permissions != null) {

            chmod($filename, $permissions);
        }
    }
    function output($image_type = IMAGETYPE_JPEG)
    {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng($this->image);
        }
    }
    function getWidth()
    {

        return imagesx($this->image);
    }
    function getHeight()
    {

        return imagesy($this->image);
    }
    function resizeToHeight($height)
    {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }

    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->
            getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
    function maxWidth($width)
    {
        if ($this->getWidth() > $width) {
            $this->resizeToWidth($width);
        }
    }
    function maxHeight($height)
    {
        if ($this->getHeight() > $height) {
            $this->resizeToHeight($height);
        }
    }
    function crop($crop = 'square', $percent = false)
    {
        list($w_i, $h_i, $type) = getimagesize($this->image);
        if (!$w_i || !$h_i)
            return;

        $types = array(
            '',
            'gif',
            'jpeg',
            'png');
        $ext = $types[$type];
        if ($ext) {
            $func = 'imagecreatefrom' . $ext;
            $img = $func($this->image);
        } else
            return;

        if ($crop == 'square') {
            $min = ($w_i > $h_i) ? $h_i : $w_i;
            $w_o = $h_o = $min;
            // Выравнивание по центру:
            $x_o = intval(($w_i - $min) / 2);
            $y_o = intval(($h_i - $min) / 2);
            /*
            // Выравнивание по правой стороне
            $x_o = $w_i - $min;
            // Выравнивание по низу
            $y_o = $h_i - $min;
            // Выравнивание по левой стороне
            $x_o = 0;
            // выравнивание по верху
            $y_o = 0;
            */
        } else {
            list($x_o, $y_o, $w_o, $h_o) = $crop;
            if ($percent) {
                $w_o *= $w_i / 100;
                $h_o *= $h_i / 100;
                $x_o *= $w_i / 100;
                $y_o *= $h_i / 100;
            }
            if ($w_o < 0)
                $w_o += $w_i;
            $w_o -= $x_o;
            if ($h_o < 0)
                $h_o += $h_i;
            $h_o -= $y_o;
        }
        $img_o = imagecreatetruecolor($w_o, $h_o);
        imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);

        $temp_file = tempnam(sys_get_temp_dir(), 'crop');

        if ($type == 2) {
            $res = imagejpeg($img_o, $temp_file, 100);
        } else {
            $func = 'image' . $ext;
            $res = $func($img_o, $temp_file);
        }

        if ($res)
            $this->image = $new_image;
        else
            return false;
    }

}
?>
