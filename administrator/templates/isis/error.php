<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Getting params from template
$params = JFactory::getApplication()->getTemplate(true)->params;

$app   = JFactory::getApplication();
$doc   = JFactory::getDocument();
$lang  = JFactory::getLanguage();
$this->language = $doc->language;
$this->direction = $doc->direction;
$input = $app->input;
$user  = JFactory::getUser();

// Detecting Active Variables
$option   = $input->get('option', '');
$view     = $input->get('view', '');
$layout   = $input->get('layout', '');
$task     = $input->get('task', '');
$itemid   = $input->get('Itemid', '');
$sitename = $app->getCfg('sitename');

$cpanel = ($option === 'com_cpanel');

$showSubmenu = false;
$this->submenumodules = JModuleHelper::getModules('submenu');
foreach ($this->submenumodules as $submenumodule)
{
	$output = JModuleHelper::renderModule($submenumodule);
	if (strlen($output))
	{
		$showSubmenu = true;
		break;
	}
}

// Logo file
if ($params->get('logoFile'))
{
	$logo = JUri::root() . $params->get('logoFile');
}
else
{
	$logo = $this->baseurl . "/templates/" . $this->template . "/images/logo.png";
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage()); ?></title>
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
	<?php // If debug  mode
		$debug = JFactory::getConfig()->get('debug_lang');
		if ((defined('JDEBUG') && JDEBUG) || $debug) : ?>
		<!-- Load additional CSS styles for debug mode-->
		<link rel="stylesheet" href="<?php echo JUri::root() ?>/media/cms/css/debug.css" type="text/css" />
	<?php endif; ?>
	<?php
	// If Right-to-Left
	if ($this->direction == 'rtl')
	{
	?>
		<link rel="stylesheet" href="<?php echo JUri::root() ?>/media/jui/css/bootstrap-rtl.css" type="text/css" />
	<?php
	}
	// Load specific language related CSS
	$file = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';
	if (is_file($file))
	{
	?>
		<link rel="stylesheet" href="<?php echo $file;?>" type="text/css" />
	<?php
	}
	// Use of Google Font
	if ($params->get('googleFont'))
	{
	?>
		<link href='http://fonts.googleapis.com/css?family=<?php echo $params->get('googleFontName');?>' rel='stylesheet' type='text/css'>
	<?php
	}
	?>

	<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	<?php
	// Template color
	if ($params->get('templateColor'))
	{
	?>
	<style type="text/css">
		.navbar-inner, .navbar-inverse .navbar-inner, .nav-list > .active > a, .nav-list > .active > a:hover, .dropdown-menu li > a:hover, .dropdown-menu .active > a, .dropdown-menu .active > a:hover, .navbar-inverse .nav li.dropdown.open > .dropdown-toggle, .navbar-inverse .nav li.dropdown.active > .dropdown-toggle, .navbar-inverse .nav li.dropdown.open.active > .dropdown-toggle
		{
			background: <?php echo $params->get('templateColor');?>;
		}
		.navbar-inner, .navbar-inverse .nav li.dropdown.open > .dropdown-toggle, .navbar-inverse .nav li.dropdown.active > .dropdown-toggle, .navbar-inverse .nav li.dropdown.open.active > .dropdown-toggle{
			-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
			-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
			box-shadow: 0 1px 3px rgba(0, 0, 0, .25), inset 0 -1px 0 rgba(0, 0, 0, .1), inset 0 30px 10px rgba(0, 0, 0, .2);
		}
	</style>
	<?php
	}
	?>
	<?php
	// Template header color
	if ($params->get('headerColor'))
	{
	?>
	<style type="text/css">
		.header
		{
			background: <?php echo $params->get('headerColor');?>;
		}
	</style>
	<?php
	}
	?>
	<script src="../media/jui/js/jquery.js" type="text/javascript"></script>
	<script src="../media/jui/js/jquery-noconflict.js" type="text/javascript"></script>
	<script src="../media/jui/js/bootstrap.js" type="text/javascript"></script>
	<script src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/template.js" type="text/javascript"></script>
	<!--[if lt IE 9]>
		<script src="../media/jui/js/html5.js"></script>
	<![endif]-->
</head>

<body class="admin <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " ";?>" data-spy="scroll" data-target=".subhead" data-offset="87">
	<!-- Top Navigation -->
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid">
				<?php if ($params->get('admin_menus') != '0') : ?>
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
				<?php endif; ?>
				<a class="brand" href="<?php echo JUri::root(); ?>" title="<?php echo JText::sprintf('TPL_ISIS_PREVIEW', $sitename);?>" target="_blank"><?php echo JHtml::_('string.truncate', $sitename, 14, false, false);?> <i class="icon-out-2 small"></i></a>
				<?php if ($params->get('admin_menus') != '0') : ?>
				<div class="nav-collapse">
				<?php else : ?>
				<div>
				<?php endif; ?>
					<?php
					// Display menu modules
					$this->menumodules = JModuleHelper::getModules('menu');
					foreach ($this->menumodules as $menumodule)
					{
						$output = JModuleHelper::renderModule($menumodule, array('style' => 'none'));
						$params = new JRegistry;
						$params->loadString($menumodule->params);
						echo $output;
					}
					?>
					<ul class="<?php if ($this->direction == 'rtl') : ?>nav<?php else : ?>nav pull-right<?php endif; ?>">
						<li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $user->username; ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li class=""><a href="index.php?option=com_admin&task=profile.edit&id=<?php echo $user->id;?>"><?php echo JText::_('TPL_ISIS_EDIT_ACCOUNT');?></a></li>
								<li class="divider"></li>
								<li class=""><a href="<?php echo JRoute::_('index.php?option=com_login&task=logout&'. JSession::getFormToken() .'=1');?>"><?php echo JText::_('TPL_ISIS_LOGOUT');?></a></li>
							</ul>
						</li>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</nav>
	<!-- Header -->
	<header class="header">
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span2 container-logo">
					<a class="logo" href="<?php echo $this->baseurl; ?>"><img src="<?php echo $logo;?>" alt="<?php echo $sitename; ?>" /></a>
				</div>
				<div class="span10">
					<h1 class="page-title"><?php echo JText::_('ERROR'); ?></h1>
				</div>
			</div>
		</div>
	</header>
	<div class="subhead-spacer" style="margin-bottom: 20px"></div>
	<!-- container-fluid -->
	<div class="container-fluid container-main">
		<section id="content">
			<!-- Begin Content -->
			<div class="row-fluid">
					<div class="span12">
						<!-- Begin Content -->
						<h1 class="page-header"><?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
						<blockquote>
							<span class="label label-inverse"><?php echo $this->error->getCode(); ?></span> <?php echo $this->error->getMessage();?>
						</blockquote>
						<p><a href="<?php echo $this->baseurl; ?>" class="btn"><i class="icon-dashboard"></i> <?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a></p>
						<!-- End Content -->
					</div>
			</div>
			<!-- End Content -->
		</section>
		<hr />
	</div>
	<!-- Begin Status Module -->
	<div id="status" class="navbar navbar-fixed-bottom hidden-phone">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<p>&copy; <?php echo $sitename; ?> <?php echo date('Y');?></p>
			</div>
			<?php
			// Display status modules
			$this->statusmodules = JModuleHelper::getModules('status');
			foreach ($this->statusmodules as $statusmodule)
			{
				$output = JModuleHelper::renderModule($statusmodule, array('style' => 'no'));
				$params = new JRegistry;
				$params->loadString($statusmodule->params);
				echo $output;
			}
			?>
		</div>
	</div>
	<!-- End Status Module -->
	<script>
		(function($){
			// fix sub nav on scroll
			var $win = $(window)
			  , $nav = $('.subhead')
			  , navTop = $('.subhead').length && $('.subhead').offset().top - 40
			  , isFixed = 0

			processScroll()

			// hack sad times - holdover until rewrite for 2.1
			$nav.on('click', function ()
			{
				if (!isFixed) setTimeout(function () {  $win.scrollTop($win.scrollTop() - 47) }, 10)
			})

			$win.on('scroll', processScroll)

			function processScroll()
			{
				var i, scrollTop = $win.scrollTop()
				if (scrollTop >= navTop && !isFixed)
				{
					isFixed = 1
					$nav.addClass('subhead-fixed')
				} else if (scrollTop <= navTop && isFixed)
				{
					isFixed = 0
					$nav.removeClass('subhead-fixed')
				}
			}
		})(jQuery);
	</script>
</body>
</html>
