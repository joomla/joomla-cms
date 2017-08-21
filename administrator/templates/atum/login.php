<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$app  = JFactory::getApplication();
$lang = JFactory::getLanguage();

// Add JavaScript Frameworks
JHtml::_('script', 'media/vendor/flying-focus-a11y/js/flying-focus.min.js', ['version' => 'auto']);

// Load template CSS file
JHtml::_('stylesheet', 'bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
JHtml::_('stylesheet', 'font-awesome.min.css', ['version' => 'auto', 'relative' => true]);
JHtml::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css', ['version' => 'auto', 'relative' => true]);

// Alerts
JHtml::_('webcomponent', ['joomla-alert' => 'system/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);


// Load custom CSS file
JHtml::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Load specific language related CSS
JHtml::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
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
			<img class="card-img-top" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo.svg" alt="<?php echo $sitename; ?>">
		</div>
		<div id="content">
			<noscript>
				<div class="alert alert-danger" role="alert">
					<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
				</div>
			</noscript>
			<?php // Begin Content ?>
			<div id="element-box" class="login card card-block">
				<h1 class="text-center mt-1 mb-4"><?php echo JText::_('MOD_LOGIN_LOGIN_TITLE'); ?></h1>
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
				<a class="login-joomla hasTooltip" href="https://www.joomla.org" target="_blank" title="<?php echo JHtml::tooltipText('TPL_ATUM_ISFREESOFTWARE'); ?>">
					<span class="fa fa-joomla"></span>
					<span class="sr-only"><?php echo JText::_('TPL_ATUM_GOTO_JOOMLA_HOME_PAGE'); ?></span>
				</a>
			</li>
			<li class="nav-item">
				<span class="text-white float-right">&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?></span>
			</li>
		</ul>
	</nav>

	<jdoc:include type="modules" name="debug" style="none" />

	<jdoc:include type="scripts" />
	<script>
		(function() {
			var formTmp = document.querySelector('.login-initial');
			if (formTmp) {
				formTmp.style.display = 'block';
				if (!document.querySelector('joomla-alert')) {
					document.getElementById('mod-login-username').focus();
				}
			}
		})();
	</script>
</body>
</html>
