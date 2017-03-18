<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$lang            = JFactory::getLanguage();
$user            = JFactory::getUser();
$this->language  = $doc->language;
$this->direction = $doc->direction;
$input           = $app->input;

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');
$doc->addScriptVersion(JUri::root() . 'media/vendor/flying-focus-a11y/js/flying-focus.min.js');
$doc->addScriptVersion($this->baseurl . '/templates/' . $this->template . '/js/template.js');

// Add Stylesheets
$doc->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/template.min.css');

// Load specific language related CSS
$languageCss = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';

if (file_exists($languageCss) && filesize($languageCss) > 0)
{
	$doc->addStyleSheetVersion($languageCss);
}

// Load custom.css
$customCss = 'templates/' . $this->template . '/css/custom.css';

if (file_exists($customCss) && filesize($customCss) > 0)
{
	$doc->addStyleSheetVersion($customCss);
}

// Detecting Active Variables
$option      = $input->get('option', '');
$view        = $input->get('view', '');
$layout      = $input->get('layout', '');
$task        = $input->get('task', '');
$itemid      = $input->get('Itemid', '');
$sitename    = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');
$cpanel      = ($option === 'com_cpanel');
$hidden      = $app->input->get('hidemainmenu');
$logoLg      = $this->baseurl . '/templates/' . $this->template . '/images/logo.svg';
$logoSm      = $this->baseurl . '/templates/' . $this->template . '/images/logo-icon.svg';

// Set some meta data
$doc->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$doc->setMetaData('theme-color', '#1c3d5c');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/0.7.24/webcomponents-lite.min.js"></script>
	<link rel="import" href="/media/joomla-elements/html/bs4-alert.html">
	<jdoc:include type="head" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid; ?>">

	<noscript>
		<div class="alert alert-danger" role="alert">
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
		</div>
	</noscript>

	<?php // Wrapper ?>
	<div id="wrapper" class="wrapper<?php echo $hidden ? '0' : ''; ?> closed">

		<?php // Sidebar ?>
		<?php if (!$hidden) : ?>
		<div id="sidebar-wrapper" class="sidebar-wrapper" <?php echo $hidden ? 'data-hidden="' . $hidden . '"' :''; ?>>
			<div id="main-brand" class="main-brand align-items-center">
				<a href="<?php echo JRoute::_('index.php'); ?>" aria-label="<?php echo JText::_('TPL_BACK_TO_CONTROL_PANEL'); ?>">
					<img src="<?php echo $logoLg; ?>" class="logo" alt="<?php echo $sitename;?>">
				</a>
			</div>
			<jdoc:include type="modules" name="menu" style="none" />
		</div>
		<?php endif; ?>

		<?php // Header ?>
		<header id="header" class="header">
			<div class="container-fluid">
				<div class="text-center">
					<div class="menu-collapse hidden-lg-up">
						<a id="menu-collapse" class="menu-toggle" href="#">
							<span class="fa fa-bars fa-fw"></span>
						</a>
					</div>

					<a class="navbar-brand" href="<?php echo JUri::root(); ?>" title="<?php echo JText::sprintf('TPL_ATUM_PREVIEW', $sitename); ?>" target="_blank">
						<?php echo JHtml::_('string.truncate', $sitename, 28, false, false); ?>
						<span class="icon-out-2 small"></span>
					</a>

					<nav>
						<ul class="nav">
							<li class="nav-item">
								<a class="nav-link dropdown-toggle" href="<?php echo JRoute::_('index.php?option=com_messages'); ?>" title="<?php echo JText::_('TPL_ATUM_PRIVATE_MESSAGES'); ?>">
									<i class="fa fa-envelope"></i>
									<?php $countUnread = JFactory::getSession()->get('messages.unread'); ?>
									<?php if ($countUnread > 0) : ?>
										<span class="badge badge-pill badge-success"><?php echo $countUnread; ?></span>
									<?php endif; ?>
								</a>
							</li>
							<?php
								/*
								 * @TODO: Remove FOF call as it's being removed in core
								 */
								try
								{
									$messagesModel = FOFModel::getTmpInstance('Messages', 'PostinstallModel')->eid(700);
									$messages      = $messagesModel->getItemList();
								}
								catch (RuntimeException $e)
								{
									$messages = array();

									// Still render the error message from the Exception object
									JFactory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
								}
								$lang->load('com_postinstall', JPATH_ADMINISTRATOR, 'en-GB', true);
							?>
							<?php if ($user->authorise('core.manage', 'com_postinstall')) : ?>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" title="<?php echo JText::_('TPL_ATUM_POST_INSTALLATION_MESSAGES'); ?>">
									<i class="fa fa-bell"></i>
									<?php if (count($messages) > 0) : ?>
										<span class="badge badge-pill badge-success"><?php echo count($messages); ?></span>
									<?php endif; ?>
								</a>
								<div class="dropdown-menu dropdown-menu-right dropdown-notifications">
									<div class="list-group">
										<?php if (empty($messages)) : ?>
										<p class="list-group-item text-center">
											<b><?php echo JText::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?></b>
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
									<i class="fa fa-user"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<div class="dropdown-item header-profile-user">
										<i class="fa fa-user"></i>
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
					</nav>

				</div>
			</div>
		</header>
		<div class="container-title">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<jdoc:include type="modules" name="title" />
					</div>
				</div>
			</div>
		</div>

		<?php // container-fluid ?>
		<div class="container-fluid container-main">
			<!-- For demo purposes -->
			<hr>
			<hr>
			<h2>Bootstrap code</h2>

			<div class="alert alert-success" role="alert">
				<h4 class="alert-heading">Well done!</h4>
				<p>Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.</p>
				<p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
			</div>

			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<strong>Holy guacamole!</strong> You should check in on some of those fields below.
			</div>

			<h2>Custom element code</h2>

			<bs4-alert data-type="success" data-button="false">
				<h4 class="alert-heading">Well done!</h4>
				<p>Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.</p>
				<p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
			</bs4-alert>

			<bs4-alert data-type="warning" data-button="true">
				<strong>Holy guacamole!</strong> You should check in on some of those fields below.
			</bs4-alert>

			<p>The data-type and data-button can be (<strong>and should be</strong>) replaced by type and button</p>
			<p>This demo proves that custom elements and native bootstrap code can co-exist!</p>
			<!-- End of demo section -->
			<?php if (!$cpanel) : ?>
				<?php // Subheader ?>
				<a class="btn btn-subhead hidden-md-up" data-toggle="collapse" data-target=".subhead-collapse"><?php echo JText::_('TPL_ATUM_TOOLBAR'); ?>
					<span class="icon-wrench"></span></a>
				<div class="subhead-collapse" data-scroll="<?php echo $hidden; ?>">
					<div id="subhead" class="subhead">
						<div class="container-fluid">
							<div id="container-collapse" class="container-collapse"></div>
							<div class="row">
								<div class="col-md-12">
									<jdoc:include type="modules" name="toolbar" style="no" />
								</div>
							</div>
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

			<?php if (!$this->countModules('status')) : ?>
				<footer class="footer">
					<p class="text-center">
						<jdoc:include type="modules" name="footer" style="no" />
						&copy; <?php echo $sitename; ?> <?php echo date('Y'); ?></p>
				</footer>
			<?php endif; ?>

			<?php if ($this->countModules('status')) : ?>
				<?php // Begin Status Module ?>
				<nav id="status" class="status navbar fixed-bottom hidden-sm-down">
					<ul class="nav d-flex justify-content-start">
						<jdoc:include type="modules" name="status" style="no" />
						<li class="ml-auto">
							<jdoc:include type="modules" name="footer" style="no" />
							&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
						</li>
					</ul>
				</nav>
				<?php // End Status Module ?>
			<?php endif; ?>

			<div class="notify-alerts">
				<jdoc:include type="message" />
			</div>

		</div>

		<jdoc:include type="modules" name="debug" style="none" />

	</div>

</body>
</html>
