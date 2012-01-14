<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2011 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// template color parameter
$templateColor = $this->params->get('templateColor');
$darkerColor   = $this->params->get('darkerColor');

// just to avoid user error when # is missing
if (strrpos($templateColor, "#") === false) $templateColor = "#".$this->params->get('templateColor');

$app = JFactory::getApplication();

?>
<!DOCTYPE html>
<html lang="<?php echo  $this->language; ?>" class="no-js" dir="<?php echo  $this->direction; ?>">

<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <jdoc:include type="head" />

    <link href="templates/<?php echo $this->template ?>/css/libs/template.reset.css" rel="stylesheet" type="text/css">    
    <link href="templates/<?php echo $this->template ?>/css/libs/template.buttons.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.forms.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet" type="text/css">

    <style type="text/css">
            body { background-color: <?php echo $templateColor;?>; }
            #login-box { border: 15px solid <?php echo $darkerColor; ?>}
            #logo { text-shadow: 1px 1px 0 <?php echo $darkerColor; ?>, -1px -1px 0 <?php echo $darkerColor; ?>; }
            #system-message { display: block; }
            .message-wrapper {margin-top: -80px;}
    </style>

</head>
<body onload="javascript:setFocus()" id="login-page">
    <div id="login-container">
        <div class="message-wrapper"><jdoc:include type="message" /></div>
        <div id="login-box">
                    <jdoc:include type="component" />
                <noscript>
                    <?php echo  JText::_('WARNJAVASCRIPT') ?>
                </noscript>
        </div><!-- /#login-box -->
    </div>
    <div id="site-box">
        <span class="site-link"><a href="<?php echo JURI::root();?>">&larr; <?php echo JText::_('TPL_MINIMA_VIEW_SITE'); ?></a></span>
    </div>
    <div id="logo-box">
        <span id="logo"><?php echo $app->getCfg('sitename');?></span>
    </div>
    <script type="text/javascript">
        function setFocus() {
            //document.getElementById('form-login').username.select();
            //document.getElementById('form-login').username.focus();	    
        }
    </script>
</body>
</html>
