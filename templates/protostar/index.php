<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->getCfg('sitename');

if($task == "edit" || $layout == "form" )
{
	$fullWidth = 1;
}
else
{
	$fullWidth = 0;
}

// Add Stylesheets
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

// If Right-to-Left
if ($this->direction == 'rtl')
{
	$doc->addStyleSheet('media/jui/css/bootstrap-rtl.css');
}

// Add current user information
$user = JFactory::getUser();

// Adjusting content width
if ($this->countModules('position-7') && $this->countModules('position-8'))
{
	$span = "span6";
}
elseif ($this->countModules('position-7') && !$this->countModules('position-8'))
{
	$span = "span9";
}
elseif (!$this->countModules('position-7') && $this->countModules('position-8'))
{
	$span = "span9";
}
else
{
	$span = "span12";
}

// Logo file
if ($this->params->get('logoFile'))
{
	$logo = JURI::root() . $this->params->get('logoFile');
}
else
{
	$logo = $this->baseurl . "/templates/" . $this->template . "/images/logo.png";
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php
	// Use of Google Font
	if ($this->params->get('googleFont'))
	{
	?>
		<link href='http://fonts.googleapis.com/css?family=<?php echo $this->params->get('googleFontName');?>' rel='stylesheet' type='text/css'>
	<?php
	}
	?>
	<script src="<?php echo $this->baseurl; ?>/media/jui/js/jquery.min.js"></script>
	<script src="<?php echo $this->baseurl; ?>/media/jui/js/bootstrap.min.js"></script>
	<script type="text/javascript">
	  jQuery.noConflict();
	</script>
	<jdoc:include type="head" />
	<?php
	// Template color
	if ($this->params->get('templateColor'))
	{
	?>
	<style type="text/css">
		body.site
		{
			border-top: 3px solid <?php echo $this->params->get('templateColor');?>;
			background-color: <?php echo $this->params->get('templateBackgroundColor');?>
		}
		a
		{
			color: <?php echo $this->params->get('templateColor');?>;
		}
		.navbar-inner, .nav-list > .active > a, .nav-list > .active > a:hover, .dropdown-menu li > a:hover, .dropdown-menu .active > a, .dropdown-menu .active > a:hover, .nav-pills > .active > a, .nav-pills > .active > a:hover
		{
			background: <?php echo $this->params->get('templateColor');?>;
		}
		.navbar-inner
		{
			-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
			-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
			box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
		}
	</style>
	<?php
	}
	?>
</head>

<body class="site <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " itemid-" . $itemid . " ";?> <?php if ($this->params->get('fluidContainer')) { echo "fluid"; } ?>">

	<!-- Body -->
	<div class="body">
		<div class="container<?php if ($this->params->get('fluidContainer')) { echo "-fluid"; } ?>">
			<!-- Header -->
			<div class="header">
				<div class="header-inner">
					<a class="brand pull-left" href="<?php echo $this->baseurl; ?>">
						<img src="<?php echo $logo;?>" alt="<?php echo $sitename; ?>" />
					</a>
					<div class="header-search pull-right">
						<jdoc:include type="modules" name="position-0" style="none" />
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
			<?php if ($this->countModules('position-1')): ?>
			<div class="navigation">
				<jdoc:include type="modules" name="position-1" style="none" />
			</div>
			<?php endif; ?>
			<!-- Banner -->
			<div class="banner">
				<jdoc:include type="modules" name="banner" style="xhtml" />
			</div>
			<div class="row-fluid">
				<?php if ($this->countModules('position-8')): ?>
				<!-- Begin Sidebar -->
				<div id="sidebar" class="span3">
					<div class="sidebar-nav">
						<jdoc:include type="modules" name="position-8" style="xhtml" />
					</div>
				</div>
				<!-- End Sidebar -->
				<?php endif; ?>
				<div id="content" class="<?php echo $span;?>">
					<!-- Begin Content -->
					<jdoc:include type="modules" name="position-3" style="xhtml" />
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<jdoc:include type="modules" name="position-2" style="none" />
					<!-- End Content -->
				</div>
				<?php if ($this->countModules('position-7')) : ?>
				<div id="aside" class="span3">
					<!-- Begin Right Sidebar -->
					<jdoc:include type="modules" name="position-7" style="well" />
					<!-- End Right Sidebar -->
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<!-- Footer -->
	<div class="footer">
		<div class="container<?php if ($this->params->get('fluidContainer')) { echo "-fluid"; } ?>">
			<hr />
			<jdoc:include type="modules" name="footer" style="none" />
			<p class="pull-right"><a href="#top" id="back-top">Back to top</a></p>
			<p>&copy; <?php echo $sitename; ?> <?php echo date('Y');?></p>
		</div>
	</div>
	<jdoc:include type="modules" name="debug" style="none" />
	<script>
		jQuery('*[rel=tooltip]').tooltip()
		jQuery('*[rel=popover]').popover()
		jQuery('.tip-bottom').tooltip({placement: "bottom"})
	</script>
</body>
</html>
