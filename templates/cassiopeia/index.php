<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var JDocumentHtml $this */

$app  = Factory::getApplication();
$lang = Factory::getLanguage();

// Getting params from template
$params = $app->getTemplate(true)->params;

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

// Add JavaScript Frameworks
HTMLHelper::_('bootstrap.framework');

// Add template js
HTMLHelper::_('script', 'template.js', ['version' => 'auto', 'relative' => true]);

// Load custom Javascript file
HTMLHelper::_('script', 'user.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
HTMLHelper::_('stylesheet', 'template.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Alerts progressive enhancement
HTMLHelper::_('webcomponent', ['joomla-alert' => 'vendor/joomla-custom-elements/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

// Logo file or site title param
if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . Uri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '">';
}
elseif ($this->params->get('siteTitle'))
{
	$logo = '<span title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
}
else
{
	$logo = '<img src="' . $this->baseurl . '/templates/' . $this->template . '/images/logo.svg' . '" class="logo d-inline-block" alt="' . $sitename . '">';
}

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
</head>

<body class="site-grid site <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '');
	echo ($this->direction == 'rtl' ? ' rtl' : '');
?>">

	<header class="container-header full-width">
		<div class="wrapper">
			<nav class="navbar navbar-expand-lg">
				<div class="navbar-brand">
					<a href="<?php echo $this->baseurl; ?>/">
						<?php echo $logo; ?>
					</a>
					<?php if ($this->params->get('siteDescription')) : ?>
						<div class="site-description"><?php echo htmlspecialchars($this->params->get('siteDescription')); ?></div>
					<?php endif; ?>
				</div>

				<?php if ($this->countModules('menu') || $this->countModules('search')) : ?>
					<button class="navbar-toggler navbar-toggler-right" type="button" aria-hidden="true" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="<?php echo Text::_('TPL_CASSIOPEIA_TOGGLE'); ?>">
						<span class="fa fa-bars"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbar">
						<jdoc:include type="modules" name="menu" style="none" />
						<?php if ($this->countModules('search')) : ?>
							<div class="form-inline">
								<jdoc:include type="modules" name="search" style="none" />
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
<<<<<<< HEAD
			</div>
		</nav>
		<?php if ($this->countModules('banner')) : ?>
		<div class="container-banner">
			<jdoc:include type="modules" name="banner" style="xhtml" />
=======
			</div>
		</nav>
		<?php if ($this->countModules('banner')) : ?>
		<div class="container-banner">
			<div class="wrapper">
				<jdoc:include type="modules" name="banner" style="xhtml" />
			</div>
>>>>>>> fdb479d26d2959993fc088e88ed56de716d8ce40
		</div>
		<?php endif; ?>
		<div class="header-shadow"></div>
		<div class="header-shape-bottom">
			<canvas width="736" height="15"></canvas>
			<svg class="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 736 15">
				<path d="M1040,301V285s-75,12-214,12-284-26-524,0v4Z" transform="translate(-302 -285)" fill="#fafafa"/>
			</svg>
		</div>
	</header>

	<jdoc:include type="modules" name="top" />

	<main class="container-main">
		<div class="wrapper">
			<?php if ($this->countModules('sidebar-left')) : ?>
			<div class="container-sidebar-left">
				<jdoc:include type="modules" name="sidebar-left" style="default" />
			</div>
			<?php endif; ?>

			<div class="container-component">
				<jdoc:include type="modules" name="main-top" style="cardGrey" />
				<jdoc:include type="message" />
				<jdoc:include type="component" />
				<jdoc:include type="modules" name="breadcrumbs" style="none" />
				<jdoc:include type="modules" name="main-bottom" style="cardGrey" />
			</div>

			<?php if ($this->countModules('sidebar-right')) : ?>
			<div class="container-sidebar-right">
				<jdoc:include type="modules" name="sidebar-right" style="default" />
			</div>
			<?php endif; ?>
		</div>
	</main>

	<jdoc:include type="modules" name="bottom" />

	<?php if ($this->countModules('footer')) : ?>
	<footer class="container-footer footer">
		<div class="wrapper">
			<hr>
			<p class="float-right">
				<a href="#top" id="back-top" class="back-top">
					<span class="icon-arrow-up-4" aria-hidden="true"></span>
					<span class="sr-only"><?php echo Text::_('TPL_CASSIOPEIA_BACKTOTOP'); ?></span>
				</a>
			</p>
			<jdoc:include type="modules" name="footer" style="none" />
		</div>
	</footer>
	<?php endif; ?>

	<jdoc:include type="modules" name="debug" style="none" />

</body>
</html>
