<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php
//get template params
$templateparams	=  JFactory::getApplication()->getTemplate(true)->params;

//get language and direction
$doc = JFactory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;

if(!$templateparams->get('html5', 0)): ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php else: ?>
	<?php echo '<!DOCTYPE html>'; ?>
<?php endif; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
<?php if ($this->error->getCode()>=400 && $this->error->getCode() < 500) { 	?>


		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/position.css" type="text/css" media="screen,projection" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/layout.css" type="text/css" media="screen,projection" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/print.css" type="text/css" media="Print" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/beez5.css" type="text/css" />
<?php
	$files = JHtml::_('stylesheet', 'templates/'.$this->template.'/css/general.css', null, false, true);
	if ($files):
		if (!is_array($files)):
			$files = array($files);
		endif;
		foreach($files as $file):
?>
		<link rel="stylesheet" href="<?php echo $file;?>" type="text/css" />
<?php
	 	endforeach;
	endif;
?>
		<?php if ($this->direction == 'rtl') : ?>
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template_rtl.css" type="text/css" />
		<?php endif; ?>
		<!--[if lte IE 6]>
			<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ieonly.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		<!--[if IE 7]>
			<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie7only.css" rel="stylesheet" type="text/css" />
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
			<?php if(!$templateparams->get('html5', 0)): ?>
		<div id="header">
			<?php else: ?>
		<header id="header">
			<?php endif; ?>
					<div class="logoheader">
						<?php
								$logo =  $templateparams->get('logo');
							?>

							<?php jimport( 'joomla.application.module.helper' ); ?>

						<h1 id="logo">
						<?php if ($logo != null ): ?>
						<img src="<?php echo $this->baseurl  .'/'. $logo; ?>" alt="<?php echo htmlspecialchars($templateparams->get('sitetitle'));?>" />
						<?php else: ?>
						<?php echo htmlspecialchars($templateparams->get('sitetitle'));?>
						<?php endif; ?>
						<span class="header1">
						<?php echo htmlspecialchars($templateparams->get('sitedescription'));?>
						</span></h1>
					</div><!-- end logoheader -->

						<ul class="skiplinks">
							<li><a href="#wrapper2" class="u2"><?php echo JText::_('TPL_BEEZ5_SKIP_TO_ERROR_CONTENT'); ?></a></li>
							<li><a href="#nav" class="u2"><?php echo JText::_('TPL_BEEZ5_ERROR_JUMP_TO_NAV'); ?></a></li>

						</ul>
						<div id="line"></div>
					<div id="header-image">
					<img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/fruits.jpg"  alt="<?php echo JText::_('TPL_BEEZ5_LOGO'); ?>" />
					</div>

		<?php if(!$templateparams->get('html5', 0)): ?>
				</div><!-- end header -->
			<?php else: ?>
				</header><!-- end header -->
			<?php endif; ?>
		<div id="contentarea2" >

						<?php if(!$templateparams->get('html5', 0)): ?>
							<div class="left1" id="nav">
						<?php else: ?>
							<nav class="left1" id="nav">
						<?php endif; ?>
				<h2 class="unseen"><?php echo JText::_('TPL_BEEZ5_NAVIGATION'); ?></h2>
											<?php $module = JModuleHelper::getModule( 'menu' );
											echo JModuleHelper::renderModule( $module);	?>
					<?php if(!$templateparams->get('html5', 0)): ?>
							</div>
						<?php else: ?>
							</nav>
						<?php endif; ?><!-- end navi -->
			<div id="wrapper2">
			<div id="errorboxbody">
						<h2><?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?><br />
								<?php echo JText::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?></h2>
								<?php if (JModuleHelper::getModule( 'search' )) : ?>
									<div id="searchbox">
									<h3 class="unseen"><?php echo JText::_('TPL_BEEZ5_SEARCH'); ?></h3>
									<p><?php echo JText::_('JERROR_LAYOUT_SEARCH'); ?></p>
									<?php $module = JModuleHelper::getModule( 'search' );
									echo JModuleHelper::renderModule( $module);	?>
									</div>
								<?php endif; ?>
								<div>
								<p><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>
								</div>

					<h3><?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></h3>

					<h2>#<?php echo $this->error->getCode() ; ?>&nbsp;<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></h2><br />

				</div><!-- end wrapper -->
			</div><!-- end contentarea -->

						<?php if ($this->debug) :
							echo $this->renderBacktrace();
						endif; ?>


			</div>  <!--end all -->
			</div>
			</div>

		<div id="footer-sub">

			<?php if (!$templateparams->get('html5', 0)): ?>
				<div id="footer">
			<?php else: ?>
				<footer id="footer">
			<?php endif; ?>

					<p>
						<?php echo JText::_('TPL_BEEZ5_POWERED_BY');?> <a href="http://www.joomla.org/">Joomla!&#174;</a>
					</p>

			<?php if (!$templateparams->get('html5', 0)): ?>
				</div><!-- end footer -->
			<?php else: ?>
				</footer>
			<?php endif; ?>

			</div>
</body>
</html>
<?php } else { ?>
<?php
if (!isset($this->error)) {
	$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false;
}
?>
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" type="text/css" />
</head>
<body>
	<div class="error">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxheader"><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></div>
			<div id="errorboxbody">
			<p><strong><?php echo JText::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></strong></p>
				<ol>
					<li><?php echo JText::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_MIS_TYPED_ADDRESS'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND'); ?></li>
					<li><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></li>
				</ol>
			<p><strong><?php echo JText::_('JERROR_LAYOUT_PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?></strong></p>

				<ul>
					<li><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></li>
					<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_search" title="<?php echo JText::_('JERROR_LAYOUT_SEARCH_PAGE'); ?>"><?php echo JText::_('JERROR_LAYOUT_SEARCH_PAGE'); ?></a></li>

				</ul>
			<div id="techinfo">
			<p><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>

			<p><?php echo JText::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>

			<p>
				<?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</p>
			</div>
			</div>
		</div>
		</div>
	</div>
</body>
</html>


<?php } ?>
