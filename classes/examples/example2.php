<?php

    // include the JavaScript tooltip generator class
    require "../class.tooltips.php";

    // instantiate the class
    $tt = new tooltips();
    
?>
<html>
    <head>
        <title>PHP JavaScript Tooltip Generator Class</title>
    </head>
    <body>
    <?php

        // set some properties for the tooltip
        // THIS MUST BE DONE BEFORE CALLING THE init() METHOD!
        
        // tell the tooltips to start fading in only after it have waited for 100 milliseconds
        $tt->fadeInDelay = 100;
        // tell the tooltips to start fading out only after 3 seconds
        // this is to show how more than just one tooltip can be visible on the screen at the same time!
        $tt->fadeOutDelay = 3000;

        // see the manual for what other properties can be set!
        
        // notice that we init the tooltips in the <BODY> !
        $tt->init();
    ?>
    
        <table style="width:100%;height:100%">
            <tr>
                <td align="center" valign="center">
                    <!--
                        notice that we're not setting the onmouseout event!
                        the script automatically takes care of it
                    -->
                    <input type="button" value="hover me!" onmouseover="<?=$tt->show("Hello World<br />from button 1!")?>"><br /><br />
                    <input type="button" value="hover me also!" onmouseover="<?=$tt->show("Hello World!<br />from button 2!")?>"><br /><br />
                    <input type="button" value="and hover me, too, please!" onmouseover="<?=$tt->show("Hello World!<br />from button 3!")?>"><br /><br />
                </td>
            </tr>
        </table>
    
    </body>
</html>
