/**
 *  JavaScript Cross-Browser Tooltips
 *
 *  copyright (c) Stefan Gabos
 *
 *  This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivs 2.5 License.
 *  To view a copy of this license, visit {@link http://creativecommons.org/licenses/by-nc-nd/2.5/} or send a letter to
 *  Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 */

/**
 *  The speed with which a tooltip should fade in.
 *  Can be any value between 1 and 100
 */
var JavaScriptTooltips_fadeInSpeed;

/**
 *  The speed with which a tooltip should fade out.
 *  Can be any value between 1 and 100
 */
var JavaScriptTooltips_fadeOutSpeed;

/**
 *  How long (in milliseconds) should the script wait onmouseout,
 *  before starting to fade out a tooltip
 */
var JavaScriptTooltips_fadeOutDelay;

/**
 *  How long (in milliseconds) should the script wait onmouseover,
 *  before starting to fade in a tooltip
 */
var JavaScriptTooltips_fadeInDelay;

/**
 *  A percentual value representing the maximum opacity a tooltip can get
 */
var JavaScriptTooltips_maximumOpacity;

/**
 *  How far (in pixels), horizontally, should the tooltip be positioned relatively to the mouse cursor
 */
var JavaScriptTooltips_offsetX;

/**
 *  How far (in pixels), vertically, should the tooltip be positioned relatively to the mouse cursor
 */
var JavaScriptTooltips_offsetY;

/**
 *  Array that keeps track of created tooltips
 *
 *  @access private
 */
function dataArray(opacity, timer, context, onmouseoverFunction)
{

    this.opacity = opacity;
    this.timer = timer;
    this.context = context;
    this.onmouseoverFunction = onmouseoverFunction;

}

var tooltipData = new Array();


/**
 *  Displays a tooltip
 *
 *  No onmouseout required. The scripts takes care of fading the tooltip out once you mouseout.
 *
 *  @param  context     context     This MUST always be the <b>this</b> keyword
 *
 *  @param  content     content     The content to be displayed in the tooltip
 *
 *                                  If you are using this script as a stand-alone script (not with it's related PHP class
 *                                  don't forget to delete the "unescape" word in the first row of the function)
 *
 *  @param  event       event       This MUST always be <b>event</b>
 *
 *  @param  string      tooltipID   This is reserved for the script. Do not specify it yourself!!!
 *
 *  @return void
 */
function show(context, content, event, tooltipID)
{

    // unescape the content sent by the related PHP class
    // DELETE THE "unescape" WORD IF YOU'RE USING THIS SCRIPT AS A STAND-ALONE SCRIPT (NOT WITH IT'S RELATED PHP CLASS!)
    content = unescape(content);

    // if tooltipID parameter is not specified
    // means that it wasn't created yet
    if (tooltipID == undefined) {
    
        // generate a 12 characters long random name for use as new tooltip id
        var tooltipID = '';
        var letters = 'abcdefghijklmnopqrstuvwxyz';
        var numbers = '0123456789';
        for (i=0; i<12; i++) {
            tooltipID += Math.floor(Math.random() * 2) == 0 ? letters.charAt(Math.floor(Math.random() * 26)) : numbers.charAt(Math.floor(Math.random() * 10));
        }

        // create a new entry in the tooltipData array
        tooltipData[tooltipID] = new dataArray(0, false, context, context.onmouseover);

        // if browser is Internet Explorer -> note that because Opera identifies itself as Opera we make one more additional check
        if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {
        
            // we create an IFRAME element which will be always positioned behind the tooltip
            // to cover SELECT elements - which, by default in Internet Explorer, are always
            // above all elements in a page
            var newIFrame = document.createElement("iframe");

            // set the IFRAME's default properties
            newIFrame.id = tooltipID + "_iframe";
            newIFrame.src = "about:blank";
            newIFrame.frameBorder = "0";
            newIFrame.scrolling = "no";
            newIFrame.style.position = "absolute";
            newIFrame.style.display = "none";

            // and actually add the element to the body of the document
            document.body.appendChild(newIFrame);
                
        }
        
        // create a new DIV element as a container for the tooltip
        var newTooltip = document.createElement("div");

        // set the DIV's default properties
        newTooltip.id = tooltipID;
        newTooltip.style.position = "absolute";
        newTooltip.style.display = "none";
        newTooltip.style.width = "auto";
        newTooltip.style.height = "auto";

        // and actually add the element to the body of the document
        document.body.appendChild(newTooltip);

        // set the onmouseout event for the caller element to hide the tooltip
        // this is how we handle "automatically" the fade out of the tooltip :)
        context.onmouseout = function() { hide(tooltipID); };
        
        // set the onmouseover function for the caller element
        // this time adding the tooltipID as fourth parameter so if we move out of the element and quicly over again,
        // the script to know not to create a new tooltip but instead to continue fading in the one already created
        context.onmouseover = function() { show(context, content, event, tooltipID); };

        // set some events for the tooltip so that the tooltip will not fade out accidentally
        document.getElementById(tooltipID).onmouseover = function () { show(context, content, event, tooltipID); };
        document.getElementById(tooltipID).onmouseout = function () { hide(tooltipID); };
        document.getElementById(tooltipID).onclick = function () { hide(tooltipID); };

        // if browser is Internet Explorer -> note that because Opera identifies itself as Opera we make one more additional check
        if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {

            // set some events for the IFRAME element so that the tooltip will not fade out accidentally
            document.getElementById(tooltipID + "_iframe").onmouseover = function () { show(context, content, event, tooltipID); };
            document.getElementById(tooltipID + "_iframe").onmouseout = function () { hide(tooltipID); };
            document.getElementById(tooltipID + "_iframe").onclick = function () { hide(tooltipID); };

        }

        // add content to tooltip
        document.getElementById(tooltipID).innerHTML = content;

        // get mouse coordinates
        var posx = 0;
        var posy = 0;

        if (event.pageX || event.pageY) {

        	posx = event.pageX;
        	posy = event.pageY;

        } else if (event.clientX || event.clientY) {

        	posx = event.clientX + document.body.scrollLeft;
        	posy = event.clientY + document.body.scrollTop;

        }

        // move tooltip position relative to mouse cursor by specified offsetX and offsetY
        with (document.getElementById(tooltipID).style) {

            left = posx + JavaScriptTooltips_offsetX;
            top  = posy + JavaScriptTooltips_offsetY;

        }

    // if tooltipID is specified
    } else {
    
        // and only if display is 'none'
        if (document.getElementById(tooltipID).style.display == 'none') {
        
            // get mouse coordinates
            var posx = 0;
            var posy = 0;

            if (event.pageX || event.pageY) {

            	posx = event.pageX;
            	posy = event.pageY;

            } else if (event.clientX || event.clientY) {

            	posx = event.clientX + document.body.scrollLeft;
            	posy = event.clientY + document.body.scrollTop;

            }

            // move tooltip position relative to mouse cursor by specified offsetX and offsetY
            with (document.getElementById(tooltipID).style) {

                left = posx + JavaScriptTooltips_offsetX;
                top  = posy + JavaScriptTooltips_offsetY;

            }
        }
        
    }
    
    // start fading in the tooltip after waiting for JavaScriptTooltips_fadeInDelay milliseconds
    tooltipData[tooltipID].timer = setTimeout(fadeIn(tooltipID), JavaScriptTooltips_fadeInDelay);

}

/**
 *  Hides a tooltip
 *
 *  @access private
 */
function hide(tooltipID)
{

    // if tooltip has reached the maximum allowed opacity
    if (tooltipData[tooltipID].opacity >= JavaScriptTooltips_maximumOpacity) {
    
        // start fading it out - after waiting for JavaScriptTooltips_fadeOutDelay milliseconds
        tooltipData[tooltipID].timer = setTimeout(fadeOut(tooltipID), JavaScriptTooltips_fadeOutDelay);
        
    // if tooltip has not yet reached the maximum allowed opacity
    } else {
    
        // start fading it out right away
        tooltipData[tooltipID].timer = setTimeout(fadeOut(tooltipID), 1);
        
    }
    
}

/**
 *  Enabler function for the _fadeIn method
 *  in order to pass context to the setTimeout function
 *
 *  It is called by the 'show()' function
 *
 *  @access private
 */
function fadeIn(tooltipID)
{

    // clear any previously set timer for this tooltip
    clearTimeout(tooltipData[tooltipID].timer);

    // return prepared string
    return '_fadeIn(\'' + tooltipID + '\')';
    
}

/**
 *  Fades in a tooltip
 *
 *  @access private
 */
function _fadeIn(tooltipID)
{

    // function will be called recursively until the tooltip reaches the maximum allowed opacity
    if (tooltipData[tooltipID].opacity <= JavaScriptTooltips_maximumOpacity) {

        // if browser is Internet Explorer -> note that because Opera identifies itself as Opera we make one more additional check
        if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {

            // set the opacity level for the IFRAME element, too
            setOpacity(tooltipID + "_iframe", tooltipData[tooltipID].opacity);
            
        }
        
        // set the opacity level for the tooltip
        setOpacity(tooltipID, tooltipData[tooltipID].opacity);

        // increase the opacity with the specified amount
        tooltipData[tooltipID].opacity += JavaScriptTooltips_fadeInSpeed;

        // recursively call itself
        tooltipData[tooltipID].timer = setTimeout(fadeIn(tooltipID), 1);

    // if tooltip has reached the maximum allowed opacity
    } else {
    
        // if the browser is Internet Explorer
        if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {
        
            // set the opacity level for the IFRAME element, too
            setOpacity(tooltipID + "_iframe", JavaScriptTooltips_maximumOpacity);
            
        }
        
        // set the opacity level for the tooltip
        setOpacity(tooltipID, JavaScriptTooltips_maximumOpacity);
    
        // clear the timeout
        clearTimeout(tooltipData[tooltipID].timer);
    
    }

    // display the tooltip
    document.getElementById(tooltipID).style.display = 'block';
    
    // we decrease every tooltip's z-index -> this is a hack for Opera
    for (var i in tooltipData) {
    
        document.getElementById(i).style.zIndex -= 1;

    }
    
    // and make the current one have the highest z-index
    document.getElementById(tooltipID).style.zIndex = 9999;

    // we strip "px" from the left and top position of the tooltip
    leftPos = document.getElementById(tooltipID).style.left.replace(/px/g, '');
    topPos = document.getElementById(tooltipID).style.top.replace(/px/g, '');
    
    // get the width and height of the tooltip
    width = document.getElementById(tooltipID).offsetWidth;
    height = document.getElementById(tooltipID).offsetHeight;

    // cross-browser detection of browser's window width and height
    // this code is from http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
    // and is written by Mark Wilton-Jones
    if (typeof( window.innerWidth ) == 'number') {
        //Non-IE
        screenWidth = window.innerWidth;
        screenHeight = window.innerHeight;
    } else if(document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
        //IE 6+ in 'standards compliant mode'
        screenWidth = document.documentElement.clientWidth;
        screenHeight = document.documentElement.clientHeight;
    } else if(document.body && ( document.body.clientWidth || document.body.clientHeight)) {
        //IE 4 compatible
        screenWidth = document.body.clientWidth;
        screenHeight = document.body.clientHeight;
    }

    // cross-browser detection of browser's scroll values
    // this code is from http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
    // and is written by Mark Wilton-Jones
    if (typeof(window.pageYOffset) == 'number') {
        //Netscape compliant
        scrollY = window.pageYOffset;
        scrollX = window.pageXOffset;
    } else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
        //IE6 standards compliant mode
        scrollY = document.documentElement.scrollTop;
        scrollX = document.documentElement.scrollLeft;
    } else {
        //DOM compliant
        scrollY = document.body.scrollTop;
        scrollX = document.body.scrollLeft;
    }
    
    // adjust the tooltip's position so that it is in the visible part of the browser's window
    
    if (eval(leftPos) < eval(scrollX)) {
    
        // add "px" for Firefox compatibility
        document.getElementById(tooltipID).style.left = scrollX + "px";
        
    }
    
    if (eval(topPos) < eval(scrollY)) {
    
        // add "px" for Firefox compatibility
        document.getElementById(tooltipID).style.top = scrollY + "px";
        
    }

    if (eval(leftPos) + eval(width) > eval(screenWidth) + eval(scrollX)) {
        document.getElementById(tooltipID).style.left = eval(screenWidth + scrollX - width) + "px";
    }

    if (eval(topPos) + eval(height) > eval(screenHeight) + eval(scrollY)) {
        document.getElementById(tooltipID).style.top = eval(screenHeight + scrollY - height) + "px";
    }

    // if browser is Internet Explorer -> note that because Opera identifies itself as Opera we make one more additional check
    if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1 && document.getElementById(tooltipID + "_iframe").style.display != 'block') {

        // move the IFRAME behind the tooltip, set it to the same size and show it
        with (document.getElementById(tooltipID + "_iframe").style) {

            left = document.getElementById(tooltipID).style.left;
            top = document.getElementById(tooltipID).style.top;
            width = document.getElementById(tooltipID).offsetWidth;
            height = document.getElementById(tooltipID).offsetHeight;
            display = 'block';

        }
        
    }
    
}

/**
 *  Enabler function for the _fadeOut method
 *  in order to pass context to the setTimeout function
 *
 *  It is called by the 'hide()' function
 *
 *  @access private
 */
function fadeOut(tooltipID)
{

    // clear any previously set timer for this tooltip
    clearTimeout(tooltipData[tooltipID].timer);
    
    // return prepared string
    return '_fadeOut(\'' + tooltipID + '\')';
}

/**
 *  Fades out a tooltip
 *
 *  @access private
 */
function _fadeOut(tooltipID)
{

    // function will be called recursively until the tooltip becomes 0% opaque
    if (tooltipData[tooltipID].opacity >= 0) {

        // if the browser is Internet Explorer
        if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {

            // set the opacity level for the IFRAME element, too
            setOpacity(tooltipID + "_iframe", tooltipData[tooltipID].opacity);

        }

        // set the opacity level for the tooltip
        setOpacity(tooltipID, tooltipData[tooltipID].opacity);

        // decrease the opacity with the specified amount
        tooltipData[tooltipID].opacity -= JavaScriptTooltips_fadeOutSpeed;

        // recursively call itself
        tooltipData[tooltipID].timer = setTimeout(fadeOut(tooltipID), 1);

    // if tooltip becomes 0% opaque (100% transparent)
    } else {

        // clear the timeout
        clearTimeout(tooltipData[tooltipID].timer);
        
        // hide the tooltip
        document.getElementById(tooltipID).style.display = 'none';

        // if the browser is Internet Explorer
        if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {

            // also hide the IFRAME element
            document.getElementById(tooltipID + "_iframe").style.display = "none";
            
        }

    }
    
}

/**
 *  Sets opacity
 *
 *  @param  integer     value   a value between 0-100 indicating the opacity of the layer
 *                              0 is fully transparent, 100 is fully opaque
 *
 *  @return void
 *
 *  @access private
 */
function setOpacity(tooltipID, value)
{

    // if browser is Konqueror
    if (navigator.vendor == "KDE") {

        // set opacity this way
        document.getElementById(tooltipID).style.KHTMLOpacity = value / 100;

    // if browser is a Netscape clone
    } else if (navigator.appName == "Netscape") {

        // set opacity this way
        document.getElementById(tooltipID).style.MozOpacity = value / 100;

    // if browser is Internet Explorer
    } else if (navigator.appName == "Microsoft Internet Explorer" && navigator.userAgent.indexOf("Opera") == -1) {

        // set opacity this way
        document.getElementById(tooltipID).style.filter = "alpha(opacity=" + value + ")";

    // anything else
    } else {

        // set opacity this way
        document.getElementById(tooltipID).style.opacity = value / 100;

    }

}
