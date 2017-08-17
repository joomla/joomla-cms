<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/** @var JDocumentHtml $this */

$app   = JFactory::getApplication();
$lang  = JFactory::getLanguage();
$user  = JFactory::getUser();
$input = $app->input;

// Detecting Active Variables
$option      = $input->get('option', '');
$view        = $input->get('view', '');
$layout      = $input->get('layout', '');
$task        = $input->get('task', '');
$itemid      = $input->get('Itemid', '');
$sitename    = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');
$hidden      = $app->input->get('hidemainmenu');
$logoLg      = $this->baseurl . '/templates/' . $this->template . '/images/logo.svg';
$logoSm      = $this->baseurl . '/templates/' . $this->template . '/images/logo-icon.svg';

// Alerts
JHtml::_('webcomponent', ['joomla-alert' => 'system/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#1c3d5c">
	<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>

	<link href="<?php echo 'templates/' . $this->template . '/favicon.ico'; ?>" rel="shortcut icon" type="image/vnd.microsoft.icon">
	<link href="<?php echo 'templates/' . $this->template . '/css/bootstrap.min.css'; ?>" rel="stylesheet">
	<link href="<?php echo 'templates/' . $this->template . '/css/font-awesome.min.css'; ?>" rel="stylesheet">
	<link href="<?php echo 'templates/' . $this->template . '/css/template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css'; ?>" rel="stylesheet">

	<?php $userCss = 'administrator/templates/' . $this->template . '/css/user.css'; ?>
	<?php if (is_file(JPATH_ROOT . $userCss)) : ?>
		<link href="<?php echo $userCss; ?>" rel="stylesheet">
	<?php endif; ?>

	<?php $langCss = 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css'; ?>
	<?php if (is_file(JPATH_ROOT . $langCss)) : ?>
		<link href="<?php echo $langCss; ?>" rel="stylesheet">
	<?php endif; ?>

	<script src="/media/system/js/core.min.js"></script>
	<script src="<?php echo 'templates/' . $this->template . '/js/template.js'; ?>"></script>
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid; ?>">

	<noscript>
		<div class="alert alert-danger" role="alert">
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
		</div>
	</noscript>

	<?php // Wrapper ?>
	<div id="wrapper" class="wrapper<?php echo $hidden ? '0' : ''; ?>">

		<?php // Sidebar ?>
		<?php if (!$hidden) : ?>
		<div id="sidebar-wrapper" class="sidebar-wrapper" <?php echo $hidden ? 'data-hidden="' . $hidden . '"' :''; ?>>
			<div id="main-brand" class="main-brand align-items-center">
				<a href="<?php echo JRoute::_('index.php'); ?>" aria-label="<?php echo JText::_('TPL_BACK_TO_CONTROL_PANEL'); ?>">
					<img src="<?php echo $logoLg; ?>" class="logo" alt="<?php echo $sitename;?>">
				</a>
			</div>
			<?php // Display menu modules ?>
			<?php $this->menumodules = JModuleHelper::getModules('menu'); ?>
			<?php foreach ($this->menumodules as $menumodule) : ?>
				<?php $output = JModuleHelper::renderModule($menumodule, array('style' => 'none')); ?>
				<?php $params = new Registry($menumodule->params); ?>
				<?php echo $output; ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php // Header ?>
		<header id="header" class="header">
			<div class="container-fluid">
				<div class="d-flex justify-content-end">
					<div class="d-flex col">
						<div class="menu-collapse">
							<a id="menu-collapse" class="menu-toggle" href="#">
								<span class="menu-toggle-icon fa fa-chevron-left fa-fw" aria-hidden="true"></span>
								<span class="sr-only"><?php echo JText::_('TPL_ATUM_CONTROL_PANEL_MENU'); ?></span>
							</a>
						</div>

						<div class="container-title">
							<jdoc:include type="modules" name="title" />
						</div>
					</div>

					<div class="ml-auto">
						<ul class="nav text-center">
							<li class="nav-item">
								<a class="nav-link" href="<?php echo JUri::root(); ?>" title="<?php echo JText::sprintf('TPL_ATUM_PREVIEW', $sitename); ?>" target="_blank">
									<span class="fa fa-external-link" aria-hidden="true"></span>
									<span class="sr-only"><?php echo JHtml::_('string.truncate', $sitename, 28, false, false); ?></span>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link dropdown-toggle" href="<?php echo JRoute::_('index.php?option=com_messages'); ?>" title="<?php echo JText::_('TPL_ATUM_PRIVATE_MESSAGES'); ?>">
									<span class="fa fa-envelope-o" aria-hidden="true"></span>
									<span class="sr-only"><?php echo JText::_('TPL_ATUM_PRIVATE_MESSAGES'); ?></span>
									<?php $countUnread = JFactory::getSession()->get('messages.unread'); ?>
									<?php if ($countUnread > 0) : ?>
										<span class="badge badge-pill badge-success"><?php echo $countUnread; ?></span>
									<?php endif; ?>
								</a>
							</li>
							<?php
								try
								{
									$messagesModel = new \Joomla\Component\Postinstall\Administrator\Model\Messages(['ignore_request' => true]);
									$messages      = $messagesModel->getItems();
								}
								catch (RuntimeException $e)
								{
									$messages = [];

									// Still render the error message from the Exception object
									JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
								}
								$lang->load('com_postinstall', JPATH_ADMINISTRATOR, 'en-GB', true);
							?>
							<?php if ($user->authorise('core.manage', 'com_postinstall')) : ?>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" title="<?php echo JText::_('TPL_ATUM_POST_INSTALLATION_MESSAGES'); ?>">
									<span class="fa fa-bell-o" aria-hidden="true"></span>
									<?php if (count($messages) > 0) : ?>
										<span class="badge badge-pill badge-success"><?php echo count($messages); ?></span>
									<?php endif; ?>
								</a>
								<div class="dropdown-menu dropdown-menu-right dropdown-notifications">
									<div class="list-group">
										<?php if (empty($messages)) : ?>
										<p class="list-group-item text-center">
											<strong><?php echo JText::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?></strong>
										</p>
										<?php endif; ?>
										<?php foreach ($messages as $message) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_postinstall&amp;eid=700'); ?>" class="list-group-item list-group-item-action">
											<h5 class="list-group-item-heading"><?php echo JHtml::_('string.truncate', JText::_($message->title_key), 28, false, false); ?></h5>
											<p class="list-group-item-text small">
												<?php echo JHtml::_('string.truncate', JText::_($message->description_key), 120, false, false); ?>
											</p>
										</a>
										<?php endforeach; ?>
									</div>
								</div>
							</li>
							<?php endif; ?>
							<li class="nav-item dropdown header-profile">
								<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
									<span class="fa fa-user-o" aria-hidden="true"></span>
									<span class="sr-only"><?php echo JText::_('TPL_ATUM_ADMIN_USER_MENU'); ?></span>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<div class="dropdown-item header-profile-user">
										<span class="fa fa-user" aria-hidden="true"></span>
										<?php echo $user->name; ?>
									</div>
									<?php $route = 'index.php?option=com_admin&amp;task=profile.edit&amp;id=' . $user->id; ?>
									<a class="dropdown-item" href="<?php echo JRoute::_($route); ?>">
										<?php echo JText::_('TPL_ATUM_EDIT_ACCOUNT'); ?></a>
									<a class="dropdown-item" href="<?php echo JRoute::_('index.php?option=com_login&task=logout&'
										. JSession::getFormToken() . '=1') ?>"><?php echo JText::_('TPL_ATUM_LOGOUT'); ?></a>
								</div>
							</li>
						</ul>
					</div>

				</div>
			</div>
		</header>

		<?php // container-fluid ?>
		<div class="container-fluid container-main">
			<section id="content" class="content">
				<?php // Begin Content ?>
				<div class="row">

					<div class="col-md-12">
						<jdoc:include type="message" />
						<h1><?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
						<blockquote class="blockquote">
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
						<p><a href="<?php echo $this->baseurl; ?>" class="btn btn-secondary"><span class="fa fa-dashboard" aria-hidden="true"></span>
							<?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a></p>
					</div>

				</div>
				<?php // End Content ?>
			</section>
		</div>

	</div>

</body>
</html>
