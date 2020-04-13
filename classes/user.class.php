<?
class User {

	var $id;
	var $username;
	var $firstname;
	var $lastname;
	var $ip1;
	var $ip2;
	var $ip3;
	var $user_photo;
	var $added;
	var $modified;
	var $password;
	var $email;
	var $passkey;
	var $uploads;
	var $navbarBG;
	var $blog;
	var $set;
	var $NumDownloads;
	var $DownloadArray;
	var $conversions;
	var $privacy;
	var $displayName;
	var $ThreeDays;
	var $jotpad;
	var $jotpad_help;
	var $probation;
	var $newsletter;
	
	function User($id=0) {
		$this->reset();
		if($id!=0) {
			$query=mysql_query("select * from user where id='".$id."'") or die("no connect: ".mysql_error());
			$data=mysql_fetch_array($query);
			$this->setInfo($data);
		}
	}

	function setInfo($data) {
		$this->id=$data['id'];
		$this->username=$data['username'];
		$this->firstname=$data['firstname'];
		$this->lastname=$data['lastname'];
		$this->ip1=$data['ip1'];
		$this->ip2=$data['ip2'];
		$this->ip3=$data['ip3'];
		$this->added=$data['added'];
		$this->modified=$data['modified'];
		$this->password=$data['password'];
		$this->email=$data['email'];
		$this->passkey=$data['passkey'];
		$this->uploads=$data['uploads'];
		$this->navbarBG=$data['navbarBG'];
		$this->blog=$data['blog'];
		$this->set=true;
		$this->privacy=$data['privacy'];
		$this->jotpad=$data['jotpad'];
		$this->jotpad_help=$data['jotpad_help'];
		$this->displayName=$data['displayName'];
		$this->user_photo=$data['user_photo'];
		$this->probation=$data['probation'];
		$this->newsletter=$data['newsletter'];
		
		$this->getDownloads();
		$this->getConversions();
	}

	function getFirstname($userid=0) {
		if($userid == 0) {
			$userid = $this->id;
		}

		$fname = mysql_fetch_array(mysql_query("select firstname from user where id = '".$userid."'"));
		return $fname['firstname'];
	}

	function lookupFirstname($userid=0) {

		if($userid == 0) {
			return 'Lukin';
		} else {
			return trim($this->getFirstname($userid));
		}
	}

	function getConversions($userid=0) {
		if($userid == 0) {
			$userid = $this->id;
		}
		$conversionQuery = "select id from uploads where user_id = ".$userid." and status = 3";
		$conversionResult = mysql_query($conversionQuery) or (error_log("could not get a list of conversions: ".mysql_error()));
		if($conversionResult) {
			$this->conversions = mysql_num_rows($conversionResult);
		}

		return $this->conversions;
	}
	
	function lastLogin() {
		
		$lastLogin = mysql_query("select entry from logins where user_id = ".$this->id." order by entry DESC limit 1,1") or error_log("can't get last login (".$this->id."): ".mysql_error());
		if(mysql_num_rows($lastLogin) > 0) {
			$row = mysql_fetch_array(mysql_query("select entry from logins where user_id = ".$this->id." order by entry DESC limit 1,1"));
			return '<span class="lastLogin">last login: '.$this->prettyDate($row['entry'],'F j, Y H:i:s').'</span>';
		} else {
			return '';
		}	
	}

	function getDownloads($userid=0) {
		if($userid == 0) {
			$userid = $this->id;
		}
		$downloadQuery = "select * from downloads where user_id = ".$userid." order by download_date DESC";
		$downloadResult = mysql_query($downloadQuery) or (error_log("could not get a list of downloads: ".mysql_error()));
		$this->NumDownloads = mysql_num_rows($downloadResult);
		while ($row = mysql_fetch_array($downloadResult) ) {
			$this->DownloadArray[] = $row;
		}
		return $this->NumDownloads;
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

	function AJAXinputField($name) {
		echo '<span id="click_'.$name.'" class="AJAXinput" onClick="ClickChange(\''.$name.'\');">'.$this->$name.'</span><input type="textfield" name="'.$name.'" value="'.$this->$name.'" onBlur="BlurChange(\''.$name.'\');" id="text_'.$name.'" style="display:none;"><span id="done_'.$name.'" class="donebutton" onClick="BlurChange(\''.$name.'\');" style="display:none;">SAVE</span><br>
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

	function uploadBox() {
		return '
		<div id="image_uploadBox" style="width:100%; display:none; padding: 5px;">
		<form action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="sub" value="yes">
		<table>
			<tr>
				<td class="account_info_title">Upload:</td>
				<td><input name="user_photo_upload" type="file" size="12" style="font-family: Verdana; font-size:12px; color:#004488; background: #ffff99; border: 1px solid #eeff00;"></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Upload Image" style="font-family:Verdana; font-size: 12px; color:#000000; border: 1px solid #eeff00;"> <input type="button" value="Cancel" style="font-family: Verdana; font-size: 12px; color:#000000; border: 1px solid #eeff00;" onClick="if(document.getElementById(\'image_uploadBox\').style.display == \'none\'){document.getElementById(\'image_uploadBox\').style.display=\'inline\';}else{document.getElementById(\'image_uploadBox\').style.display=\'none\';}"></td>
			</tr>
		</table>
		</form>
		</div>
		';
	}

	function showThumbnail($width="50",$userid=0,$params="") {
		$photo = $this->user_photo;
		if($userid != 0) {
			$result = mysql_fetch_array(mysql_query("select user_photo from user where id = '".$userid."'"));
			$photo = $result['user_photo'];
		}

		if(trim($photo) != '') {
			$newh=50;
			list($oldw,$oldh)=getimagesize('/var/www/'.$photo);
			$newH=($oldh/$oldw)*$width;
		} else {
			$photo = 'nouser.gif';
		}
		return '<img src="'.$photo.'" width="'.$width.'" height="'.$newH.'" '.$params.'>';
	}

	function display($form=false) {
		$vars = get_class_vars(get_class($this));
		foreach ($vars as $name => $val) {
			if($name != 'set' && $name != 'password' && $name != 'passkey' && !strstr($name,'ip') && $name != 'navbarBG' && $name != 'uploads' && $name != 'NumDownloads' && $name != 'DownloadArray' && $name != 'conversions' && $name != 'ThreeDays' && $name != 'jotpad' && $name != 'jotpad_help' && $name != 'probation' && $name != 'newsletter') {
				if($this->$name == '' && $name != 'user_photo') {
					$this->$name = '&nbsp; &nbsp; &nbsp;';
				}
				$showName = $name;
				if($name == 'displayName') {
					$showName = 'display name';
				}
				if($name == 'user_photo') {
					$showName = 'user photo<br><font style="font-size:9px; color:#AAAAAA;">(Click image to change)</font>';
				}
				
				echo '<tr><td class="account_info_title">';
				echo '<span class="accountInfoTitle">'.$showName.' </span></td>';
				if($name != 'id' && $name != 'username' && $name != 'added' && $name != 'modified' && $name != 'privacy' && $name != 'user_photo') {
					// echo AJAX change field values
					echo '<td class="account_info_data_change">';
					if($name == 'display name') {
						$name = 'displayName';
					}
					$this->AJAXinputField($name);
					echo '</td>';

				} else if($name == 'privacy') {
					echo '<td class="account_info_data_change">';
					$this->AJAXdropdownList($name,array('public','private'));
					echo '</td>';
				} else if($name == 'modified' || $name == 'added') {
					echo '<td class="account_info_data_nochange"><span id="'.$name.'">'.date("F j, Y g:i:sA",strtotime($this->$name)).'</span></td>';	
				} else if($name == 'user_photo') {
					$width = 50;
					$newH  = 50;
					if(trim($this->$name) == '') {
						$this->$name = 'nouser.gif';
					} else {
						list($oldW,$oldH)=getimagesize('/var/www/'.$this->$name);
						$newH=($oldH/$oldW)*$width;
					}
					echo '<td class="account_info_data_change"><div width="100%"><span id="'.$name.'">'.$this->showThumbnail(50,'','onclick="if(document.getElementById(\'image_uploadBox\').style.display == \'none\'){document.getElementById(\'image_uploadBox\').style.display=\'inline\';}else{document.getElementById(\'image_uploadBox\').style.display=\'none\';}"').'</span></div>'.$this->uploadBox().'</td>';
				} else {
					// echo just the value, not changing
					echo '<td class="account_info_data_nochange"><span class="accountInfoValue">'.$this->$name.'</span></td>';
				}
				echo '</tr>';
			}
		}

		// output total uploads and downloads
		echo '<tr><td class="account_info_title">uploads</td><td class="account_info_data_nochange">'.$this->uploads.'</td></tr>';
		echo '<tr><td class="account_info_title">downloads</td><td class="account_info_data_nochange">'.$this->NumDownloads.'</td></tr>';
		echo '<tr><td class="account_info_title">conversions</td><td class="account_info_data_nochange">'.$this->conversions.'</td></tr>';
	}

	function getLatestAlbums($userid=0) {
		if($userid == 0) {
			$userid = $this->id;
		}

		
	}

	function prettyDate($date,$format='') {

		if($format == '') {
			$format = 'F j, Y';
		}
		$spaces = explode(" ",$date);
		$debris = explode('-',$spaces[0]);
		$hour = 0;
		$minute = 0;
		$second = 0;
		if(isset($spaces[1])) {
			$time_debris = explode(":",$spaces[1]);
			$hour = $time_debris[0];
			$minute = $time_debris[1];
			$second = $time_debris[2];
		}

		$pretty = date($format,mktime($hour,$minute,$second,$debris[1],$debris[2],$debris[0]));

		return $pretty;
	}

	function displayAllUsers() {
		/*

		var $firstname;
		var $lastname;
		var $added;
		var $modified;
		var $email;
		var $uploads;
		var $blog;
	
		*/
		
		// get list of users
		$usersQuery = mysql_query("select * from user where privacy = 'public' order by displayName") or die ("could grab users: ".mysql_error());

		if($this->privacy == 'private') {
			// customer's privacy is set to private, display notice
			$content = 'Don\'t see yourself here?  You are currently set as <b>private</b> user<br><a href="account_info.php">Change that here</a>';
		} else {
			$content = 'Do you like dark, empty rooms?  Don\'t want others to see you here?<br><a href="account_info.php">Change your privacy here</a>';
		}

		echo '<div id="privacy_notice">
			'.$content.'
		</div>';

		while($row = mysql_fetch_array($usersQuery) ) {

			$row['NumDownloads'] = $this->getDownloads($row['id']);
			$row['conversions']  = $this->getConversions($row['id']);

			if($this->id == $row['id']) {
				echo '<div class="its_you">';
			} else {
				echo '<div class="other_user" style="border: 1px solid '.$row['navbarBG'].';">';
			}

			// resize image if necessary
			if($row['user_photo'] != '') {
				list($oldW,$oldH)=getimagesize('/var/www/'.$row['user_photo']);
				if($oldW > 40) {
					$newW=40;
					$newH=($oldH/$oldW)*$newW;
				} else {
					$newW=$oldW;
					$newH=$oldH;
				}
			
				echo '<img align="absmiddle" width="'.$newW.'" height="'.$newH.'" src="'.$row['user_photo'].'" onClick="showPopup(\''.$row['user_photo'].'\',\''.$row['id'].'\');"> 
				<div class="popover_div" id="popover_div_'.$row['id'].'" style="display: none; border: 2px dashed '.$row['navbarBG'].';">
				<div style="text-align: right"><span onClick="showPopup(this,\''.$row['id'].'\');" style="cursor: pointer;"><b>[x] Close</b></span></div>
				<div class="popover_div_content" id="popover_div_content_'.$row['id'].'" style="display: none;"></div>
				</div>
				&nbsp;';
			}
			echo '<span class="user_fullname">'.$row['displayName'].' </span>&nbsp; <span id="expand_closeuser'.$row['id'].'" onClick="switcharoo(\'user'.$row['id'].'\');" class="expander">[+] expand</span><br>';
			
			echo '<div id="user'.$row['id'].'" style="display:none;" class="user_details">';
		
			echo '<div class="user_personal_info">';
			if(($row['firstname'].' '.$row['lastname']) != $row['displayName']) {
				echo '<span class="user_personal"><span class="user_personal_title">name:</span> '.$row['firstname'].' '.$row['lastname'].'</span><br>';
			}
			if($row['email'] != '') {
				echo '<span class="user_personal"><span class="user_personal_title">email:</span> <a href="mailto:'.$row['email'].'">'.$row['email'].'</a></span><br>';
			}
			if(trim($row['blog']) != '') {
				echo '<span class="user_personal"><span class="user_personal_title">blog:</span> <a href="'.$row['blog'].'" target="_blank">'.$row['blog'].'</a></span><br>';
			}
			echo '</div>';
			echo '<div class="user_data_section">';
			$debris=explode(' ',$row['added']);
			$added = $debris[0];
			echo '<span class="user_data">member since: '.$this->prettyDate($added).'</span><br>';

			if($row['modified'] != $row['added'] and $row['modified'] != '0000-00-00 00:00:00') {
				$debris=explode(' ',$row['modified']);
				$moded = $debris[0];
				echo '<span class="user_data">info last updated: '.$this->prettyDate($moded).'</a></span><br>';
			}

			
			if($row['NumDownloads'] == 0) {
				$UDratio = $row['uploads'];
			} else {
				$UDratio = ($row['uploads']/$row['NumDownloads']) * 100;
			}
			if($row['uploads'] == 0) {
				$CUratio = $row['conversions'];
			} else {
				$CUratio = ($row['conversions']/$row['uploads']) * 100;
			}
			echo '<span class="user_data">usage ratio: '.$row['uploads'].'/'.$row['NumDownloads'].' <span class="ratio_val">'.number_format($UDratio, 2).' %</span>  (uploads/downloads) </span><br>';
			echo '<span class="user_data">conversion ratio: '.$row['conversions'].'/'.$row['uploads'].' <span class="ratio_val">'.number_format($CUratio, 2).' %</span> (conversions/uploads) </span><br>';

			// display total thumbsup
			$thumbsUpTotal = mysql_fetch_array(mysql_query("select count(*) as thumbs from thumbsup where user_id = ".$row['id'])) or error_log("could not get total thumbs for user: ".mysql_error());

			echo '<span class="user_data">thumbsup\'d: '.$thumbsUpTotal['thumbs'].'</span>';

			// find suggestions (if any)
			$suggestionsQuery = mysql_query("select id, type, user_id, similar, suggestion_id from suggestions where user_id = ".$row['id']) or error_log("can't get suggestion: ".mysql_error());
			$suggestionString = '';
			if(mysql_num_rows($suggestionsQuery) > 0) {
				while($sugg = mysql_fetch_array($suggestionsQuery) ) {
					// lookup artist/album
					mysql_query("select name from ".$sugg['type']." where id = ".$sugg['suggestion_id']) or error_log("can't get name: ".mysql_error());
					$nameLookup = mysql_fetch_array(mysql_query("select name from ".$sugg['type']." where id = ".$sugg['suggestion_id']));

					$text = '<tr id="sugg_row'.$sugg['id'].'"><td width="15" height="10"></td><td class="suggestion_list"><b>'.$nameLookup['name'].'</b> ('.$sugg['type'].')';
					if($sugg['similar'] != '') {
						$text .= ' -- similar to: <i>'.$sugg['similar'].'</i>';
					}
					if($sugg['user_id'] == $this->id) {
						$text .= '&nbsp; <span class="delete_suggestion" onClick="deleteSuggestion('.$sugg['id'].');">delete</span>';
					}
					$text .= '</td></tr>
					';
					$suggestionString .= $text;
				
				}
			}
			if($suggestionString != '') {
				echo '<br><span class="user_data">Suggestions:</span><table cellpadding="0" cellspacing="0">'.$suggestionString.'</table>';
			}
			echo '</div>';
			echo '</div></div>';

		}

	}

	function reset() {
		$vars = get_class_vars(get_class($this));
		foreach ($vars as $name => $val) {
			$this->$name = '';
		}
		$this->set=false;
		$this->DownloadArray = array();
		$this->conversions = 0;
		$this->ThreeDays = 259200;
	}
}
?>
