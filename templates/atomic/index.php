<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.atomic
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* The following line loads the MooTools JavaScript Library */
JHtml::_('behavior.framework', true);

/* The following line gets the application object for things like displaying the site name */
$app = JFactory::getApplication();
?>
<?php echo '<?'; ?>xml version="1.0" encoding="<?php echo $this->_charset ?>"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
	<head>
		<!-- The following JDOC Head tag loads all the header and meta information from your site config and content. -->
		<jdoc:include type="head" />

		<!-- The following five lines load the Blueprint CSS Framework (http://blueprintcss.org). If you don't want to use this framework, delete these lines. -->
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/blueprint/screen.css" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/blueprint/print.css" type="text/css" media="print" />
		<!--[if lt IE 8]><link rel="stylesheet" href="blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/blueprint/plugins/fancy-type/screen.css" type="text/css" media="screen, projection" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/blueprint/plugins/joomla-nav/screen.css" type="text/css" media="screen" />

		<!-- The following line loads the template CSS file located in the template folder. -->
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />

		<!-- The following four lines load the Blueprint CSS Framework and the template CSS file for right-to-left languages. If you don't want to use these, delete these lines. -->
		<?php if($this->direction == 'rtl') : ?>
			<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/blueprint/plugins/rtl/screen.css" type="text/css" />
			<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template_rtl.css" type="text/css" />
		<?php endif; ?>

		<!-- The following line loads the template JavaScript file located in the template folder. It's blank by default. -->
		<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/template.js"></script>
	</head>
	<body>
		<div class="container">
			<hr class="space" />
			<div class="joomla-header span-16 append-1">
				<h1><?php echo htmlspecialchars($app->getCfg('sitename')); ?></h1>
			</div>
			<?php if($this->countModules('atomic-search') or $this->countModules('position-0')) : ?>
				<div class="joomla-search span-7 last">
	  	 			<jdoc:include type="modules" name="atomic-search" style="none" />
	  	 			<jdoc:include type="modules" name="position-0" style="none" />
				</div>
			<?php endif; ?>
		</div>
		<?php if($this->countModules('atomic-topmenu') or $this->countModules('position-2') ) : ?>
			<jdoc:include type="modules" name="atomic-topmenu" style="container" />
			<jdoc:include type="modules" name="position-1" style="container" />
		<?php endif; ?>

		<div class="container">
			<div class="span-16 append-1">
			<?php if($this->countModules('atomic-topquote') or $this->countModules('position-15') ) : ?>
				<jdoc:include type="modules" name="atomic-topquote" style="none" />
				<jdoc:include type="modules" name="position-15" style="none" />

			<?php endif; ?>
				<jdoc:include type="message" />
				<jdoc:include type="component" />
				<hr />
			<?php if($this->countModules('atomic-bottomleft') or $this->countModules('position-11')) : ?>
			 	<div class="span-7 colborder">
					<jdoc:include type="modules" name="atomic-bottomleft" style="bottommodule" />
					<jdoc:include type="modules" name="position-11" style="bottommodule" />

	        	</div>
	        <?php endif; ?>

	        <?php if($this->countModules('atomic-bottommiddle') or $this->countModules('position-9')
				or $this->countModules('position-10')) : ?>
				<div class="span-7 last">
	        		<jdoc:include type="modules" name="atomic-bottommiddle" style="bottommodule" />
					<jdoc:include type="modules" name="position-9" style="bottommodule" />
					<jdoc:include type="modules" name="position-10" style="bottommodule" />

				</div>
			<?php endif; ?>
			</div>
			<?php if($this->countModules('atomic-sidebar') || $this->countModules('position-7')
			|| $this->countModules('position-4') || $this->countModules('position-5')
			|| $this->countModules('position-3') || $this->countModules('position-6') || $this->countModules('position-8'))
			: ?>
				<div class="span-7 last">
					<jdoc:include type="modules" name="atomic-sidebar" style="sidebar" />
					<jdoc:include type="modules" name="position-7" style="sidebar" />
					<jdoc:include type="modules" name="position-4" style="sidebar" />
					<jdoc:include type="modules" name="position-5" style="sidebar" />
					<jdoc:include type="modules" name="position-6" style="sidebar" />
					<jdoc:include type="modules" name="position-8" style="sidebar" />
					<jdoc:include type="modules" name="position-3" style="sidebar" />
				</div>

			<?php endif; ?>

			<div class="joomla-footer span-16 append-1">
				<hr />
				&copy;<?php echo date('Y'); ?> <?php echo htmlspecialchars($app->getCfg('sitename')); ?>
			</div>
		</div>
		<jdoc:include type="modules" name="debug" />
	</body>
</html>
