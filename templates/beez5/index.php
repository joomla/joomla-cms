<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.beez5
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// check modules
$showRightColumn	= ($this->countModules('position-3') or $this->countModules('position-6') or $this->countModules('position-8'));
$showbottom			= ($this->countModules('position-9') or $this->countModules('position-10') or $this->countModules('position-11'));
$showleft			= ($this->countModules('position-4') or $this->countModules('position-7') or $this->countModules('position-5'));

if ($showRightColumn == 0 and $showleft == 0)
{
	$showno = 0;
}

JHtml::_('behavior.framework', true);

// get params
$color			= $this->params->get('templatecolor');
$logo			= $this->params->get('logo');
$navposition	= $this->params->get('navposition');
$app			= JFactory::getApplication();
$doc			= JFactory::getDocument();
$templateparams	= $app->getTemplate(true)->params;

$doc->addScript($this->baseurl.'/templates/'.$this->template.'/javascript/md_stylechanger.js', 'text/javascript', true);
?>
<?php if(!$templateparams->get('html5', 0)): ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php else: ?>
	<?php echo '<!DOCTYPE html>'; ?>
<?php endif; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
	<head>
		<jdoc:include type="head" />
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
		<!--[if IE 7]>
			<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/ie7only.css" rel="stylesheet" type="text/css" />
		<![endif]-->
<?php if($templateparams->get('html5', 0)) { ?>
		<!--[if lt IE 9]>
			<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/javascript/html5.js"></script>
		<![endif]-->
<?php } ?>
		<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/javascript/hide.js"></script>

		<script type="text/javascript">
			var big ='<?php echo (int) $this->params->get('wrapperLarge');?>%';
			var small='<?php echo (int) $this->params->get('wrapperSmall'); ?>%';
			var altopen='<?php echo JText::_('TPL_BEEZ5_ALTOPEN', true); ?>';
			var altclose='<?php echo JText::_('TPL_BEEZ5_ALTCLOSE', true); ?>';
			var bildauf='<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/plus.png';
			var bildzu='<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/minus.png';
			var rightopen='<?php echo JText::_('TPL_BEEZ5_TEXTRIGHTOPEN', true); ?>';
			var rightclose='<?php echo JText::_('TPL_BEEZ5_TEXTRIGHTCLOSE', true); ?>';
			var fontSizeTitle='<?php echo JText::_('TPL_BEEZ5_FONTSIZE', true); ?>';
            var bigger='<?php echo JText::_('TPL_BEEZ5_BIGGER', true); ?>';
            var reset='<?php echo JText::_('TPL_BEEZ5_RESET', true); ?>';
            var smaller='<?php echo JText::_('TPL_BEEZ5_SMALLER', true); ?>';
            var biggerTitle='<?php echo JText::_('TPL_BEEZ5_INCREASE_SIZE', true); ?>';
            var resetTitle='<?php echo JText::_('TPL_BEEZ5_REVERT_STYLES_TO_DEFAULT', true); ?>';
            var smallerTitle='<?php echo JText::_('TPL_BEEZ5_DECREASE_SIZE', true); ?>';
		</script>

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
					<h1 id="logo">

					<?php if ($logo != null ): ?>
					<img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars($templateparams->get('sitetitle'));?>" />
					<?php else: ?>
					<?php echo htmlspecialchars($templateparams->get('sitetitle'));?>
					<?php endif; ?>
					<span class="header1">
					<?php echo htmlspecialchars($templateparams->get('sitedescription'));?>
					</span></h1>
				</div><!-- end logoheader -->

					<ul class="skiplinks">
						<li><a href="#main" class="u2"><?php echo JText::_('TPL_BEEZ5_SKIP_TO_CONTENT'); ?></a></li>
						<li><a href="#nav" class="u2"><?php echo JText::_('TPL_BEEZ5_JUMP_TO_NAV'); ?></a></li>
						<?php if($showRightColumn ):?>
						<li><a href="#additional" class="u2"><?php echo JText::_('TPL_BEEZ5_JUMP_TO_INFO'); ?></a></li>
						<?php endif; ?>
					</ul>
					<h2 class="unseen"><?php echo JText::_('TPL_BEEZ5_NAV_VIEW_SEARCH'); ?></h2>
					<h3 class="unseen"><?php echo JText::_('TPL_BEEZ5_NAVIGATION'); ?></h3>
					<jdoc:include type="modules" name="position-1" />
					<div id="line">
					<div id="fontsize"></div>
					<h3 class="unseen"><?php echo JText::_('TPL_BEEZ5_SEARCH'); ?></h3>
					<jdoc:include type="modules" name="position-0" />
					</div> <!-- end line -->
		<div id="header-image">
			<jdoc:include type="modules" name="position-15" />
			<?php if ($this->countModules('position-15') == 0): ?>
				<img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/fruits.jpg"  alt="<?php echo JText::_('TPL_BEEZ5_LOGO'); ?>" />
			<?php endif; ?>
		</div>
		<?php if (!$templateparams->get('html5', 0)): ?>
			</div><!-- end header -->
		<?php else: ?>
			</header><!-- end header -->
		<?php endif; ?>
		<div id="<?php echo $showRightColumn ? 'contentarea2' : 'contentarea'; ?>">
					<div id="breadcrumbs">

							<jdoc:include type="modules" name="position-2" />

					</div>

					<?php if ($navposition == 'left' and $showleft) : ?>

						<?php if(!$this->params->get('html5', 0)): ?>
							<div class="left1 <?php if ($showRightColumn == null){ echo 'leftbigger';} ?>" id="nav">
						<?php else: ?>
							<nav class="left1 <?php if ($showRightColumn == null){ echo 'leftbigger';} ?>" id="nav">
						<?php endif; ?>

								<jdoc:include type="modules" name="position-7" style="beezDivision" headerLevel="3" />
								<jdoc:include type="modules" name="position-4" style="beezHide" headerLevel="3" state="0 " />
								<jdoc:include type="modules" name="position-5" style="beezTabs" headerLevel="2"  id="3" />

						<?php if(!$this->params->get('html5', 0)): ?>
							</div><!-- end navi -->
						<?php else: ?>
							</nav>
						<?php endif; ?>

					<?php endif; ?>

					<div id="<?php echo $showRightColumn ? 'wrapper' : 'wrapper2'; ?>" <?php if (isset($showno)){echo 'class="shownocolumns"';}?>>

						<div id="main">

						<?php if ($this->countModules('position-12')): ?>
							<div id="top"><jdoc:include type="modules" name="position-12"   />
							</div>
						<?php endif; ?>

							<jdoc:include type="message" />
							<jdoc:include type="component" />

						</div><!-- end main -->

					</div><!-- end wrapper -->

				<?php if ($showRightColumn) : ?>
					<h2 class="unseen">
						<?php echo JText::_('TPL_BEEZ5_ADDITIONAL_INFORMATION'); ?>
					</h2>
					<div id="close">
						<a href="#" onclick="auf('right')">
							<span id="bild">
								<?php echo JText::_('TPL_BEEZ5_TEXTRIGHTCLOSE'); ?></span></a>
					</div>

				<?php if (!$templateparams->get('html5', 0)): ?>
					<div id="right">
				<?php else: ?>
					<aside id="right">
				<?php endif; ?>

						<a id="additional"></a>
						<jdoc:include type="modules" name="position-6" style="beezDivision" headerLevel="3"/>
						<jdoc:include type="modules" name="position-8" style="beezDivision" headerLevel="3"  />
						<jdoc:include type="modules" name="position-3" style="beezDivision" headerLevel="3"  />

				<?php if(!$templateparams->get('html5', 0)): ?>
					</div><!-- end right -->
				<?php else: ?>
					</aside>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($navposition == 'center' and $showleft) : ?>

				<?php if (!$this->params->get('html5', 0)): ?>
					<div class="left <?php if ($showRightColumn == null){ echo 'leftbigger';} ?>" id="nav" >
				<?php else: ?>
					<nav class="left <?php if ($showRightColumn == null){ echo 'leftbigger';} ?>" id="nav">
				<?php endif; ?>

						<jdoc:include type="modules" name="position-7"  style="beezDivision" headerLevel="3" />
						<jdoc:include type="modules" name="position-4" style="beezHide" headerLevel="3" state="0 " />
						<jdoc:include type="modules" name="position-5" style="beezTabs" headerLevel="2"  id="3" />

				<?php if (!$templateparams->get('html5', 0)): ?>
					</div><!-- end navi -->
				<?php else: ?>
					</nav>
				<?php endif; ?>
			<?php endif; ?>

					<div class="wrap"></div>

				</div> <!-- end contentarea -->

			</div><!-- back -->

		</div><!-- all -->

		<div id="footer-outer">

		<?php if ($showbottom) : ?>
			<div id="footer-inner">

				<div id="bottom">
					<?php if ($this->countModules('position-9')): ?>
					<div class="box box1"> <jdoc:include type="modules" name="position-9" style="beezDivision" headerlevel="3" /></div>
					<?php endif; ?>
					   <?php if ($this->countModules('position-10')): ?>
					<div class="box box2"> <jdoc:include type="modules" name="position-10" style="beezDivision" headerlevel="3" /></div>
					<?php endif; ?>
					<?php if ($this->countModules('position-11')): ?>
					<div class="box box3"> <jdoc:include type="modules" name="position-11" style="beezDivision" headerlevel="3" /></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

			<div id="footer-sub">

			<?php if (!$templateparams->get('html5', 0)): ?>
				<div id="footer">
			<?php else: ?>
				<footer id="footer">
			<?php endif; ?>

					<jdoc:include type="modules" name="position-14" />
					<p>
						<?php echo JText::_('TPL_BEEZ5_POWERED_BY');?> <a href="http://www.joomla.org/">Joomla!&#174;</a>
					</p>

			<?php if (!$templateparams->get('html5', 0)): ?>
				</div><!-- end footer -->
			<?php else: ?>
				</footer>
			<?php endif; ?>

			</div>

		</div>
		<jdoc:include type="modules" name="debug" />
	</body>
</html>
