<?

class colorwheel {

	var $color;
	var $image;
	var $bg_image;
	var $title;

	function colorwheel() {
		$this->reset();
		$this->image = 'colorwheel.jpg';
		$this->bg_image = 'opacity_bg.gif';
		$this->title = 'lukin color';
	}

	function cssInfo() {
		echo "
	#csspopoverDiv {
		background-image:url('".$this->bg_image."');
		border: 1px solid #000000;
		position: absolute;
		top: 50px;
		left: 5px;
		z-index:99;
		visibility: hidden;
	}

	#clrdiv {
		width:25px;
		height:25px;
		z-index:7;
		position: relative;
	}

	#csspopoverDivTitle {
		font-size: 12px;
	}

	a.close_popup {
		font-size:11px;
		cursor: pointer;
	}
	a.save_popup {
		background-color: #999999;
		border: 1px solid #000000;
		padding: 1px 5px 1px 5px;
		margin-right: 5px;
		cursor: pointer;
	}
		";
	}

	function popupDiv() {
		echo '
		<div ID="csspopoverDiv" class="demo">
			<table border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td align="right" bgcolor="#FFFFFF"><a onclick="toggleBox(0,\'\');" class="save_popup">SAVE</a><a onclick="closeBox();" class="close_popup">Close [X]</a></td>
				</tr>     
				<tr>
					<td align="center" bgcolor="#FFFFFF" id="csspopoverDivTitle">text</td>
				</tr>
				<tr>
					<td bgcolor="#FFFFFF">
						<div id="imgdiv">
							<a href="#" onclick="setcolor(); return false;"><img src="'.$this->image.'" width=256 height=256 border=0></a>
						</div>
					</td>
				</tr>
				<tr>
					<td align="center" bgcolor="#FFFFFF"></td>
				</tr>
				<tr>
					<td align="center" bgcolor="#FFFFFF">
						<div id="clrdiv" style="width:100%;">
							<input type="hidden" id="hid">
						</div>
					</td>
				</tr>
			</table>
		</div>
		';
	}

	function form_value($value) {
		echo '
		<tr>
			<td class="account_info_title">'.$this->title.'</td>
			<td class="account_info_data_change">
				<div id="colorwheelTD" style="width:22px; height:22px; background-color:'.$value.';padding:0px 0px 0px 0px; border:1px solid #000000;">
				<a onclick="toggleBox(1,\'Your Navbar Color\');" href="#">
				<img src="pixel_trans.gif" width="22px" height="22px" border="0">
				</a>
				<input type="hidden" id="saveColor" value="">
				</div>
			</td>
		</tr>
		';
	}

	function setImage($path) {
		$this->image = $path;
	}
	
	function reset() {
		$this->color = '';
		$this->image = '';
		$this->bg_image = '';
		$this->title = '';
	}
}

?>
