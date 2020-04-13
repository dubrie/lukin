<?php

class thumbnail {
	public $path

	public thumbnail() {

	}

	public function createthumbnail($name,$filename,$new_w,$new_h) {
		$system=explode(".",$name);
		if (preg_match("/jpg|jpeg|JPG|JPEG/",$system[2])){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/gif|GIF/",$system[2])){$src_img=imagecreatefromgif($name);}
		if (preg_match("/png|PNG/",$system[2])){$src_img=imagecreatefrompng($name);}
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);
		$thumb_w=$new_w;
		$thumb_h=$new_h;
		// make sure that the width is at least the thumbnail size
		if ($old_x > $new_w) {
			$ratio = $new_w/$old_x;
			$thumb_w=$new_w;
			$thumb_h=$old_y*$ratio;
		} else {
			$thumb_w=$old_x;
			$thumb_h=$old_y;
		}
		// width is good, verify that the new height is within the thumbnail size
		if ($thumb_h > $new_h) {
			$ratio = $new_h/$thumb_h;
			$thumb_h=$new_h;
			$thumb_w=($thumb_w*$ratio);
		}

		$dst_img=imagecreatetruecolor($thumb_w,$thumb_h);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
		if (preg_match("/jpg|jpeg|JPG|JPEG/",$system[2])){$src_img=imagecreatefromjpeg($name);}
		if (preg_match("/gif|GIF/",$system[2])){$src_img=imagecreatefromgif($name);}
		if (preg_match("/png|PNG/",$system[2])){$src_img=imagecreatefrompng($name);}
		if (preg_match("/png|PNG/",$system[2])) {
			imagepng($dst_img,$filename);
		} else if(preg_match("/gif|GIF/",$system[2])) {
			imagegif($dst_img, $filename);
		} else {
			imagejpeg($dst_img,$filename);
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);
	}
}

?>
