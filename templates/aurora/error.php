<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.aurora
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentError $this */

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

// Alerts progressive enhancement
JHtml::_('webcomponent', ['joomla-alert' => 'system/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);
// Logo file or site title param
if ($params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $params->get('logoFile') . '" alt="' . $sitename . '">';
}
elseif ($params->get('siteTitle'))
{
	$logo = '<span title="' . $sitename . '">' . htmlspecialchars($params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
}
else
{
	$logo = '<img src="' . $this->baseurl . '/templates/' . $this->template . '/images/logo.svg' . '" class="logo d-inline-block align-top" alt="' . $sitename . '">';
}

// Container
$container = $params->get('fluidContainer') ? 'container-fluid' : 'container';
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>

	<link href="<?php echo $this->baseurl . '/templates/' . $this->template . '/favicon.ico'; ?>" rel="shortcut icon" type="image/vnd.microsoft.icon">
	<link href="<?php echo $this->baseurl . '/templates/' . $this->template . '/css/template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css'; ?>" rel="stylesheet">

	<?php $userCss = $this->baseurl . '/templates/' . $this->template . '/css/user.css'; ?>
	<?php if (is_file(JPATH_ROOT . $userCss)) : ?>
		<link href="<?php echo $userCss; ?>" rel="stylesheet">
	<?php endif; ?>

	<?php $langCss = $this->baseurl . '/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css'; ?>
	<?php if (is_file(JPATH_ROOT . $langCss)) : ?>
		<link href="<?php echo $langCss; ?>" rel="stylesheet">
	<?php endif; ?>

	<script src="/media/system/js/core.min.js"></script>
	<script src="/media/vendor/jquery/js/jquery.min.js"></script>
	<script src="/media/vendor/tether/js/tether.min.js"></script>
	<script src="/media/vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="<?php echo $this->baseurl . '/templates/' . $this->template . '/js/template.js'; ?>"></script>
</head>

<body class="site <?php echo $option
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
				<?php if ($params->get('siteDescription')) : ?>
					<div class="site-description"><?php echo htmlspecialchars($params->get('siteDescription')); ?></div>
				<?php endif; ?>
			</div>

			<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="<?php echo JText::_('TPL_AURORA_TOGGLE'); ?>">
				<span class="fa fa-bars"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbar">
				<?php echo $this->getBuffer('modules', 'menu', ['style' => 'none']); ?>
			</div>
		</nav>
	</header>

	<div class="container-main">

		<div class="container-component">
			<h1 class="page-header"><?php echo JText::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?></h1>
			<div class="card">
				<div class="card-block">
					<jdoc:include type="message" />
					<p><strong><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></strong></p>
					<p><?php echo JText::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></p>
					<ul>
						<li><?php echo JText::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
						<li><?php echo JText::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
						<li><?php echo JText::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
						<li><?php echo JText::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
					</ul>
					<p><?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?></p>
					<p><a href="<?php echo $this->baseurl; ?>/index.php" class="btn btn-secondary"><span class="fa fa-home" aria-hidden="true"></span> <?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>
					<hr>
					<p><?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>
					<blockquote>
						<span class="badge badge-default"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8');?>
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
									<p><strong><?php echo JText::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
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

	</div>

	<footer class="container-footer footer">
		<hr>
		<p class="float-right">
			<a href="#top" id="back-top" class="back-top">
				<span class="icon-arrow-up-4" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('TPL_AURORA_BACKTOTOP'); ?></span>
			</a>
		</p>
		<?php echo $this->getBuffer('modules', 'footer', ['style' => 'none']); ?>
	</footer>

	<?php echo $this->getBuffer('modules', 'debug', ['style' => 'none']); ?>

</body>
</html>

