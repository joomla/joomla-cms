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

/** @var JDocumentHtml $this */
// here we go
$app  = Factory::getApplication();
$lang = $app->getLanguage();

require_once __DIR__ . '/Service/HTML/Atum.php';

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Load template JS file
HTMLHelper::_('script', 'media/templates/' . $this->template . '/js/template.min.js', ['version' => 'auto']);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'fontawesome.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'custom.css', ['version' => 'auto', 'relative' => true]);

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', ['version' => 'auto']);

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', 'default');
$sitename = $app->get('sitename');

// Template params
$siteLogo  = $this->params->get('siteLogo')
	? JUri::root() . $this->params->get('siteLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logo-joomla-blue.svg';
$loginLogo = $this->params->get('loginLogo')
	? JUri::root() . $this->params->get('loginLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';
$smallLogo = $this->params->get('smallLogo')
	? JUri::root() . $this->params->get('smallLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

$logoAlt = htmlspecialchars($this->params->get('altSiteLogo', ''), ENT_COMPAT, 'UTF-8');
$logoSmallAlt = htmlspecialchars($this->params->get('altSmallLogo', ''), ENT_COMPAT, 'UTF-8');

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');
$this->addScriptDeclaration('cssVars();');

// Set page title
$this->setTitle(Text::sprintf('TPL_ATUM_LOGIN_SITE_TITLE', $sitename));

// Opacity must be set before displaying the DOM, so don't move to a CSS file
$css = '
	.container-main > * {
		opacity: 0;
	}
	.sidebar-wrapper > * {
		opacity: 0;
	}
';
$this->addStyleDeclaration($css);

HTMLHelper::getServiceRegistry()->register('atum', 'JHtmlAtum');
HTMLHelper::_('atum.rootcolors', $this->params);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout; ?>">

<noscript>
	<div class="alert alert-danger" role="alert">
		<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</div>
</noscript>


<header id="header" class="header">
	<div class="d-flex">
		<div class="header-title d-flex">
			<div class="d-flex">
				<?php // No home link in edit mode (so users can not jump out) and control panel (for a11y reasons) ?>
				<div class="logo">
					<img src="<?php echo $siteLogo; ?>" alt="<?php echo $logoAlt; ?>">
					<img class="logo-small" src="<?php echo $smallLogo; ?>" alt="<?php echo $logoSmallAlt; ?>">
				</div>
			</div>
			<jdoc:include type="modules" name="title" />
		</div>
		<div class="header-items d-flex">
			<jdoc:include type="modules" name="status" style="header-item" />
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<div class="container-fluid container-main order-1">
		<section id="content" class="content h-100">
			<main class="d-flex justify-content-center align-items-center h-100">
				<div class="login">
					<div class="main-brand d-flex align-items-center justify-content-center">
						<img src="<?php echo $loginLogo; ?>"
							 alt="<?php echo htmlspecialchars($this->params->get('altLoginLogo', ''), ENT_COMPAT, 'UTF-8'); ?>">
					</div>
					<jdoc:include type="component" />
				</div>
			</main>
		</section>

		<div class="notify-alerts">
			<jdoc:include type="message" />
		</div>
	</div>

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper order-0">
		<div id="main-brand" class="main-brand">
			<h1><?php echo Text::_('TPL_ATUM_BACKEND_LOGIN'); ?></h1>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body" />
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none" />
<jdoc:include type="scripts" />
</body>
</html>
