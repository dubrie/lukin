<?
include_once("thumbnailImages.class.php");

$obj_img = new thumbnail_images();

$obj_img->PathImgOld = 'colorwheel_bk.jpg';
$obj_img->PathImgNew = 'new_wheel.jpg';
$obj_img->NewWidth = 100;
$obj_img->NewHeight = 75;
if (!$obj_img->create_thumbnail_images()) {
	echo "error";
} else {
	echo 'New:<img src="new_wheel.jpg"><br>
	Old: <img src="colorwheel_bk.jpg"><br>';
}
?>
