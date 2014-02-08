<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params = JFactory::getApplication()->getTemplate(true)->params;
$logo = $params->get('logo');
$showRightColumn = 0;
$showleft = 0;
$showbottom = 0;

// get params
$color			= $params->get('templatecolor');
$navposition	= $params->get('navposition');

//get language and direction
$doc = JFactory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage()); ?></title>

	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/position.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/layout.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/print.css" type="text/css" media="Print" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/<?php echo htmlspecialchars($color); ?>.css" type="text/css" />

	<?php $files = JHtml::_('stylesheet', 'templates/'.$this->template.'/css/general.css', null, false, true);
	if ($files):
		if (!is_array($files)):
			$files = array($files);
		endif;
		foreach ($files as $file) : ?>
			<link rel="stylesheet" href="<?php echo $file; ?>" type="text/css" />
		<?php endforeach;
	endif; ?>
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/<?php echo htmlspecialchars($color); ?>.css" type="text/css" />
	<?php if ($this->direction == 'rtl') : ?>
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template_rtl.css" type="text/css" />
		<?php if (file_exists(JPATH_SITE . '/templates/' . $this->template.'/css/' . $color . '_rtl.css')) : ?>
			<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/<?php echo $color ?>_rtl.css" type="text/css" />
		<?php endif; ?>
	<?php endif; ?>
	<?php
		$debug = JFactory::getConfig()->get('debug_lang');
		if (JDEBUG || $debug) : ?>
			<link rel="stylesheet" href="<?php echo $this->baseurl ?>/media/cms/css/debug.css" type="text/css" />
		<?php endif; ?>
	<!--[if lte IE 6]>
		<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ieonly.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie7only.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if lt IE 9]>
		<script src="<?php echo $this->baseurl ?>/media/jui/js/html5.js"></script>
	<![endif]-->

	<style type="text/css">
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
						<?php
						$params = JFactory::getApplication()->getTemplate(true)->params;
						$logo = $params->get('logo');
						?>
						<h1 id="logo">
							<?php if ($logo) : ?>
								<img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($logo); ?>"  alt="<?php echo htmlspecialchars($params->get('sitetitle'));?>" />
							<?php else :
								echo htmlspecialchars($params->get('sitetitle'));
							endif; ?>
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
						<?php $module = JModuleHelper::getModule('menu');
						echo JModuleHelper::renderModule($module);	?>
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
							<?php if (JModuleHelper::getModule('search')) : ?>
								<div id="searchbox">
									<h3 class="unseen">
										<?php echo JText::_('TPL_BEEZ3_SEARCH'); ?>
									</h3>
									<p>
										<?php echo JText::_('JERROR_LAYOUT_SEARCH'); ?>
									</p>
									<?php $module = JModuleHelper::getModule('search');
									echo JModuleHelper::renderModule($module); ?>
								</div><!-- end searchbox -->
							<?php endif; ?>
							<div><!-- start gotohomepage -->
								<p>
								<a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>
								</p>
							</div><!-- end gotohomepage -->
							<h3>
								<?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?>
							</h3>
							<h2>#<?php echo $this->error->getCode(); ?>&nbsp;<?php echo $this->error->getMessage();?>
							</h2>
							<br />
						</div><!-- end errorboxbody -->
					</div><!-- end wrapper2 -->
				</div><!-- end contentarea2 -->
				<?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</div><!--end back -->
		</div><!--end all -->
		<div id="footer-outer">
			<div id="footer-sub">
				<div id="footer">
				<p>
					<?php echo JText::_('TPL_BEEZ3_POWERED_BY');?>
					<a href="http://www.joomla.org/">
						Joomla!&#174;
					</a>
				</p>
				</div><!-- end footer -->
			 </div><!-- end footer-sub -->
		</div><!-- end footer-outer-->
	</body>
	</html>
