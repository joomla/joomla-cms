<?php 

/**
 * @version		$Id: index.php 11953 2009-06-01 03:36:36Z robs $
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Ron Severdia
 * @website		http://www.kontentdesign.com 
 * @email		ron@kontentdesign.com 
 */

JHTML::_('behavior.mootools');
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
		<div id="main_container">
			<div id="head">
				<?php if($this->countModules('head')) : ?>
					<div id="feature">
						<jdoc:include type="modules" name="head" style="none" />
					</div>
				<?php endif; ?>
			</div>
			<div id="body">
				<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
			<div id="footer">
				&copy; <?php echo date("Y"); ?> <?php echo $mainframe->getCfg('sitename'); ?>
			</div>
		</div>
	</body> 
</html>