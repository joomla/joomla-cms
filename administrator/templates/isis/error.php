<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/** @var JDocumentError $this */

// Getting params from template
$params = JFactory::getApplication()->getTemplate(true)->params;

$app   = JFactory::getApplication();
$lang  = JFactory::getLanguage();
$input = $app->input;
$user  = JFactory::getUser();

// Gets the FrontEnd Main page Uri
$frontEndUri = JUri::getInstance(JUri::root());
$frontEndUri->setScheme(((int) $app->get('force_ssl', 0) === 2) ? 'https' : 'http');
$mainPageUri = $frontEndUri->toString();

// Detecting Active Variables
$option   = $input->get('option', '');
$view     = $input->get('view', '');
$layout   = $input->get('layout', '');
$task     = $input->get('task', '');
$itemid   = $input->get('Itemid', 0, 'int');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');

$cpanel = ($option === 'com_cpanel');

$showSubmenu = false;
$this->submenumodules = JModuleHelper::getModules('submenu');
foreach ($this->submenumodules as $submenumodule)
{
	$output = JModuleHelper::renderModule($submenumodule);
	if ($output !== '')
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
	$logo = $this->baseurl . '/templates/' . $this->template . '/images/logo.png';
}

// Template Parameters
$displayHeader = $params->get('displayHeader', '1');
$statusFixed   = $params->get('statusFixed', '1');
$stickyToolbar = $params->get('stickyToolbar', '1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
	<?php if ($app->get('debug_lang', '0') == '1' || $app->get('debug', '0') == '1') : ?>
		<!-- Load additional CSS styles for debug mode-->
		<link href="<?php echo JUri::root(true); ?>/media/cms/css/debug.css" rel="stylesheet" />
	<?php endif; ?>
	<?php // If Right-to-Left ?>
	<?php if ($this->direction == 'rtl') : ?>
		<link href="<?php echo JUri::root(true); ?>/media/jui/css/bootstrap-rtl.css" rel="stylesheet" />
	<?php endif; ?>
	<?php // Load specific language related CSS ?>
	<?php $file = '/administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css'; ?>
	<?php if (is_file(JPATH_ROOT . $file)) : ?>
		<link href="<?php echo JUri::root(true) . $file; ?>" rel="stylesheet" />
	<?php endif; ?>
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template<?php echo ($this->direction == 'rtl' ? '-rtl' : ''); ?>.css" rel="stylesheet" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
	<?php // Template color ?>
	<?php if ($params->get('templateColor')) : ?>
	<style>
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
	<?php endif; ?>
	<?php // Template header color ?>
	<?php if ($params->get('headerColor')) : ?>
	<style>
		.header
		{
			background: <?php echo $params->get('headerColor');?>;
		}
	</style>
	<?php endif; ?>
	<?php // Sidebar background color ?>
	<?php if ($params->get('sidebarColor')) : ?>
		<style>
			.nav-list > .active > a, .nav-list > .active > a:hover {
				background: <?php echo $params->get('sidebarColor'); ?>;
			}
		</style>
	<?php endif; ?>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/jquery.js"></script>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/jquery-noconflict.js"></script>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/bootstrap.js"></script>
	<script src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/template.js"></script>
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task;?>" data-spy="scroll" data-target=".subhead" data-offset="87">
	<!-- Top Navigation -->
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid">
				<?php if ($params->get('admin_menus') != '0') : ?>
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="element-invisible"><?php echo JTEXT::_('TPL_ISIS_TOGGLE_MENU'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
				<?php endif; ?>
				<a class="admin-logo" href="<?php echo $this->baseurl; ?>"><span class="icon-joomla"></span></a>

				<a class="brand hidden-desktop hidden-tablet" href="<?php echo $mainPageUri; ?>" title="<?php echo JText::sprintf('TPL_ISIS_PREVIEW', $sitename); ?>" target="_blank"><?php echo JHtml::_('string.truncate', $sitename, 14, false, false); ?>
					<span class="icon-out-2 small"></span></a>

				<?php if ($params->get('admin_menus') != '0') : ?>
				<div class="nav-collapse">
				<?php else : ?>
				<div>
				<?php endif; ?>
					<?php // Display menu modules ?>
					<?php $this->menumodules = JModuleHelper::getModules('menu'); ?>
					<?php foreach ($this->menumodules as $menumodule) : ?>
						<?php $output = JModuleHelper::renderModule($menumodule, array('style' => 'none')); ?>
						<?php $params = new Registry($menumodule->params); ?>
						<?php echo $output; ?>
					<?php endforeach; ?>
					<ul class="nav nav-user<?php echo ($this->direction == 'rtl') ? ' pull-left' : ' pull-right'; ?>">
						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="icon-cog"></span>
								<span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li>
									<span>
										<span class="icon-user"></span>
										<strong><?php echo $user->name; ?></strong>
									</span>
								</li>
								<li class="divider"></li>
								<li class="">
									<a href="index.php?option=com_admin&amp;task=profile.edit&amp;id=<?php echo $user->id; ?>"><?php echo JText::_('TPL_ISIS_EDIT_ACCOUNT'); ?></a>
								</li>
								<li class="divider"></li>
								<li class="">
									<a href="<?php echo JRoute::_('index.php?option=com_login&task=logout&' . JSession::getFormToken() . '=1'); ?>"><?php echo JText::_('TPL_ISIS_LOGOUT'); ?></a>
								</li>
							</ul>
						</li>
					</ul>
					<a class="brand visible-desktop visible-tablet" href="<?php echo $mainPageUri; ?>" title="<?php echo JText::sprintf('TPL_ISIS_PREVIEW', $sitename); ?>" target="_blank"><?php echo JHtml::_('string.truncate', $sitename, 14, false, false); ?>
						<span class="icon-out-2 small"></span></a>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</nav>
	<!-- Header -->
	<header class="header">
		<?php if ($displayHeader) : ?>
		<div class="container-logo">
			<img src="<?php echo $logo; ?>" class="logo" />
		</div>
		<?php endif; ?>
		<div class="container-title">
			<h1 class="page-title"><?php echo JText::_('ERROR'); ?></h1>
		</div>
	</header>
	<?php if (!$statusFixed && $this->getInstance()->countModules('status')) : ?>
		<!-- Begin Status Module -->
		<div id="status" class="navbar status-top hidden-phone">
			<div class="btn-toolbar">
				<div class="btn-group pull-right">
					<p>
						&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
					</p>
				</div>
				<?php // Display status modules ?>
				<?php $this->statusmodules = JModuleHelper::getModules('status'); ?>
				<?php foreach ($this->statusmodules as $statusmodule) : ?>
					<?php $output = JModuleHelper::renderModule($statusmodule, array('style' => 'no')); ?>
					<?php $params = new Registry($statusmodule->params); ?>
					<?php echo $output; ?>
				<?php endforeach; ?>
			</div>
			<div class="clearfix"></div>
		</div>
		<!-- End Status Module -->
	<?php endif; ?>
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
						<span class="label label-inverse"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8');?>
						<?php if ($this->debug) : ?>
							<br/><?php echo htmlspecialchars($this->error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->error->getLine(); ?>
						<?php endif; ?>
					</blockquote>
					<?php if ($this->debug) : ?>
						<div>
							<?php echo $this->renderBacktrace(); ?>
							<?php // Check if there are more Exceptions and render their data as well ?>
							<?php if ($this->error->getPrevious()) : ?>
								<?php $loop = true; ?>
								<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
								<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
								<?php $this->setError($this->_error->getPrevious()); ?>
								<?php while ($loop === true) : ?>
									<p><strong><?php echo JText::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
									<p>
										<?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
										<br/><?php echo htmlspecialchars($this->_error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->_error->getLine(); ?>
									</p>
									<?php echo $this->renderBacktrace(); ?>
									<?php $loop = $this->setError($this->_error->getPrevious()); ?>
								<?php endwhile; ?>
								<?php // Reset the main error object to the base error ?>
								<?php $this->setError($this->error); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<p><a href="<?php echo $this->baseurl; ?>" class="btn"><span class="icon-dashboard"></span> <?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a></p>
					<!-- End Content -->
				</div>
			</div>
			<!-- End Content -->
		</section>
		<hr />
	</div>
	<script>
		(function($){
			// fix sub nav on scroll
			var $win    = $(window)
			  , $nav    = $('.subhead')
			  , navTop  = $('.subhead').length && $('.subhead').offset().top - 40
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
