<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/** @var JDocumentHtml $this */

$app  = Factory::getApplication();
$lang = Factory::getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'font-awesome.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto', 'relative' => true]);

// Alerts
HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-alert.min.js', ['relative' => true, 'version' => 'auto']);


// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

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
</head>

<body class="site <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid . ' '; ?>">
	<?php // Container ?>
	<div class="login-bg-grad"></div>
	<div class="d-flex justify-content-center align-items-center h-100">
		<div class="login">
			<div class="login-logo">
				<img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo-joomla-white.svg" alt="<?php echo $sitename; ?>">
			</div>
			<div id="content">
				<noscript>
					<div class="alert alert-danger" role="alert">
						<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
				<?php // Begin Content ?>
				<div id="element-box" class="login-box">
					<div class="login-box-header">
						<h1 class="text-center m-0"><?php echo $sitename; ?></h1>
					</div>
					<div class="login-box-body">
						<jdoc:include type="message" />
						<jdoc:include type="component" />
					</div>
				</div>
				<?php // End Content ?>
			</div>
			<div class="mt-4 d-none d-md-flex justify-content-between">
				<a href="<?php echo Uri::root(); ?>" target="_blank" class="text-white"><span class="fa fa-eye mr-1" aria-hidden="true"></span><?php echo Text::_('TPL_ATUM_VIEW_SITE'); ?></a>
				<span class="text-white">&nbsp;&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?></span>
			</div>
		</div>
	</div>

	<jdoc:include type="modules" name="debug" style="none" />

	<jdoc:include type="scripts" />
</body>
</html>
