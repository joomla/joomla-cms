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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var JDocumentHtml $this */

$app         = Factory::getApplication();
$lang        = $app->getLanguage();
$input       = $app->input;
$wa          = $this->getWebAssetManager();

// Detecting Active Variables
$option      = $input->get('option', '');
$cpanel      = $option === 'com_cpanel';
$hidden      = $input->get('hidemainmenu');

// Set the class
$pageClass   = ['admin'];
$pageClass[] = $option;
$pageClass[] = 'view-' . $input->get('view', '');
$pageClass[] = 'layout-' . $input->get('layout', '');
$pageClass[] = 'task-' . $input->get('task', '');
$pageClass[] = 'itemid-' . $input->get('Itemid', '');

// Set the images paths
$logo        = $this->baseurl . '/templates/' . $this->template . '/images/logo.svg';
$logoBlue    = $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

// Enable assets
$wa->enableAsset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');
$this->addScriptDeclaration('cssVars();');

/**
 * Evaluate the contents of each block that needs to be included
 * The order of the blocks is essential
 * These lines should always follow any inclusion of assets
 *
 * The renderBlock method accepts the following parameters
 *
 * type:    {string} Can be component,modules,styles,metas,scripts,messages or any custom defined render
 * name:    {string} Used in modules to get the name of the module
 * attribs: {array}  Any attributes
 */
// Component is always first, Joomla is component driven
$component = $this->renderBlock('component');

// Then evaluating any modules
$title     = $this->renderBlock('modules', 'title');
$status    = $this->renderBlock('modules', 'status');
$menu      = $this->renderBlock('modules', 'menu');
$toolbar   = $this->renderBlock('modules', 'toolbar');
$top       = $this->renderBlock('modules', 'top');
$bottom    = $this->renderBlock('modules', 'bottom');
$debug     = $this->renderBlock('modules', 'debug');

// Then any messages
$message   = $this->renderBlock('message');

// Finally, evaluate metas, styles and scripts (always in that order)
$metas     = $this->renderBlock('metas');
$styles    = $this->renderBlock('styles');
$scripts   = $this->renderBlock('scripts');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <?php echo $metas; ?>
    <?php echo $styles; ?>
    <?php echo $scripts; ?>
</head>

<body class="admin <?php echo implode('', $pageClass); ?>">

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
						<img src="<?php echo $logoBlue; ?>" alt="">
					</a>
				</div>
				<?php echo $title; ?>
			</div>
			<div class="header-items d-flex ml-auto">
				<?php echo $status; ?>
			</div>
		</div>
	</header>

	<?php // Wrapper ?>
	<div id="wrapper" class="d-flex wrapper<?php echo $hidden ? '0' : ''; ?>">

		<?php // Sidebar ?>
		<?php if (!$hidden) : ?>
		<div id="sidebar-wrapper" class="sidebar-wrapper" <?php echo $hidden ? 'data-hidden="' . $hidden . '"' : ''; ?>>
			<?php echo $menu; ?>
			<div id="main-brand" class="main-brand d-flex align-items-center justify-content-center">
				<img src="<?php echo $logo; ?>" alt="">
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
								<?php echo $toolbar; ?>
							</div>
					</div>
				</div>
			<?php endif; ?>
			<section id="content" class="content">
				<?php // Begin Content ?>
				<?php echo $top; ?>
				<div class="row">
					<div class="col-md-12">
						<main>
							<?php echo $component; ?>
						</main>
					</div>
					<?php if ($this->countModules('bottom')) : ?>
						<?php echo $bottom; ?>
					<?php endif; ?>
				</div>
				<?php // End Content ?>
			</section>

			<div class="notify-alerts">
				<?php echo $message; ?>
			</div>
		</div>
	</div>
	<?php echo $debug; ?>
</body>
</html>
