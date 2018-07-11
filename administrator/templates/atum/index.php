<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

/** @var JDocumentHtml $this */

$app   = Factory::getApplication();
$lang  = Factory::getLanguage();
$input = $app->input;

// Detecting Active Variables
$option      = $input->get('option', '');
$view        = $input->get('view', '');
$layout      = $input->get('layout', '');
$task        = $input->get('task', '');
$itemid      = $input->get('Itemid', '');
$sitename    = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');
$cpanel      = $option === 'com_cpanel';
$hidden      = $app->input->get('hidemainmenu');
$logo        = $this->baseurl . '/templates/' . $this->template . '/images/logo.svg';
$logoBlue    = $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

// Add JavaScript
HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'font-awesome.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Alerts
HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-alert.min.js', ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

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
	<jdoc:include type="scripts" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid; ?>">

	<noscript>
		<div class="alert alert-danger" role="alert">
			<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
		</div>
	</noscript>

	<?php // Header ?>
	<header id="header" class="header">
		<div class="d-flex align-items-center">
			<div class="header-title d-flex mr-auto">
				<div class="d-flex">
					<a class="logo" href="<?php echo Route::_('index.php'); ?>" aria-label="<?php echo Text::_('TPL_BACK_TO_CONTROL_PANEL'); ?>">
						<img src="<?php echo $logoBlue; ?>" alt="<?php echo $sitename; ?>">
					</a>
				</div>
				<jdoc:include type="modules" name="title" />
			</div>
			<div class="header-items d-flex ml-auto">
				<jdoc:include type="modules" name="status" style="no" />
			</div>
		</div>
	</header>

	<?php // Wrapper ?>
	<div id="wrapper" class="d-flex wrapper<?php echo $hidden ? '0' : ''; ?>">

		<?php // Sidebar ?>
		<?php if (!$hidden) : ?>
		<div id="sidebar-wrapper" class="sidebar-wrapper" <?php echo $hidden ? 'data-hidden="' . $hidden . '"' : ''; ?>>
			<jdoc:include type="modules" name="menu" style="none" />
			<div id="main-brand" class="main-brand d-flex align-items-center justify-content-center">
				<img src="<?php echo $logo; ?>" alt="<?php echo $sitename; ?>">
			</div>
		</div>
		<?php endif; ?>

		<?php // container-fluid ?>
		<div class="container-fluid container-main">
			<?php if (!$cpanel) : ?>
				<?php // Subheader ?>
				<a class="btn btn-subhead d-md-none d-lg-none d-xl-none" data-toggle="collapse" data-target=".subhead-collapse"><?php echo Text::_('TPL_ATUM_TOOLBAR'); ?>
					<span class="icon-wrench"></span></a>
				<div id="subhead" class="subhead">
						<div id="container-collapse" class="container-collapse"></div>
						<div class="row">
							<div class="col-md-12">
								<jdoc:include type="modules" name="toolbar" style="no" />
							</div>
					</div>
				</div>
			<?php endif; ?>
			<section id="content" class="content">
				<?php // Begin Content ?>
				<jdoc:include type="modules" name="top" style="xhtml" />
				<div class="row">
					<div class="col-md-12">
						<jdoc:include type="component" />
					</div>

					<?php if ($this->countModules('bottom')) : ?>
						<jdoc:include type="modules" name="bottom" style="xhtml" />
					<?php endif; ?>
				</div>
				<?php // End Content ?>
			</section>

			<div class="notify-alerts">
				<jdoc:include type="message" />
			</div>

		</div>

	</div>

	<jdoc:include type="modules" name="debug" style="none" />

</body>
</html>
