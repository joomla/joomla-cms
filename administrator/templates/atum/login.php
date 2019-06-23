<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var JDocumentHtml $this */

$app  = Factory::getApplication();
$lang = Factory::getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'fontawesome.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto', 'relative' => true]);

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

// Template params
$showSitename = $this->params->get('showSitename', '1');
$loginLogo    = $this->params->get('loginLogo', '');

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');

// Set page title
$this->setTitle($sitename . ' - ' . Text::_('JACTION_LOGIN_ADMIN'));

$this->addScriptDeclaration('cssVars();')

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas"/>
	<jdoc:include type="styles"/>
</head>
<body class="site <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid . ' '; ?>">
	<?php // Container ?>
	<main class="d-flex justify-content-center align-items-center h-100">
		<div class="login-bg-grad"></div>
		<div class="login">
			<div class="login-logo">
				<img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo-joomla-white.svg"
					 alt="">
			</div>
			<div id="content">
				<noscript>
					<div class="alert alert-danger" role="alert">
						<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
				<h1 class="m-3 h4 text-light"><?php echo Text::_('TPL_ATUM_BACKEND_LOGIN'); ?></h1>
				<div id="element-box" class="login-box">
					<?php if ($showSitename || $loginLogo) : ?>
						<div class="p-4 bg-white text-center">
							<?php if ($showSitename) : ?>
								<h2 class="m-0 text-primary"><?php echo $sitename; ?></h2>
							<?php endif; ?>
							<?php if ($loginLogo) : ?>
								<img src="<?php echo Uri::root() . '/' . $loginLogo; ?>" class="img-fluid my-2" alt="">
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="p-4">
						<jdoc:include type="message"/>
						<jdoc:include type="component"/>
					</div>
				</div>
			</div>
			<div class="mt-4 d-none d-md-flex justify-content-between">
				<a href="<?php echo Uri::root(); ?>" target="_blank" class="text-white"><span
							class="fa fa-external-link-alt mr-1"
							aria-hidden="true"></span><?php echo Text::_('TPL_ATUM_VIEW_SITE'); ?></a> <span
						class="text-white">&nbsp;&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?></span>
			</div>
		</div>
	</main>
	<jdoc:include type="modules" name="debug" style="none"/>
	<jdoc:include type="scripts"/>
</body>
</html>
