<?php
//русский текст

function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

function drawImg($source, $dest)
{
	$iw = imagesx($source);
	$ih = imagesy($source);
	$w = imagesx($dest);
	$h = imagesy($dest);
	
	imagecopy($dest, $source, ($w - $iw)/2, ($h - $ih)/2, 0, 0, $iw, $ih); 
}

function colorImg($source, $color, $sourceColor = null)
{
	$iw = imagesx($source);
	$ih = imagesy($source);
	
	$c = null;
	if($sourceColor) 
	{
		$c = $sourceColor;
	}else{
		$c = imagecolorat($source, $iw/2, $ih/2);
	}
	
	$buf = html2rgb($color);
	imagecolorset($source, $c, $buf[0], $buf[1], $buf[2]);
}

function colorImgAll($source, $color)
{
	$buf = html2rgb($color);
	for($i = 1; $i< imagecolorstotal ($source); $i++)
	{
		imagecolorset($source, $i, $buf[0], $buf[1], $buf[2]);
	}
}

function imagepalettetotruecolor(&$img)
{
	if (!imageistruecolor($img))
	{
		$w = imagesx($img);
		$h = imagesy($img);
		$img1 = imagecreatetruecolor($w,$h);
		imagecolortransparent($img1, 0);
		imagecopy($img1,$img,0,0,0,0,$w,$h);
		$img = $img1;
	}
}

function imagetruecolortopalette2(&$image) 
{
    $copy = $image;
    $dx = imagesx($image);
    $dy = imagesy($image);
    $image = imagecreate($dx, $dy);
   
    imagecopy($image, $copy, 0, 0, 0, 0, $dx, $dy);
    imagedestroy($copy);
}

function drawMarker($icon, $color=null, $text=null)
{
	$im = imagecreatefrompng('design/img/marker.png');//png-8
	if($color) colorImg($im, $color);
	imagepalettetotruecolor($im);//png-24
	
	if($icon)
	{
		$icon = imagecreatefrompng($icon);//png-24
		drawImg($icon, $im);
	}
	
	if($text)
	{
		$fontSize = 10;
		$text_color = imagecolorallocate($im, 255, 255, 255);
		$bbox = imagettfbbox($fontSize, 0, 'fonts/arial.ttf', $text);
		$tw = $bbox[2];
		$th = $fontSize;
		$w = imagesx($im);
		$h = imagesy($im);
		imagefttext($im, $fontSize, 0, ($w - $tw)/2, ($h-$th)/2+$th, $text_color, 'fonts/arial.ttf',$text);	
	}
	
	imagepng($im);
	imagedestroy($im);	
}

function drawIcon($icon, $color=null)
{
	$im = imagecreatefrompng($icon);//png-24
	imagetruecolortopalette2($im, false, 255);//png-8
	imagecolortransparent($im, 0);
	imagesavealpha($im, true);
	if($color) 
	{
		colorImgAll($im, $color);
	}
	
	imagepng($im);
	imagedestroy($im);	
}

//*********************************

function test1()
{
	header("Content-type: image/png");
	drawMarker('test_data/images/14.png', '#006700');
}

function test2()
{
	header("Content-type: image/png");
	drawIcon('test_data/images/14.png', '#006700');
}

//test1();
//test2();
//**********************************

if($_GET['icon'])
{
	header("Content-type: image/png");
	drawIcon($_GET['icon'], $_GET['color']);
}


?>