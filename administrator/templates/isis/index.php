<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.0
 */

defined('_JEXEC') or die;

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$lang            = JFactory::getLanguage();
$this->language  = $doc->language;
$this->direction = $doc->direction;
$input           = $app->input;
$user            = JFactory::getUser();

// Output as HTML5
$doc->setHtml5(true);

// Gets the FrontEnd Main page Uri
$frontEndUri = JUri::getInstance(JUri::root());
$frontEndUri->setScheme(((int) $app->get('force_ssl', 0) === 2) ? 'https' : 'http');
$mainPageUri = $frontEndUri->toString();

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

$doc->addScriptVersion($this->baseurl . '/templates/' . $this->template . '/js/template.js');

// Add Stylesheets
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/template' . ($this->direction == 'rtl' ? '-rtl' : '') . '.css');

// Load specific language related CSS
$languageCss = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';

if (file_exists($languageCss) && filesize($languageCss) > 0)
{
	$doc->addStyleSheetVersion($languageCss);
}

// Load custom.css
$customCss = 'templates/' . $this->template . '/css/custom.css';

if (file_exists($customCss) && filesize($customCss) > 0)
{
	$doc->addStyleSheetVersion($customCss);
}

// Detecting Active Variables
$option   = $input->get('option', '');
$view     = $input->get('view', '');
$layout   = $input->get('layout', '');
$task     = $input->get('task', '');
$itemid   = $input->get('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');
$cpanel   = ($option === 'com_cpanel');

$hidden = $app->input->get('hidemainmenu');

$showSubmenu          = false;
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

// Template Parameters
$displayHeader = $this->params->get('displayHeader', '1');
$statusFixed   = $this->params->get('statusFixed', '1');
$stickyToolbar = $this->params->get('stickyToolbar', '1');

// Header classes
$navbar_color = $this->params->get('templateColor') ? $this->params->get('templateColor') : '';
$header_color = ($displayHeader && $this->params->get('headerColor')) ? $this->params->get('headerColor') : '';
$navbar_is_light = ($navbar_color && colorIsLight($navbar_color));
$header_is_light = ($header_color && colorIsLight($header_color));

if ($displayHeader)
{
	// Logo file
	if ($this->params->get('logoFile'))
	{
		$logo = JUri::root() . $this->params->get('logoFile');
	}
	else
	{
		$logo = $this->baseurl . '/templates/' . $this->template . '/images/logo' . ($header_is_light ? '-inverse' : '') . '.png';
	}
}

function colorIsLight($color)
{
	$r = hexdec(substr($color, 1, 2));
	$g = hexdec(substr($color, 3, 2));
	$b = hexdec(substr($color, 5, 2));
	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

	return $yiq >= 200;
}

// Pass some values to javascript
$offset = 20;

if ($displayHeader || !$statusFixed)
{
	$offset = 30;
}

$stickyBar = 0;

if ($stickyToolbar)
{
	$stickyBar = 'true';
}

// Template color
if ($navbar_color)
{
	$doc->addStyleDeclaration("
	.navbar-inner,
	.navbar-inverse .navbar-inner,
	.dropdown-menu li > a:hover,
	.dropdown-menu .active > a,
	.dropdown-menu .active > a:hover,
	.navbar-inverse .nav li.dropdown.open > .dropdown-toggle,
	.navbar-inverse .nav li.dropdown.active > .dropdown-toggle,
	.navbar-inverse .nav li.dropdown.open.active > .dropdown-toggle,
	#status.status-top {
		background: " . $navbar_color . ";
	}");
}

// Template header color
if ($header_color)
{
	$doc->addStyleDeclaration("
	.header {
		background: " . $header_color . ";
	}");
}

// Sidebar background color
if ($this->params->get('sidebarColor'))
{
	$doc->addStyleDeclaration("
	.nav-list > .active > a,
	.nav-list > .active > a:hover {
		background: " . $this->params->get('sidebarColor') . ";
	}");
}

// Link color
if ($this->params->get('linkColor'))
{
	$doc->addStyleDeclaration("
	a,
	.j-toggle-sidebar-button {
		color: " . $this->params->get('linkColor') . ";
	}");
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<jdoc:include type="head" />
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid; ?>" data-basepath="<?php echo JURI::root(true); ?>">
<!-- Top Navigation -->
<nav class="navbar<?php echo $navbar_is_light ? '' : ' navbar-inverse'; ?> navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<?php if ($this->params->get('admin_menus') != '0') : ?>
				<a href="#" class="btn btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
			<?php endif; ?>

			<a class="admin-logo <?php echo ($hidden ? 'disabled' : ''); ?>" <?php echo ($hidden ? '' : 'href="' . $this->baseurl . '/index.php"'); ?>><span class="icon-joomla"></span></a>

			<a class="brand hidden-desktop hidden-tablet" href="<?php echo $mainPageUri; ?>" title="<?php echo JText::sprintf('TPL_ISIS_PREVIEW', $sitename); ?>" target="_blank"><?php echo JHtml::_('string.truncate', $sitename, 14, false, false); ?>
				<span class="icon-out-2 small"></span></a>

			<div<?php echo ($this->params->get('admin_menus') != '0') ? ' class="nav-collapse collapse"' : ''; ?>>
				<jdoc:include type="modules" name="menu" style="none" />
				<ul class="nav nav-user<?php echo ($this->direction == 'rtl') ? ' pull-left' : ' pull-right'; ?>">
					<li class="dropdown">
						<a class="<?php echo ($hidden ? ' disabled' : 'dropdown-toggle'); ?>" data-toggle="<?php echo ($hidden ? '' : 'dropdown'); ?>" <?php echo ($hidden ? '' : 'href="#"'); ?>><span class="icon-user"></span>
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<?php if (!$hidden) : ?>
								<li>
									<span>
										<span class="icon-user"></span>
										<strong><?php echo $user->name; ?></strong>
									</span>
								</li>
								<li class="divider"></li>
								<li>
									<a href="index.php?option=com_admin&amp;task=profile.edit&amp;id=<?php echo $user->id; ?>"><?php echo JText::_('TPL_ISIS_EDIT_ACCOUNT'); ?></a>
								</li>
								<li class="divider"></li>
								<li class="">
									<a href="<?php echo JRoute::_('index.php?option=com_login&task=logout&' . JSession::getFormToken() . '=1'); ?>"><?php echo JText::_('TPL_ISIS_LOGOUT'); ?></a>
								</li>
							<?php endif; ?>
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
<?php if ($displayHeader) : ?>
	<header class="header<?php echo $header_is_light ? ' header-inverse' : ''; ?>">
		<div class="container-logo">
			<img src="<?php echo $logo; ?>" class="logo" alt="<?php echo $sitename;?>" />
		</div>
		<div class="container-title">
			<jdoc:include type="modules" name="title" />
		</div>
	</header>
<?php endif; ?>
<?php if ((!$statusFixed) && ($this->countModules('status'))) : ?>
	<!-- Begin Status Module -->
	<div id="status" class="navbar status-top hidden-phone">
		<div class="btn-toolbar">
			<jdoc:include type="modules" name="status" style="no" />
		</div>
		<div class="clearfix"></div>
	</div>
	<!-- End Status Module -->
<?php endif; ?>
<?php if (!$cpanel) : ?>
	<!-- Subheader -->
	<a class="btn btn-subhead" data-toggle="collapse" data-target=".subhead-collapse"><?php echo JText::_('TPL_ISIS_TOOLBAR'); ?>
		<span class="icon-wrench"></span></a>
	<div class="subhead-collapse collapse" id="isisJsData" data-tmpl-sticky="<?php echo $stickyBar; ?>" data-tmpl-offset="<?php echo $offset; ?>">
		<div class="subhead">
			<div class="container-fluid">
				<div id="container-collapse" class="container-collapse"></div>
				<div class="row-fluid">
					<div class="span12">
						<jdoc:include type="modules" name="toolbar" style="no" />
					</div>
				</div>
			</div>
		</div>
	</div>
<?php else : ?>
	<div style="margin-bottom: 20px"></div>
<?php endif; ?>
<!-- container-fluid -->
<div class="container-fluid container-main">
	<section id="content">
		<!-- Begin Content -->
		<jdoc:include type="modules" name="top" style="xhtml" />
		<div class="row-fluid">
			<?php if ($showSubmenu) : ?>
			<div class="span2">
				<jdoc:include type="modules" name="submenu" style="none" />
			</div>
			<div class="span10">
				<?php else : ?>
				<div class="span12">
					<?php endif; ?>
					<jdoc:include type="message" />
					<jdoc:include type="component" />
				</div>
			</div>
			<?php if ($this->countModules('bottom')) : ?>
				<jdoc:include type="modules" name="bottom" style="xhtml" />
			<?php endif; ?>
			<!-- End Content -->
	</section>

	<?php if (!$this->countModules('status') || (!$statusFixed && $this->countModules('status'))) : ?>
		<footer class="footer">
			<p class="text-center">
				<jdoc:include type="modules" name="footer" style="no" />
				&copy; <?php echo $sitename; ?> <?php echo date('Y'); ?></p>
		</footer>
	<?php endif; ?>
</div>
<?php if (($statusFixed) && ($this->countModules('status'))) : ?>
	<!-- Begin Status Module -->
	<div id="status" class="navbar navbar-fixed-bottom hidden-phone">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<p>
					<jdoc:include type="modules" name="footer" style="no" />
					&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
				</p>

			</div>
			<jdoc:include type="modules" name="status" style="no" />
		</div>
	</div>
	<!-- End Status Module -->
<?php endif; ?>
<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
