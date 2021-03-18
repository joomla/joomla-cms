<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app  = Factory::getApplication();
$lang = $app->getLanguage();
$wa   = $this->getWebAssetManager();

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu->getParams()->get('pageclass_sfx');

// Enable assets
$wa->usePreset('template.cassiopeia.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
	->useStyle('template.active.language')
	->useStyle('template.user')
	->useScript('template.user');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.cassiopeia.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Preload the stylesheet for the font, actually we need to preload the font
$this->getPreloadManager()->preload('https://fonts.googleapis.com/css?family=Fira+Sans:400', array('as' => 'stylesheet'));

// Logo file or site title param
if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . Uri::root() . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES) . '" alt="' . $sitename . '">';
}
elseif ($this->params->get('siteTitle'))
{
	$logo = '<span title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
}
else
{
	$logo = '<img src="' . $this->baseurl . '/templates/' . $this->template . '/images/logo.svg" class="logo d-inline-block" alt="' . $sitename . '">';
}

$hasClass = '';

if ($this->countModules('sidebar-left'))
{
	$hasClass .= ' has-sidebar-left';
}

if ($this->countModules('sidebar-right'))
{
	$hasClass .= ' has-sidebar-right';
}

// Container
$wrapper = $this->params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

$stickyHeader = $this->params->get('stickyHeader') ? 'position-sticky sticky-top' : '';

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
</head>

<body class="site-grid site <?php echo $option
	. ' ' . $wrapper
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ' ' . $pageclass
	. $hasClass;
	echo ($this->direction == 'rtl' ? ' rtl' : '');
?>">
	<div class="grid-child container-header full-width <?php echo $stickyHeader; ?>">
		<header class="header">
			<nav class="grid-child navbar navbar-expand-lg">
				<div class="navbar-brand">
					<a href="<?php echo $this->baseurl; ?>/">
						<?php echo $logo; ?>
					</a>
					<?php if ($this->params->get('siteDescription')) : ?>
						<div class="site-description"><?php echo htmlspecialchars($this->params->get('siteDescription')); ?></div>
					<?php endif; ?>
				</div>

				<?php if ($this->countModules('menu') || $this->countModules('search')) : ?>
					<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="<?php echo Text::_('TPL_CASSIOPEIA_TOGGLE'); ?>">
						<span class="fas fa-bars" aria-hidden="true"></span>
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
			<?php if ($this->countModules('banner')) : ?>
			<div class="grid-child container-banner">
				<jdoc:include type="modules" name="banner" style="html5" />
			</div>
			<?php endif; ?>
		</header>
	</div>

	<?php if ($this->countModules('top-a')) : ?>
	<div class="grid-child container-top-a">
		<jdoc:include type="modules" name="top-a" style="cardGrey" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('top-b')) : ?>
	<div class="grid-child container-top-b">
		<jdoc:include type="modules" name="top-b" style="card" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('sidebar-left')) : ?>
	<div class="grid-child container-sidebar-left">
		<jdoc:include type="modules" name="sidebar-left" style="default" />
	</div>
	<?php endif; ?>

	<div class="grid-child container-component">
		<jdoc:include type="modules" name="main-top" style="cardGrey" />
		<jdoc:include type="message" />
		<jdoc:include type="modules" name="breadcrumbs" style="none" />
		<jdoc:include type="component" />
		<jdoc:include type="modules" name="main-bottom" style="cardGrey" />
	</div>

	<?php if ($this->countModules('sidebar-right')) : ?>
	<div class="grid-child container-sidebar-right">
		<jdoc:include type="modules" name="sidebar-right" style="default" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('bottom-a')) : ?>
	<div class="grid-child container-bottom-a">
		<jdoc:include type="modules" name="bottom-a" style="cardGrey" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('bottom-b')) : ?>
	<div class="grid-child container-bottom-b">
		<jdoc:include type="modules" name="bottom-b" style="card" />
	</div>
	<?php endif; ?>

	<?php if ($this->countModules('footer')) : ?>
	<footer class="grid-child container-footer footer">
		<hr>
		<p class="float-right">
			<a href="#top" id="back-top" class="back-top">
				<span class="fas fa-arrow-up" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('TPL_CASSIOPEIA_BACKTOTOP'); ?></span>
			</a>
		</p>
		<jdoc:include type="modules" name="footer" style="none" />
	</footer>
	<?php endif; ?>

	<jdoc:include type="modules" name="debug" style="none" />

</body>
</html>
