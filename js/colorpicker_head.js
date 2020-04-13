addary=new Array(255,1,1);
clrary=new Array(360);
for(i=0;i<6;i++) {
 	for(j=0;j<60;j++) {
  		clrary[60*i+j]=new Array(3);
    	for(k=0;k<3;k++) {
     		clrary[60*i+j][k]=addary[k];
       		addary[k]+=((Math.floor(65049010/Math.pow(3,i*3+k))%3)-1)*4; 
    	} 
 	}
}
// Global row value
var row = '';

function getPageCoords(element) {
	var coords = {x: 0, y: 0};
	while(element) {
		coords.x += element.offsetLeft;
		coords.y += element.offsetTop;
		element = element.offsetParent;
	}
	return coords;
}

function getOffsets(evt) {
	var target = evt.target;
	if(typeof target.offsetLeft == 'undefined') {
		target = target.parentNode;
	}
	
	var pageCoords = getPageCoords(target);
	var eventCoords = {
		x: window.pageXOffset + evt.clientX,
		y: window.pageYOffset + evt.clientY
	}
	
	var offsets = {
		offsetX: eventCoords.x - pageCoords.x,
		offsetY: eventCoords.y - pageCoords.y
	}
	
	return offsets;
}

function moved(e) {
 	
	if(window.event) {
		offx = event.offsetX;
		offy = event.offsetY;
	} else {
		offx = getOffsets(e).offsetX;
		offy = getOffsets(e).offsetY;
	}

	sx=( (document.layers) ?e.layerY:offy)-128;
   	sy=( (document.layers) ?e.layerX:offx)-128;
   	quad=new Array(-180,360,180,0);
   	xa=Math.abs(sx); 
   	ya=Math.abs(sy);
   	d=ya*45/xa;
   	if(ya>xa) {
   		d=90-(xa*45/ya);
   	}
   	deg=Math.floor( Math.abs( quad[2*( (sy<0) ?0:1 )+( (sx<0) ?0:1 )]-d ) );
   	n=0; 
   	c="000000";
   	r=Math.sqrt( (xa*xa) + (ya*ya) );
   	if(sx!=0 || sy!=0) {
    	for(i=0;i<3;i++) {
        	r2=clrary[deg][i]*r/64;
         	if(r>64) {
         		r2+=Math.floor(r-64)*2;
         	}
         	if(r2>255) {
         		r2=255;
         	}
         	n=256*n+Math.floor(r2); 
    	};
      	c=(n.toString(16)).toUpperCase();
      	while(c.length<6) {
      		c="0"+c;
      	}
   	}
   	if(document.layers) {
    	document.layers['clrdiv'].bgColor="#"+c;
   	}
   	else {
    	document.getElementById("clrdiv").style.backgroundColor="#"+c;
   	}
   	document.getElementById('hid').value="#"+c;
   	return false; 
}

function capture() {
 	if(document.layers) {
    	with(document.layers['imgdiv']) {
       		document.captureEvents(Event.MOUSEMOVE);
         	document.onmousemove=moved(Event); 
    	}
 	} else if(document.getElementById('imgdiv')) { 
 		document.getElementById('imgdiv').onmousemove=moved; 
 	} else {
 		
 	}
}

function setcolor() {
 	document.getElementById('colorwheelTD').style.backgroundColor = document.getElementById('hid').value;
 	document.getElementById('ul_account_heading').style.backgroundColor = document.getElementById('hid').value;
 	document.getElementById('saveColor').value = document.getElementById('hid').value;
 	document.getElementById('uploads_bottom_row').value = document.getElementById('hid').value;
 	document.getElementById('uploads_heading_row').value = document.getElementById('hid').value;
}

function toggleBox(rownum, title)
{
	var szDivID = 'csspopoverDiv';
	var iState = 1;
	
	row = rownum;
    if(row == 0) {
    	iState = 0;
	x_update_field(document.getElementById('saveColor').value,'navbarBG',navbar_cb);
    }
	
	if(document.layers)	   //NN4+
    {
       document.layers[szDivID].visibility = iState ? "show" : "hide";
    }
    else if(document.getElementById)	  //gecko(NN6) + IE 5+
    {
        var obj = document.getElementById(szDivID);
        obj.style.visibility = iState ? "visible" : "hidden";
    }
    else if(document.all)	// IE 4
    {
        document.all[szDivID].style.visibility = iState ? "visible" : "hidden";
    }
   	document.getElementById('hid').value="";
    	document.getElementById('csspopoverDivTitle').innerHTML = title;

}
