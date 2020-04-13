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
        
        // tell the tooltips to start fading in only after it have waited for one second
        $tt->fadeInDelay = 1000;
        
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
                    <input type="button" value="hover me!" onmouseover="<?=$tt->show("Hello World!")?>">
                </td>
            </tr>
        </table>
    
    </body>
</html>
