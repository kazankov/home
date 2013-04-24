<?php
//русский текст
header("Content-type: image/png");

$txt = $_GET['text'];
$fontSize = 10;

$img = $_GET['image'];
if($img) $img="http://{$_SERVER['HTTP_HOST']}/".$img;


if(!$txt || ($txt && strlen($txt)<=2))
{
	require_once 'icon.php';
	drawMarker($img, $_GET['color'], $_GET['text']);
}else{
	$w = 20;
	$h = 20;
	if($txt){
		$w = strlen($txt)*8+5;
	}
	$im = imagecreate($w, $h);
	$background_color = imagecolorallocate($im, 255, 255, 255);
	$icon_color = imagecolorallocate($im, 255, 0, 0);
	$text_color = imagecolorallocate($im, 0, 0, 0);
	imagefilledrectangle($im, 0, 0, $w, $h, $icon_color);
	imagefttext($im, $fontSize, 0, 4, 14, $text_color, 'fonts/arial.ttf',$txt);
	imagepng($im);
	imagedestroy($im);
}
?>