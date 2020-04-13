<?

class ajaxform {

	var $onClickfunc;
	var $Savefunc;

	function ajaxform($filename) {
		$this->onClickfunc= "ClickToChange";
		$this->savefunc= "saveNewValue";


		// required SAJAX code
		sajax_init();
		$sajax_debug_mode = 0;
		sajax_export("updateField");
		sajax_handle_client_request();
	}

	function ajax_form_css() {
		echo '
	<link rel="stylesheet" href="css/ajaxform.css" type="text/css">
		';
	}

	function ajax_form_javascript() {
		echo '
	<script language="javascript" src="js/ajaxform.js">
		';
	}

	function updateField($val, $field) {
		$date = date("Y-m-d H:i:s");
		$query = "update user set ".$field." = '".$val."', modified='".$date."' where id = ".$this->id;
		$update = mysql_query($query);
		error_log("user update: ".$query);
		if($field == 'firstname') {
			$_SESSION['firstname'] = $val;
		}
	}

	function AJAXspan($name) {
		echo '<span id="click_'.$name.'" class="AJAXinput" onClick="ClickChange(\''.$name.'\');">'.$this->$name.'</span><input type="textfield" name="'.$name.'" value="'.$this->$name.'" onBlur="BlurChange(\''.$name.'\');" id="text_'.$name.'" style="display:none;"><span id="done_'.$name.'" class="donebutton" onClick="BlurChange(\''.$name.'\');" style="display:none;">SAVE</span>
		';
	
	}

	function AJAXtd($name) {
		echo '<td><span id="click_'.$name.'" class="AJAXinput" onClick="ClickChange(\''.$name.'\');">'.$this->$name.'</span><input type="textfield" name="'.$name.'" value="'.$this->$name.'" onBlur="BlurChange(\''.$name.'\');" id="text_'.$name.'" style="display:none;"><span id="done_'.$name.'" class="donebutton" onClick="BlurChange(\''.$name.'\');" style="display:none;">SAVE</span></td>
		';
	
	}

	function AJAXdropdownList($name, $listvals) {

		// radiovals is an array of all the possible radio values
	
		echo '<span id="click_'.$name.'" class="AJAXinput" onClick="ClickChange(\''.$name.'\');">'.$this->$name.'</span>';
		echo '<select name="dropdown'.$name.'" id="text_'.$name.'" size="1" style="display:none;">';
		for($i=0;$i<sizeof($listvals);$i++) {
			echo '<option value="'.$listvals[$i].'">'.$listvals[$i].'</option>
			';
			
		}
		echo '</select>
		<span id="done_'.$name.'" class="donebutton" onClick="BlurChange(\''.$name.'\');" style="display:none;">SAVE</span>	
		';
	
	}

}


?>
