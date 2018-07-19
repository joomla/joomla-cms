<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var JDocumentError $this */

$app  = Factory::getApplication();
$lang = Factory::getLanguage();

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu->params->get('pageclass_sfx');

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
HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-alert.min.js', ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);

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

// Header bottom margin
$headerMargin = !$this->countModules('banner') ? ' mb-4' : '';

// Container
$wrapper = $this->params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

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
	. ' ' . $wrapper
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ' ' . $pageclass;
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
		</nav>
		<?php if ($this->countModules('banner')) : ?>
		<div class="grid-child container-banner">
			<jdoc:include type="modules" name="banner" style="xhtml" />
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

	<div class="grid-child container-main">

		<?php if ($this->countModules('sidebar-left')) : ?>
		<div class="container-sidebar-left">
			<jdoc:include type="modules" name="sidebar-left" style="default" />
		</div>
		<?php endif; ?>

		<div class="container-component">
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
					<p><a href="<?php echo $this->baseurl; ?>/index.php" class="btn btn-secondary"><span class="fa fa-home" aria-hidden="true"></span> <?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>
					<hr>
					<p><?php echo Text::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>
					<blockquote>
						<span class="badge badge-secondary"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
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

		<?php if ($this->countModules('sidebar-right')) : ?>
		<div class="container-sidebar-right">
			<jdoc:include type="modules" name="sidebar-right" style="default" />
		</div>
		<?php endif; ?>

	</div>

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
				<span class="icon-arrow-up-4" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('TPL_CASSIOPEIA_BACKTOTOP'); ?></span>
			</a>
		</p>
		<jdoc:include type="modules" name="footer" style="none" />
	</footer>
	<?php endif; ?>

	<jdoc:include type="modules" name="debug" style="none" />

</body>
</html>
