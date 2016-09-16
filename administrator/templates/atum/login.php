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
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<jdoc:include type="head" />
	<style>
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

<body class="site <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " itemid-" . $itemid . " "; ?>">
	<!-- Container -->
	<div class="container">
		<div id="content">
			<!-- Begin Content -->
			<div id="element-box" class="login card card-block">
				<img class="card-img-top" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/joomla.png" alt="<?php echo $sitename; ?>" />
				<h2 class="text-xs-center"><?php echo $sitename; ?></h2>
				<hr>
				<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
			<noscript>
				<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
			</noscript>
			<!-- End Content -->
		</div>
	</div>
	<div class="navbar navbar-fixed-bottom hidden-sm-down">
		<p class="pull-sm-right">
			&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
		</p>
		<a class="login-joomla hasTooltip" href="https://www.joomla.org" target="_blank" title="<?php echo JHtml::tooltipText('TPL_ATUM_ISFREESOFTWARE'); ?>"><span class="icon-joomla"></span></a>
		<a href="<?php echo JUri::root(); ?>" target="_blank" class="pull-sm-left"><span class="icon-out-2"></span> <?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE'); ?></a>
	</div>
	<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
