var changing = '';

function update_field_cb(retval) {
	var newval 	= retval[0];
	var id 		= retval[1];
	var column 	= retval[2];
	var table 	= retval[3];

	if(column) {
		if((column == 'photo' || column == 'artwork') && newval == '') {
			newval = 'images/nophoto.gif';
		}
		document.getElementById(table + '_' + column + '_' + id).src = newval;
		fieldname = column + '_' + id;
		document.getElementById('text_' + fieldname).style.display = 'none';
		document.getElementById('save_' + fieldname).style.display = 'none';
	}
	turnoff(fieldname);
}

function turnoff(fieldname) {
	document.getElementById('text_' + fieldname).style.display = 'none';
	document.getElementById('save_' + fieldname).style.display = 'none';
	changing = '';
}

function ClickChange(fieldname) {
	var textfield = document.getElementById('text_' + fieldname).style.display = 'inline';
	var donefield = document.getElementById('save_' + fieldname).style.display = 'inline';

	if(changing != '') {
		turnoff(changing);
	} else {
		changing = fieldname;
	}
}
function BlurChange(fieldname) {
	var split_array = fieldname.split("_");
	var id = split_array[1];
	var column = split_array[0];
	var newVal = document.getElementById('text_' + fieldname).value;

	x_update_field(newVal,fieldname,update_field_cb);
}