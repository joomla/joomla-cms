<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app  = JFactory::getApplication();
$doc  = JFactory::getDocument();
$lang = JFactory::getLanguage();

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.tooltip');
$doc->addScriptVersion(JUri::root() . 'media/vendor/flying-focus-a11y/js/flying-focus.min.js');

// Add Stylesheets
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/template.min.css');

// Load specific language related CSS
$file = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';

if (is_file($file))
{
	$doc->addStyleSheet($file);
}

// Load custom.css
$file = 'templates/' . $this->template . '/css/custom.css';

if (is_file($file))
{
	$doc->addStyleSheetVersion($file);
}

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="theme-color" content="#1c3d5c" />
	<jdoc:include type="head" />
	<style>
		.login-initial {
			display: none;
		}
		<?php // Check if debug is on ?>
		<?php if ($app->get('debug_lang', 1) || $app->get('debug', 1)) : ?>
		.view-login .container {
			position: static;
			margin-top: 20px;
			margin-left: auto;
			margin-right: auto;
		}
		.view-login .navbar-fixed-bottom {
			display: none;
		}
		<?php endif; ?>
	</style>
</head>

<body class="site <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid . ' '; ?>">
	<?php // Container ?>
	<div class="container">
		<div class="login-logo">
			<img class="card-img-top" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo.svg" alt="<?php echo $sitename; ?>" />
		</div>
		<div id="content">
			<noscript>
				<div class="alert alert-danger" role="alert">
					<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
				</div>
			</noscript>
			<?php // Begin Content ?>
			<div id="element-box" class="login card card-block">
				<h2 class="text-center m-t-1 m-b-2"><?php echo JText::_('MOD_LOGIN_LOGIN'); ?></h2>
				<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
			<?php // End Content ?>
		</div>
	</div>
	<nav class="navbar fixed-bottom hidden-xs-down">
		<ul class="nav nav-fill">
			<li class="nav-item">
				<a href="<?php echo JUri::root(); ?>" target="_blank" class="float-left"><span class="fa fa-external-link"></span> <?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE'); ?></a>
			</li>
			<li class="nav-item">
				<a class="login-joomla hasTooltip" href="https://www.joomla.org" target="_blank" title="<?php echo JHtml::tooltipText('TPL_ATUM_ISFREESOFTWARE'); ?>"><span class="fa fa-joomla"></span></a>
			</li>
			<li class="nav-item">	
				<span class="text-white float-right">&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?></span>
			</li>
		</ul>
	</nav>
	<jdoc:include type="modules" name="debug" style="none" />
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var formTmp = document.querySelector('.login-initial');
			if (formTmp) formTmp.style.display = 'block';
		});
	</script>
</body>
</html>
