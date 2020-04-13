<?php

/**
 *  A class used to generate JavaScript tooltips.
 *
 *  -   works on all major browsers (no transparency on Opera, though)
 *  -   in Internet Explorer the tooltips show above HTML SELECT elements as they should
 *  -   the appearance of the tooltips is template driven
 *  -   only onmouseover/onmousemove events need to be sepcified - no onmouseout!
 *
 *  See the manual for more info.
 *
 *  Though the JavaScript code is mainly written by myself there are some bits taken from the Internet:
 *
 *  -   9 lines of code to find the cursor position even if the page was scrolled - I really can't remember
 *      where I have this code from...
 *  -   16 lines of code for a cross-browser solution to determine the width and height of the browser's
 *      window and the scrolling values - this one I have from
 *      {@link http://www.howtocreate.co.uk/tutorials/javascript/browserwindow} and is written by
 *      Mark Wilton-Jones - thanks!
 *
 *  This work is licensed under the Creative Commons Attribution-NonCommercial-NoDerivs 2.5 License.
 *  To view a copy of this license, visit {@link http://creativecommons.org/licenses/by-nc-nd/2.5/} or send a letter to
 *  Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 *
 *  @name       tooltips
 *  @package    tooltips
 *  @version    1.0 BETA (last revision: September 25, 2006)
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 Stefan Gabos
 *  @example    example.php
 */

error_reporting(E_ALL);

class tooltips
{

    /**
     *  The speed with which a tooltip should fade in.
     *  Can be any value between 1 and 100
     *
     *  Default is 10
     *
     *  @var integer
     */
    var $fadeInSpeed = 10;
    
    /**
     *  The speed with which a tooltip should fade out.
     *  Can be any value between 1 and 100
     *
     *  Default is 10
     *
     *  @var integer
     */
    var $fadeOutSpeed = 10;

    /**
     *  How long (in milliseconds) should the script wait onmouseout,
     *  before starting to fade out a tooltip
     *
     *  Default is 500
     *
     *  @var integer
     */
    var $fadeOutDelay = 10;

    /**
     *  How long (in milliseconds) should the script wait onmousemove/onmouseover,
     *  before starting to fade in a tooltip
     *
     *  Default is 0
     *
     *  @var integer
     */
    var $fadeInDelay = 0;

    /**
     *  A percentual value representing the maximum opacity a tooltip can get
     *
     *  Default is 80
     *
     *  @var integer
     */
    var $maximumOpacity = 80;

    /**
     *  How far (in pixels), horizontally, should the tooltip's top-left corner be positioned relatively to the mouse cursor
     *
     *  A positive number means "how far to the right" while a negative number means "how far to the left".
     *
     *  <i>Note that, if needed, the script will automatically adjust the tooltip's horizontal position so
     *  that it will always be inside the visible area of the browser's window!</i>
     *
     *  Default is 10
     *
     *  @var integer
     */
    var $offsetX = 0;

    /**
     *  How far (in pixels), vertically, should the tooltip's top-left corner be positioned relatively to the mouse cursor
     *
     *  A positive number means "how far to the bottom" while a negative number means "how far to the top".
     *
     *  <i>Note that, if needed, the script will automatically adjust the tooltip's vertical position so
     *  that it will always be inside the visible area of the browser's window!</i>
     *
     *  Default is 10
     *
     *  @var integer
     */
    var $offsetY = 0;

    /**
     *  Template folder to use
     *  Note that only the folder of the template you wish to use needs to be specified. Inside the folder
     *  you <b>must</b> have the <b>template.xtpl</b> file which will be automatically used
     *
     *  default is "default"
     *
     *  @var   string
     */
    var $template = "default";

    /**
     *  In case of an error read this property's value to find out what went wrong
     *
     *  possible error values are:
     *
     *      - 1:  XTemplate class could not be found
     *      - 2:  template file could not be found
     *      - 3:  you must call the init() method first
     *
     *  default is 0
     *
     *  @var integer
     */
    var $error = 0;

    /**
     *  Constructor of the class
     *
     *  @access private
     */
    function tooltips()
    {

        // Sets default values of the class' properties
        // We need to do it this way for the variables to have default values PHP 4
        // public properties
        $this->fadeInSpeed = 10;
        $this->fadeOutSpeed = 10;
        $this->fadeOutDelay = 10;
        $this->fadeInDelay = 0;
        $this->maximumOpacity = 80;
        $this->offsetX = 20;
        $this->offsetY = 20;
        $this->template = "default";
        $this->error = 0;

        // get the absolute path of the class. any further includes rely on this
        // and (on a windows machine) replace \ with /
        $this->absolutePath = preg_replace("/\\\/", "/", dirname(__FILE__));

        // get the relative path of the class. ( by removing $_SERVER["DOCUMENT_ROOT"] from the it)
        // any HTML reference (to scripts, to stylesheets) in the template file should rely on this
        $this->relativePath = preg_replace("/".preg_replace("/\//", "\/", $_SERVER["DOCUMENT_ROOT"])."/i", "", $this->absolutePath);
        
    }
    
    /**
     *  Initializes the tooltips.
     *
     *  <b>You need to place a call to this method in the <BODY> of your HTML as this method parses the "init"
     *  section of the template which makes a link to the stylesheet and to the javascript file</b>
     *
     *  @return boolean     Returns TRUE on success or FALSE on failure
     */
    function init()
    {
    
        // if the xtemplate class is not already included
        if (!class_exists("XTemplate")) {

            // if the file exists
            if (file_exists($this->absolutePath."/includes/class.xtemplate.php")) {

                // include the xtemplate class
                require_once "classes/includes/class.xtemplate.php";

            // if the file does not exists
            } else {

                // save the error level and stop the execution of the script
                $this->error = 1;
                return false;

            }

        }

        // if specified template file exists
        if (file_exists($this->absolutePath."/templates/".$this->template."/template.xtpl")) {
        
            // create a new XTemplate object using the specified template
            $this->xtpl = new XTemplate("classes/templates/".$this->template."/template.xtpl");

            // this is in order for the template to be able to link to the stylesheet file
            $this->xtpl->assign("template", $this->template);

            // transmit the settings to the JavaScript
            $JavaScriptSettings = "
                JavaScriptTooltips_fadeInSpeed = ".$this->fadeInSpeed.";
                JavaScriptTooltips_fadeOutSpeed = ".$this->fadeOutSpeed.";
                JavaScriptTooltips_fadeOutDelay = ".$this->fadeOutDelay.";
                JavaScriptTooltips_fadeInDelay = ".$this->fadeInDelay.";
                JavaScriptTooltips_maximumOpacity = ".$this->maximumOpacity.";
                JavaScriptTooltips_offsetX = ".$this->offsetX.";
                JavaScriptTooltips_offsetY = ".$this->offsetY.";
            ";
            $this->xtpl->assign("JavaScriptSettings", $JavaScriptSettings);

            // this will only parse and output the "init" section of the template
            // the "init" section links the stylesheet file and the javascript file
            $this->xtpl->parse("main.init");
            $this->xtpl->out("main.init");

        // if the file does not exists
        } else {
        
            // save the error level and stop the execution of the script
            $this->error = 2;
            return false;

        }
        
        return true;

    }
    
    /**
     *  Returns the JavaScript code that will make a tooltip show.
     *
     *  This JavaScript code needs to be inserted as an onmouseover/onmousemove event for an HTML element
     *
     *
     *  @param  string  $content    The string to be shown in the tooltip.
     *
     *                              <i>You don't have to escape the value of $content!</i>
     *
     *  @return mixed   Returns the JavaScript code that will make the tooltip show on success or FALSE on failure
     */
    function show($content)
    {
    
        // if the "init" section of the template file was indeed parsed
        if (isset($this->xtpl)) {
        
            // parse the template for the tooltip
            $this->xtpl->reset("main.tooltip");
            $this->xtpl->assign("content", $content);
            $this->xtpl->parse("main.tooltip");

            // this is how the parsed template looks with the content in it
            $parsed = $this->xtpl->text("main.tooltip");

            // as we need the javascript to be on one single line, we strip the newline
            $parsed = preg_replace("/\n|\r/", "", $this->xtpl->text("main.tooltip"));

            // in order to not have problems with single and double quotes we simply rawurlencode everything
            $parsed = rawurlencode(trim($parsed));

            // return the generated string
            return "javascript: show(this,'".$parsed."', event)";
        
        // if the "init" section of the template file was not parsed
        } else {
        
            // save the error level and stop the execution of the script
            $this->error = 3;
            return "javascript:false";
        
        }

    }
    
}

?>
