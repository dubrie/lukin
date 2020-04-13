var changing = '';

function nada_cb() {
	/* Nothing to see here, move along */
}
function turnoff(fieldname) {
	document.getElementById('text_' + fieldname).style.display = 'none';
	document.getElementById('done_' + fieldname).style.display = 'none';
	changing = '';
}
function update_album_cb(retval) {
	var newval = retval[0];
	var fieldname = retval[1];

	if(newval == '') {
		newval = 'nophoto.gif';
	}

	document.getElementById('album_' + fieldname).src = newval;
	document.getElementById('text_' + fieldname).style.display = 'none';
	document.getElementById('done_' + fieldname).style.display = 'none';
	turnoff(fieldname);
}
function update_field_cb(retval) {
	var newval = retval[0];
	var id = retval[1];
	var column = retval[2];

	if(column) {
		if((column == 'photo' || column == 'artwork') && newval == '') {
			newval = 'nophoto.gif';
		}
		document.getElementById('artist_' + column + '_' + id).src = newval;
		fieldname = column + '_' + id;
		document.getElementById('text_' + fieldname).style.display = 'none';
		document.getElementById('done_' + fieldname).style.display = 'none';
	} else {
		document.getElementById('mainline' + id).innerHTML = newval;
		document.getElementById('href_' + id).innerHTML = 'edit';
		document.getElementById('text_' + id).value = newval;
		document.getElementById('text_' + id).style.display = 'none';
		document.getElementById('done_' + id).style.display = 'none';
	}
	turnoff(fieldname);
}
function download(song,user) {
	x_update_downloads(song,user,nada_cb);
}
function Expand(fieldname) {
	document.getElementById('href_' + fieldname).innerHTML = '';
	document.getElementById('mainline' + fieldname).innerHTML = '';
	var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';	
	var donefield = document.getElementById('done_' + fieldname).style.display = 'inline';

	if(changing != '') {
		turnoff(changing);
	}
	changing = fieldname;
}
function ClickChange(fieldname) {
	var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';	
	var donefield = document.getElementById('done_' + fieldname).style.display = 'inline';
	
	if(changing != '') {
		turnoff(changing);
	}
	changing = fieldname;
}
function BlurChange(fieldname) {
	var split_array = fieldname.split("_");
	var id = split_array[1];
	var column = split_array[0];
	var newVal = document.getElementById('text_' + fieldname).value;
	
	if (column != '') {
		x_update_artist(newVal,id,column,update_field_cb);
	} else {
		x_update_field(newVal,fieldname,update_field_cb);
	}
}
function AlbChange(fieldname) {
	var pieces = fieldname.split("_");
	var column = '';
	id = pieces[1];
	column = pieces[0]; 
	var newVal = document.getElementById('text_' + fieldname).value;
	x_update_album(newVal,id,update_album_cb);
}
function popupDuplicates(type,id) {
	if(type == 'artist') {
		window.open('duplicates.php','popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	} else if(type == 'editartist') {
		window.open('wikiedit.php?type=Artist&id='+id,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	} else if(type == 'editalbum') {
		window.open('wikiedit.php?type=Album&id='+id,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	} else if(type == 'editsong') {
		window.open('songwiki.php?type=Song&id='+id,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=730,height=500,screenX=50,screenY=50,top=50,left=50');
	}
}

function showPopup(type) {
	
	document.getElementById('popover_suggestion_div').style.display = 'inline';
	
	if(type == 'song') {
		document.getElementById('popover_song_suggestion_div_content').style.display = 'inline';	
	} else {
		document.getElementById('popover_suggestion_div_content').style.display = 'inline';	
	}
}

function turnoffPopup() {
	document.getElementById('popover_suggestion_div').style.display = 'none';
	document.getElementById('popover_suggestion_div_content').style.display = 'none';	
	document.getElementById('popover_song_suggestion_div_content').style.display = 'none';	
	document.getElementById('suggestion_status').style.display = 'none';
}

function suggestion_cb() {
	document.getElementById('suggestion_form').style.display = 'none';
	document.getElementById('suggestion_status').style.display = 'inline';
	document.getElementById('suggestion_status').innerHTML = 'Added!';
	setTimeout('turnoffPopup()',2000);
}

function processSuggestion(type,id) {
	var similar = document.getElementById('similar_thing').value;
	x_submit_suggestion(type,id,similar,suggestion_cb);
}
function switcharoo(divID) {
	var the_div = document.getElementById(divID);
	var the_span = document.getElementById('expand_close' + divID);
	if(the_div.style.display == 'inline') {
		the_div.style.display = 'none';
		the_span.innerHTML = '[+] details';
		
	} else {
		the_div.style.display = 'inline';
		the_span.innerHTML = '[-] less';
	}
}
function showMusicPlayer(songID,artist,album,song) {
	//alert ("song: " + songID + ", artist: " + artist + ", album: " + album + ", songname: " + song);
	var mp = document.getElementById('musicPlayer_song' + songID);
	if(mp.style.display == 'inline') {
		mp.innerHTML = '';
		mp.style.display = 'none';
	} else {
		mp.style.display = 'inline';
		mp.innerHTML = '<iframe src="http://mail.google.com/mail/html/audio.swf?audioUrl=http://lukin.kicks-ass.net/music/' + artist + '/' + album + '/' + song + '" style="width: 264px; height: 25px; border: 1px solid #aaa; padding: 2px 2px 2px 2px;" id="musicPlayer_song"></iframe>';
	}
}

function place_cb() {
	// don't care
}

function Place(item) {
	var song_order = [];
	var songlist = document.getElementById('song_sorting').childNodes;
	for(var i=0; i<songlist.length; i++) {
		song_order[i] = songlist[i].id;
	}
	x_place_item(item, song_order, place_cb);
}

function thumbsup_cb(retVal) {
	document.getElementById("thumbvotes").innerHTML = retVal;
}

function giveThumbsUp(item,type,thing,votes) {
	item.style.display = "none";
	var votesID = "thumbvotes" + thing;
	document.getElementById(votesID).innerHTML = (votes + 1);

	x_thumbsup_vote(type,thing,thumbsup_cb);
}

function playLocal(path) {
	x_play_song(path,nada_cb);
}
