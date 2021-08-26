<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var JDocumentError $this */

$app = Factory::getApplication();
$wa  = $this->getWebAssetManager();

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

// Getting params from template
$params = $app->getTemplate(true)->params;

// Template path
$templatePath = 'templates/' . $this->template;

// Color Theme
$paramsColorName = $params->get('colorName', 'colors_standard');
$assetColorName  = 'theme.' . $paramsColorName;
$wa->registerAndUseStyle($assetColorName, $templatePath . '/css/global/' . $paramsColorName . '.css');
$this->getPreloadManager()->prefetch($wa->getAsset('style', $assetColorName)->getUri(), ['as' => 'style']);

// Use a font scheme if set in the template style options
$paramsFontScheme = $params->get('useFontScheme', false);

if ($paramsFontScheme)
{
	// Prefetch the stylesheet for the font scheme, actually we need to prefetch the font(s)
	$assetFontScheme  = 'fontscheme.' . $paramsFontScheme;
	$wa->registerAndUseStyle($assetFontScheme, $templatePath . '/css/global/' . $paramsFontScheme . '.css');
	$this->getPreloadManager()->prefetch($wa->getAsset('style', $assetFontScheme)->getUri(), ['as' => 'style']);
}

// Enable assets
$wa->usePreset('template.cassiopeia.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
	->useStyle('template.active.language')
	->useStyle('template.user')
	->useScript('template.user');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.cassiopeia.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Logo file or site title param
if ($params->get('logoFile'))
{
	$logo = '<img src="' . Uri::root() . htmlspecialchars($params->get('logoFile'), ENT_QUOTES) . '" alt="' . $sitename . '">';
}
elseif ($params->get('siteTitle'))
{
	$logo = '<span title="' . $sitename . '">' . htmlspecialchars($params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
}
else
{
	$logo = '<img src="' . $this->baseurl . '/' . $templatePath. '/images/logo.svg" class="logo d-inline-block" alt="' . $sitename . '">';
}

// Container
$wrapper = $params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
</head>

<body class="site-grid site error_site <?php echo $option
	. ' ' . $wrapper
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ' ' . $pageclass;
	echo ($this->direction == 'rtl' ? ' rtl' : '');
?>">
	<header class="header container-header full-width">
		<div class="grid-child">
			<div class="navbar-brand">
				<a class="brand-logo" href="<?php echo $this->baseurl; ?>/">
					<?php echo $logo; ?>
				</a>
				<?php if ($params->get('siteDescription')) : ?>
					<div class="site-description"><?php echo htmlspecialchars($params->get('siteDescription')); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ($this->countModules('menu') || $this->countModules('search')) : ?>
			<div class="grid-child container-nav">
				<?php if ($this->countModules('menu')) : ?>
					<nav class="navbar navbar-expand-md">
						<?php HTMLHelper::_('bootstrap.collapse', '.navbar-toggler'); ?>
						<button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="<?php echo Text::_('TPL_CASSIOPEIA_TOGGLE'); ?>">
							<span class="icon-menu" aria-hidden="true"></span>
						</button>
						<div class="collapse navbar-collapse" id="navbar">
							<jdoc:include type="modules" name="menu" style="none" />
						</div>
					</nav>
				<?php endif; ?>
				<?php if ($this->countModules('search')) : ?>
					<div class="container-search">
						<jdoc:include type="modules" name="search" style="none" />
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</header>

	<div class="grid-child container-component">
		<h1 class="page-header"><?php echo Text::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?></h1>
		<div class="card">
			<div class="card-body">
				<jdoc:include type="message" />
				<p><strong><?php echo Text::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></strong></p>
				<p><?php echo Text::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></p>
				<ul>
					<li><?php echo Text::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
					<li><?php echo Text::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
					<li><?php echo Text::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
					<li><?php echo Text::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
				</ul>
				<p><?php echo Text::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?></p>
				<p><a href="<?php echo $this->baseurl; ?>/index.php" class="btn btn-secondary"><span class="icon-home" aria-hidden="true"></span> <?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>
				<hr>
				<p><?php echo Text::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>
				<blockquote>
					<span class="badge bg-secondary"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
				</blockquote>
				<?php if ($this->debug) : ?>
					<div>
						<?php echo $this->renderBacktrace(); ?>
						<?php // Check if there are more Exceptions and render their data as well ?>
						<?php if ($this->error->getPrevious()) : ?>
							<?php $loop = true; ?>
							<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
							<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
							<?php $this->setError($this->_error->getPrevious()); ?>
							<?php while ($loop === true) : ?>
								<p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
								<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
								<?php echo $this->renderBacktrace(); ?>
								<?php $loop = $this->setError($this->_error->getPrevious()); ?>
							<?php endwhile; ?>
							<?php // Reset the main error object to the base error ?>
							<?php $this->setError($this->error); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if ($this->countModules('footer')) : ?>
	<footer class="container-footer footer full-width">
		<div class="grid-child">
			<jdoc:include type="modules" name="footer" style="none" />
		</div>
	</footer>
	<?php endif; ?>

	<jdoc:include type="modules" name="debug" style="none" />

</body>
</html>
