<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentError $this */

$showRightColumn = 0;
$showleft        = 0;
$showbottom      = 0;

// Get params
$app         = JFactory::getApplication();
$params      = $app->getTemplate(true)->params;
$logo        = $params->get('logo');
$color       = $params->get('templatecolor');
$navposition = $params->get('navposition');
$format      = $app->input->getCmd('format', 'html');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta charset="utf-8" />
	<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
	<link href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" rel="stylesheet" />
	<link href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" rel="stylesheet" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/position.css" rel="stylesheet" media="screen" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/layout.css" rel="stylesheet" media="screen" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/print.css" rel="stylesheet" media="print" />
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/<?php echo htmlspecialchars($color); ?>.css" rel="stylesheet" />
	<?php $files = JHtml::_('stylesheet', 'general.css', array('relative' => true, 'returnPath' => true)); ?>
	<?php if ($files) : ?>
		<?php if (!is_array($files)) : ?>
			<?php $files = array($files); ?>
		<?php endif; ?>
	<?php foreach ($files as $file) : ?>
		<link href="<?php echo $file; ?>" rel="stylesheet" />
	<?php endforeach; ?>
	<?php endif; ?>
	<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/<?php echo htmlspecialchars($color, ENT_COMPAT, 'UTF-8'); ?>.css" rel="stylesheet" />
	<?php if ($this->direction === 'rtl') : ?>
		<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template_rtl.css" rel="stylesheet" />
		<?php if (file_exists(JPATH_SITE . '/templates/' . $this->template . '/css/' . $color . '_rtl.css')) : ?>
			<link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/<?php echo htmlspecialchars($color, ENT_COMPAT, 'UTF-8'); ?>_rtl.css" rel="stylesheet" />
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($app->get('debug_lang', '0') == '1' || $app->get('debug', '0') == '1') : ?>
		<link href="<?php echo JUri::root(true); ?>/media/cms/css/debug.css" rel="stylesheet" />
	<?php endif; ?>
	<!--[if lte IE 6]><link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/ieonly.css" rel="stylesheet" /><![endif]-->
	<!--[if IE 7]><link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/ie7only.css" rel="stylesheet" /><![endif]-->
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
	<style>
	<!--
		#errorboxbody
		{margin:30px}
		#errorboxbody h2
		{font-weight:normal;
		font-size:1.5em}
		#searchbox
		{background:#eee;
		padding:10px;
		margin-top:20px;
		border:solid 1px #ddd
		}
	-->
	</style>
</head>
	<body>
		<div id="all">
			<div id="back">
				<div id="header">
					<div class="logoheader">
						<h1 id="logo">
							<?php if ($logo) : ?>
								<img src="<?php echo $this->baseurl; ?>/<?php echo htmlspecialchars($logo); ?>"  alt="<?php echo htmlspecialchars($params->get('sitetitle')); ?>" />
							<?php else : ?>
								<?php echo htmlspecialchars($params->get('sitetitle')); ?>
							<?php endif; ?>
							<span class="header1">
								<?php echo htmlspecialchars($params->get('sitedescription')); ?>
							</span>
						</h1>
					</div><!-- end logoheader -->
					<ul class="skiplinks">
						<li>
							<a href="#wrapper2" class="u2">
								<?php echo JText::_('TPL_BEEZ3_SKIP_TO_ERROR_CONTENT'); ?>
							</a>
						</li>
						<li>
							<a href="#nav" class="u2">
								<?php echo JText::_('TPL_BEEZ3_ERROR_JUMP_TO_NAV'); ?>
							</a>
						</li>
					</ul>
					<div id="line">
					</div><!-- end line -->
				</div><!-- end header -->
				<div id="contentarea2" >
					<div class="left1" id="nav">
						<h2 class="unseen">
							<?php echo JText::_('TPL_BEEZ3_NAVIGATION'); ?>
						</h2>
						<?php $module = JModuleHelper::getModule('menu'); ?>
						<?php echo JModuleHelper::renderModule($module); ?>
					</div><!-- end navi -->
					<div id="wrapper2">
						<div id="errorboxbody">
							<h2>
								<?php echo JText::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?>
							</h2>
							<h3><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></h3>
							<p><?php echo JText::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></p>
							<ul>
								<li><?php echo JText::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
								<li><?php echo JText::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
							</ul>
							<?php if ($format === 'html' && JModuleHelper::getModule('mod_search')) : ?>
								<div id="searchbox">
									<h3 class="unseen">
										<?php echo JText::_('TPL_BEEZ3_SEARCH'); ?>
									</h3>
									<p>
										<?php echo JText::_('JERROR_LAYOUT_SEARCH'); ?>
									</p>
									<?php $module = JModuleHelper::getModule('mod_search'); ?>
									<?php echo JModuleHelper::renderModule($module); ?>
								</div><!-- end searchbox -->
							<?php endif; ?>
							<div><!-- start goto home page -->
								<p>
								<a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>
								</p>
							</div><!-- end goto home page -->
							<h3>
								<?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?>
							</h3>
							<h2>
								#<?php echo $this->error->getCode(); ?>&nbsp;<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
							</h2>
							<?php if ($this->debug) : ?>
								<br/><?php echo htmlspecialchars($this->error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->error->getLine(); ?>
							<?php endif; ?>
							<br />
						</div><!-- end errorboxbody -->
					</div><!-- end wrapper2 -->
				</div><!-- end contentarea2 -->
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
								<p>
									<?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
									<br/><?php echo htmlspecialchars($this->_error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->_error->getLine(); ?>
								</p>
								<?php echo $this->renderBacktrace(); ?>
								<?php $loop = $this->setError($this->_error->getPrevious()); ?>
							<?php endwhile; ?>
							<?php // Reset the main error object to the base error ?>
							<?php $this->setError($this->error); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div><!--end back -->
		</div><!--end all -->
		<div id="footer-outer">
			<div id="footer-sub">
				<div id="footer">
				<p>
					<?php echo JText::_('TPL_BEEZ3_POWERED_BY'); ?>
					<a href="https://www.joomla.org/">
						Joomla!&#174;
					</a>
				</p>
				</div><!-- end footer -->
			 </div><!-- end footer-sub -->
		</div><!-- end footer-outer-->
	</body>
</html>
