<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.aurora
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$app  = JFactory::getApplication();
$lang = JFactory::getLanguage();

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
JHtml::_('bootstrap.framework');

// Add template js
JHtml::_('script', 'template.js', ['version' => 'auto', 'relative' => true]);

// Load custom Javascript file
JHtml::_('script', 'user.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
JHtml::_('stylesheet', 'template.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
JHtml::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Alerts progressive enhancement
JHtml::_('webcomponent', ['joomla-alert' => 'system/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);

// Load specific language related CSS
JHtml::_('stylesheet', 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

// Logo file or site title param
if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '">';
}
elseif ($this->params->get('siteTitle'))
{
	$logo = '<span title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
}
else
{
	$logo = '<img src="' . $this->baseurl . '/templates/' . $this->template . '/images/logo.svg' . '" class="logo d-inline-block align-top" alt="' . $sitename . '">';
}

// Header bottom margin
$headerMargin = !$this->countModules('banner') ? ' mb-4' : '';

// Container
$container = $params->get('fluidContainer') ? 'container-fluid' : 'container';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
</head>

<body class="site site-grid <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '');
	echo ($this->direction == 'rtl' ? ' rtl' : '');
?>">

	<header class="header full-width">
		<nav class="navbar navbar-toggleable-md navbar-full">
			<div class="navbar-brand">
				<a href="<?php echo $this->baseurl; ?>/">
					<?php echo $logo; ?>
				</a>
				<?php if ($this->params->get('siteDescription')) : ?>
					<div class="site-description"><?php echo htmlspecialchars($this->params->get('siteDescription')); ?></div>
				<?php endif; ?>
			</div>

			<?php if ($this->countModules('menu')) : ?>
				<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="<?php echo JText::_('TPL_AURORA_TOGGLE'); ?>">
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
		</nav>
	</header>

	<?php if ($this->countModules('banner')) : ?>
	<div class="container-banner full-width">
		<jdoc:include type="modules" name="banner" style="xhtml" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('top-a')) : ?>
	<div class="container-top-a">
		<jdoc:include type="modules" name="top-a" style="cardGrey" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('top-b')) : ?>
	<div class="container-top-b">
		<jdoc:include type="modules" name="top-b" style="card" />
	</div>
	<?php endif; ?>

	<div class="container-main">

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

	<?php if ($this->countModules('bottom-a')) : ?>
	<div class="container-bottom-a">
		<jdoc:include type="modules" name="bottom-a" style="cardGrey" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('bottom-b')) : ?>
	<div class="container-bottom-b">
		<jdoc:include type="modules" name="bottom-b" style="card" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('footer')) : ?>
	<footer class="container-footer footer">
		<hr>
		<p class="float-right">
			<a href="#top" id="back-top" class="back-top">
				<span class="icon-arrow-up-4" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('TPL_AURORA_BACKTOTOP'); ?></span>
			</a>
		</p>
		<jdoc:include type="modules" name="footer" style="none" />
	</footer>
	<?php endif; ?>

<jdoc:include type="modules" name="debug" style="none" />


</body>
</html>
