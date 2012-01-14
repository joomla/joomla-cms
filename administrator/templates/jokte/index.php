<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2011 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$app = JFactory::getApplication();

// template color parameter
$templateColor = $this->params->get('templateColor');
$darkerColor   = $this->params->get('darkerColor');

// just to avoid user error when # is missing
if (strrpos($templateColor, "#") === false) $templateColor = "#".$this->params->get('templateColor');

// get the current logged in user
$currentUser = JFactory::getUser();

// get the current language object
$lang   = JFactory::getLanguage();

// Mount the body classes
$requestVars = array(
                        "option"  => JRequest::getCmd('option', 'no-option'), 
                        "view"    => JRequest::getCmd('view', 'no-view'),
                        "layout"  => JRequest::getCmd('layout', 'no-layout'),
                        "task"    => JRequest::getCmd('task', 'no-task'),
                        "itemId"  => JRequest::getCmd('Itemid', 'no-itemid'),                        
                        "locked"  => JRequest::getInt('hidemainmenu') ? 'locked' : 'not-locked',
                        "hasId"   => JRequest::getCmd('id', '') ? 'has-id' : 'no-id'
                    );

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" class="no-js" dir="<?php echo  $this->direction; ?>">

<head>

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <jdoc:include type="head" />


    <link href="templates/<?php echo $this->template ?>/css/libs/template.reset.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.buttons.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.shortcuts.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.forms.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/module.mypanel.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/libs/template.icons.css" rel="stylesheet">
    <link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet">     
    
    <!--<link href="templates/<?php echo $this->template ?>/css/tablet.css" media="screen and (min-device-width: 768px) and (max-device-width : 1024px)" rel="stylesheet">-->

    <style>
        #panel li a:hover,.box-top, #panel-pagination li { background-color: <?php echo $templateColor; ?>; }
        #panel-tab, #panel-tab.active, #panel-wrapper,#more, #more.inactive { background-color: <?php echo $darkerColor; ?>; }
        #tophead { background: <?php echo $templateColor;?>; background: -moz-linear-gradient(-90deg,<?php echo $templateColor;?>,<?php echo $darkerColor;?>); /* FF3.6 */ background: -webkit-gradient(linear, left top, left bottom, from(<?php echo $templateColor;?>), to(<?php echo $darkerColor;?>)); /* Saf4+, Chrome */ filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=<?php echo $templateColor;?>, endColorstr=<?php echo $darkerColor;?>); /* IE6,IE7 */ -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='<?php echo $templateColor;?>', EndColorStr='<?php echo $darkerColor;?>')"; /* IE8 */ }
        #prev, #next { border: 1px solid <?php echo $templateColor; ?>; background: <?php echo $templateColor;?>; background: -moz-linear-gradient(-90deg,<?php echo $templateColor;?>,<?php echo $darkerColor;?>); /* FF3.6 */ background: -webkit-gradient(linear, left top, left bottom, from(<?php echo $templateColor;?>), to(<?php echo $darkerColor;?>)); /* Saf4+, Chrome */ filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=<?php echo $templateColor;?>, endColorstr=<?php echo $darkerColor;?>); /* IE6,IE7 */ -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorStr='<?php echo $templateColor;?>', EndColorStr='<?php echo $darkerColor;?>')"; /* IE8 */ }
        #panel-pagination .current { border: none; background: <?php echo $templateColor;?>; background: -webkit-gradient(linear, left top, left bottom, from(<?php echo $templateColor;?>), to(<?php echo $darkerColor; ?>)); box-shadow: none; box-shadow: inset 0 1px 1px rgba(255,255,255,0.3); border: 1px solid rgba(0,0,0,0.3);}
        #prev:active, #next:active { background-color: <?php echo $darkerColor; ?>; }
        .box:hover { -moz-box-shadow: 0 0 10px <?php echo $templateColor; ?>; -webkit-box-shadow: 0 0 10px <?php echo $templateColor; ?>; box-shadow: 0 0 10px <?php echo $templateColor; ?>; }
        #panel-pagination li { color: <?php echo $templateColor; ?>; }
        ::selection { background: <?php echo $templateColor; ?>; color:#000; /* Safari */ }
        ::-moz-selection { background: <?php echo $templateColor; ?>; color:#000; /* Firefox */ }
        body, a:link { -webkit-tap-highlight-color: <?php echo $templateColor; ?>;  }
        #logo {text-shadow: 1px 1px 0 <?php echo $darkerColor; ?>, -1px -1px 0 <?php echo $darkerColor; ?>; }
    </style>

    <script src="templates/<?php echo $this->template ?>/js/plugins/head.min.js"></script>    
	<!--[if (gte IE 6)&(lte IE 8)]>
        <script type="text/javascript" src="templates/<?php echo $this->template ?>/js/plugins/selectivizr.min.js" defer="defer"></script>
    <![endif]-->
</head>
<body id="minima" class="<?php echo implode(" ", $requestVars); ?>">
    <?php if (!JRequest::getInt('hidemainmenu')): ?>
        <?php if( $this->countModules('panel') ): ?>
        <div id="panel-wrapper">
            <jdoc:include type="modules" name="panel" />
        </div>
        <?php endif; ?>        
    <header id="tophead">
        <div class="title">
                <span id="logo"><?php echo $app->getCfg('sitename');?></span>
                <span class="site-link"><a target="_blank" title="<?php echo $app->getCfg('sitename');?>" href="<?php echo JURI::root();?>"><?php echo "(".JText::_('TPL_MINIMA_VIEW_SITE').")"; ?></a></span>
        </div>
        <div id="module-status">
            <jdoc:include type="modules" name="status" />
        </div>
        <?php if( $this->countModules('panel') ): ?>
        <div id="tab-wrapper">
            <span id="panel-tab">
                <?php echo JText::_('TPL_MINIMA_PANEL') ?>
            </span>
        </div>
        <?php endif; ?>
        <div id="list-wrapper">
            <span id="more"></span>
            <div class="clr"></div>
            <nav id="list-content">
                <dl>
                    <dt><?php echo JText::_('TPL_MINIMA_TOOLS',true);?></dt>
                    <?php if( $currentUser->authorize( array('core.manage','com_checkin') ) ): ?><dd><a href="index.php?option=com_checkin"><?php echo JText::_('TPL_MINIMA_TOOLS_GLOBAL_CHECKIN'); ?></a></dd><?php endif; ?>
                    <?php if( $currentUser->authorize( array('core.manage','com_cache') ) ): ?><dd><a href="index.php?option=com_cache"><?php echo JText::_('TPL_MINIMA_TOOLS_CLEAR_CACHE'); ?></a></dd><?php endif; ?>
                    <?php if( $currentUser->authorize( array('core.manage','com_cache') ) ): ?><dd><a href="index.php?option=com_cache&amp;view=purge"><?php echo JText::_('TPL_MINIMA_TOOLS_PURGE_EXPIRED_CACHE'); ?></a></dd><?php endif; ?>
                    <?php if( $currentUser->authorize( array('core.manage','com_admin') ) ): ?><dd><a href="index.php?option=com_admin&amp;view=sysinfo"><?php echo JText::_('TPL_MINIMA_TOOLS_SYSTEM_INFORMATION'); ?></a></dd><?php endif; ?>
                </dl>
                <?php if( $currentUser->authorize( array('core.manage','com_installer') ) ): ?>
                <dl>
                    <dt><?php echo JText::_('TPL_MINIMA_EXTENSIONS',true);?></dt>
                    <dd><a href="index.php?option=com_installer"><?php echo JText::_('TPL_MINIMA_EXTENSIONS_INSTALL'); ?></a></dd>
                    <dd><a href="index.php?option=com_installer&amp;view=update"><?php echo JText::_('TPL_MINIMA_EXTENSIONS_UPDATE'); ?></a></dd>
                    <dd><a href="index.php?option=com_installer&amp;view=manage"><?php echo JText::_('TPL_MINIMA_EXTENSIONS_MANAGE'); ?></a></dd>
                    <dd><a href="index.php?option=com_installer&amp;view=discover"><?php echo JText::_('TPL_MINIMA_EXTENSIONS_DISCOVER'); ?></a></dd>
                </dl>
                <?php endif; ?>
            </nav><!-- /#list-content -->
        </div><!-- /#list-wrapper -->        
    </header><!-- /#tophead -->
    <nav id="shortcuts">
            <jdoc:include type="modules" name="shortcuts" />
    </nav><!-- /#shortcuts -->
    <?php else: ?>
        <header id="tophead" class="locked">
            <div class="title">
                <span id="logo"><?php echo $app->getCfg('sitename');?></span>
                <span class="site-link"><a target="_blank" title="<?php echo $app->getCfg('sitename');?>" href="<?php echo JURI::root();?>"><?php echo "(".JText::_('TPL_MINIMA_VIEW_SITE').")"; ?></a></span>
            </div>
        </header>
    <?php endif; ?>
    <div class="message-wrapper"><jdoc:include type="message" /></div>
    <div id="content">
        <header id="content-top">
            <?php if (!JRequest::getInt('hidemainmenu')): ?>
                <jdoc:include type="modules" name="submenu" />
            <?php endif; ?>
            <div id="toolbar-box">
                <jdoc:include type="modules" name="toolbar" />
                <jdoc:include type="modules" name="title" />
            </div>
        </header><!-- /# content-top -->
        <section id="content-box">
            <jdoc:include type="component" />
            <noscript><?php echo  JText::_('WARNJAVASCRIPT') ?></noscript>
        </section><!-- /#content-box -->
    </div><!-- /#content -->
    
    <footer>
        <p class="copyright">
            <a href="http://www.joomla.org">Joomla!</a>
            <span class="version"><?php echo JVERSION; ?></span>
            <a href="#minima" id="topLink"><?php echo JText::_('TPL_MINIMA_TOP'); ?></a>
        </p>
        <jdoc:include type="modules" name="footer" style="none" />        
    </footer>
    <script>
        head.js(
            { minimaClass: "templates/<?php echo $this->template ?>/js/libs/minima.class.js" },
            { minimaPanel: "templates/<?php echo $this->template ?>/js/libs/minima.panel.js" },
            { minimaTabs: "templates/<?php echo $this->template ?>/js/libs/minima.tabs.js" },
            { minimaToolbar: "templates/<?php echo $this->template ?>/js/libs/minima.toolbar.js" },
            { minimaFilterbar: "templates/<?php echo $this->template ?>/js/libs/minima.filterbar.js" },
            { minima: "templates/<?php echo $this->template ?>/js/minima.js" }
        , function() {
            // all done            
            $('minima').addClass('ready');
        });
        MooTools.lang.set('en-US', 'Minima', {
            actionBtn : "<?php echo JText::_('TPL_MINIMA_ACTIONS',true);?>",
            showFilter: "<?php echo JText::_('TPL_MINIMA_SHOW_FILTER',true);?>",
            hideFilter: "<?php echo JText::_('TPL_MINIMA_HIDE_FILTER',true);?>"
        });
    </script>        
</body>
</html>
