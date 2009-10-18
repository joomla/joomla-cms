<?php

/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Steve Fisher
 * @website		http://stevefisher.ca
 * @email		hello@stevefisher.ca
 */

JHTML::_('behavior.mootools');

$app = JFactory::getApplication();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
	<head>
		<jdoc:include type="head" />
		<meta http-equiv="Content-Type" content="text/html; <?php echo _ISO; ?>" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
		<!--[if IE]>
			<link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template_ie.css" />
		<![endif]-->
		<?php if($this->direction == 'rtl') : ?>
			<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
		<?php endif; ?>
		<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/template.js"></script>
	</head>

	<body>
		<div id="pagewidth">
        <a name="top"></a><a href="#content" class="hidden">skip to content</a>
			<div id="header">
				<h1 id="sitename"><a href="index.php" title="Back Home"><?php echo $app->getCfg('sitename'); ?></a></h1>
				<jdoc:include type="modules" name="top" style="none" />
                <?php if($this->countModules('topmenu')) : ?>
                	<div id="navigation"><jdoc:include type="modules" name="topmenu" style="none" />
					</div>
				<?php endif; ?>
                <?php if($this->countModules('feature')) : ?>
                	<div id="feature"><jdoc:include type="modules" name="feature" style="none" />
					</div>
				<?php endif; ?>
			</div>
            <div id="twocols">
				<div id="maincol">
                	<a name="content"></a>
            		<jdoc:include type="message" />
            		<jdoc:include type="component" />
            	</div>
            	<div id="rightcol">
            		<jdoc:include type="modules" name="right" style="xhtml" />
                </div>
                <div id="footer">
				&copy; <?php echo date("Y"); ?> <?php echo $app->getCfg('sitename'); ?>
				</div>
           	</div>
		</div><a href="#top" id="gototop" class="no-click no-print">Top of Page</a>
	</body>
</html>