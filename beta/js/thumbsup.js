function thumbsup_cb(retVal) {
	document.getElementById("thumbvotes").innerHTML = retVal;
}


function giveThumbsUp(item,type,thing,votes) {
	item.style.display = "none";
	var votesID = "thumbvotes" + thing;
	document.getElementById(votesID).innerHTML = (votes + 1);

	x_thumbsup_vote(type,thing,thumbsup_cb);
}