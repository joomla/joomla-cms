<?php
/**
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

include_once (dirname(__FILE__).DS.'/ja_vars.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>">

<head>
<jdoc:include type="head" />
<?php JHtml::_('behavior.framework', true); ?>

<link rel="stylesheet" href="<?php echo $tmpTools->baseurl(); ?>templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $tmpTools->baseurl(); ?>templates/system/css/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/css/template.css" type="text/css" />

<script language="javascript" type="text/javascript" src="<?php echo $tmpTools->templateurl(); ?>/js/ja.script.js"></script>

<?php if ($tmpTools->getParam('rightCollapsible')): ?>
<script language="javascript" type="text/javascript">
var rightCollapseDefault='<?php echo $tmpTools->getParam('rightCollapseDefault'); ?>';
var excludeModules='<?php echo $tmpTools->getParam('excludeModules'); ?>';
</script>
<script language="javascript" type="text/javascript" src="<?php echo $tmpTools->templateurl(); ?>/js/ja.rightcol.js"></script>
<?php endif; ?>

<?php  if ($this->direction == 'rtl') : ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/css/template_rtl.css" type="text/css" />
<?php else : ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/css/menu.css" type="text/css" />
<?php endif; ?>

<?php if ($this->countModules('hornav')): ?>
<?php if ($tmpTools->getParam('horNavType') == 'css'): ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/css/ja-sosdmenu.css" type="text/css" />
<script language="javascript" type="text/javascript" src="<?php echo $tmpTools->templateurl(); ?>/js/ja.cssmenu.js"></script>
<?php else: ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/css/ja-sosdmenu.css" type="text/css" />
<script language="javascript" type="text/javascript" src="<?php echo $tmpTools->templateurl(); ?>/js/ja.moomenu.js"></script>
<?php endif; ?>
<?php endif; ?>

<?php if ($tmpTools->getParam('theme_header') && $tmpTools->getParam('theme_header')!='-1') : ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/styles/header/<?php echo $tmpTools->getParam('theme_header'); ?>/style.css" type="text/css" />
<?php endif; ?>
<?php if ($tmpTools->getParam('theme_background') && $tmpTools->getParam('theme_background')!='-1') : ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/styles/background/<?php echo $tmpTools->getParam('theme_background'); ?>/style.css" type="text/css" />
<?php endif; ?>
<?php if ($tmpTools->getParam('theme_elements') && $tmpTools->getParam('theme_elements')!='-1') : ?>
<link rel="stylesheet" href="<?php echo $tmpTools->templateurl(); ?>/styles/elements/<?php echo $tmpTools->getParam('theme_elements'); ?>/style.css" type="text/css" />
<?php endif; ?>

<!--[if gte IE 7.0]>
<style type="text/css">
.clearfix {display: inline-block;}
</style>
<![endif]-->
<?php if ($tmpTools->isIE6()): ?>
<!--[if lte IE 6]>
<script type="text/javascript">
var siteurl = '<?php echo $tmpTools->baseurl();?>';

window.addEvent ('load', makeTransBG);
function makeTransBG() {
	fixIEPNG($E('.ja-headermask'), '', '', 1);
	fixIEPNG($E('h1.logo a'));
	fixIEPNG($$('img'));
	fixIEPNG ($$('#ja-mainnav ul.menu li ul'), '', 'scale', 0, 2);
}
</script>
<style type="text/css">
.ja-headermask, h1.logo a, #ja-cssmenu li ul { background-position: -1000px; }
#ja-cssmenu li ul li, #ja-cssmenu li a { background:transparent url(<?php echo $tmpTools->templateurl(); ?>/images/blank.png) no-repeat right;}
.clearfix {height: 1%;}
</style>
<![endif]-->
<?php endif; ?>

<style type="text/css">
#ja-header,#ja-mainnav,#ja-container,#ja-botsl,#ja-footer {width: <?php echo $tmpWidth; ?>;margin: 0 auto;}
#ja-wrapper {min-width: <?php echo $tmpWrapMin; ?>;}
</style>
</head>

<body id="bd" class="fs<?php echo $tmpTools->getParam(JA_TOOL_FONT);?> <?php echo $tmpTools->browser();?>" >
<a name="Top" id="Top"></a>
<ul class="accessibility">
	<li><a href="#ja-content" title="<?php echo JText::_("Skip to content");?>"><?php echo JText::_("Skip to content");?></a></li>
	<li><a href="#ja-mainnav" title="<?php echo JText::_("Skip to main navigation");?>"><?php echo JText::_("Skip to main navigation");?></a></li>
	<li><a href="#ja-col1" title="<?php echo JText::_("Skip to 1st column");?>"><?php echo JText::_("Skip to 1st column");?></a></li>
	<li><a href="#ja-col2" title="<?php echo JText::_("Skip to 2nd column");?>"><?php echo JText::_("Skip to 2nd column");?></a></li>
</ul>

<div id="ja-wrapper">

<!-- BEGIN: HEADER -->
<div id="ja-headerwrap">
	<div id="ja-header" class="clearfix" style="background: url(<?php echo $tmpTools->templateurl(); ?>/images/header/<?php echo $tmpTools->getRandomImage(dirname(__FILE__).DS.'images/header'); ?>) no-repeat top <?php if ($this->direction == 'rtl') echo 'left'; else echo 'right';?>;">

	<div class="ja-headermask">&nbsp;</div>

	<?php
		$siteName = $tmpTools->sitename();
		if ($tmpTools->getParam('logoType')=='image'): ?>
		<h1 class="logo">
			<a href="index.php" title="<?php echo $siteName; ?>"><span><?php echo $siteName; ?></span></a>
		</h1>
	<?php else:
		$logoText = (trim($tmpTools->getParam('logoText'))=='') ? $config->sitename : $tmpTools->getParam('logoText');
		$sloganText = (trim($tmpTools->getParam('sloganText'))=='') ? JText::_('SITE SLOGAN') : $tmpTools->getParam('sloganText');	?>
		<h1 class="logo-text">
			<a href="index.php" title="<?php echo $siteName; ?>"><span><?php echo $logoText; ?></span></a>
		</h1>
		<p class="site-slogan"><?php echo $sloganText;?></p>
	<?php endif; ?>

	<?php $tmpTools->genToolMenu(JA_TOOL_FONT, 'png'); ?>

	<?php if ($this->countModules('user4')) : ?>
		<div id="ja-search">
			<jdoc:include type="modules" name="user4" />
		</div>
	<?php endif; ?>

	</div>
</div>
<!-- END: HEADER -->

<!-- BEGIN: MAIN NAVIGATION -->
<?php if ($this->countModules('hornav')): ?>
<div id="ja-mainnavwrap">
	<div id="ja-mainnav" class="clearfix">
	<jdoc:include type="modules" name="hornav" />
	</div>
</div>
<?php endif; ?>
<!-- END: MAIN NAVIGATION -->

<div id="ja-containerwrap<?php echo $divid; ?>">
<div id="ja-containerwrap2">
	<div id="ja-container">
	<div id="ja-container2" class="clearfix">

		<div id="ja-mainbody<?php echo $divid; ?>" class="clearfix">

		<!-- BEGIN: CONTENT -->
		<div id="ja-contentwrap">
		<div id="ja-content">

			<jdoc:include type="message" />

			<?php if (!$tmpTools->isFrontPage()) : ?>
			<div id="ja-pathway">
				<jdoc:include type="module" name="breadcrumbs" />
			</div>
			<?php endif ; ?>

			<jdoc:include type="component" />

			<?php if ($this->countModules('banner')) : ?>
			<div id="ja-banner">
				<jdoc:include type="modules" name="banner" />
			</div>
			<?php endif; ?>

		</div>
		</div>
		<!-- END: CONTENT -->

		<?php if ($this->countModules('left')): ?>
		<!-- BEGIN: LEFT COLUMN -->
		<div id="ja-col1">
			<jdoc:include type="modules" name="left" style="xhtml" />
		</div><br />
		<!-- END: LEFT COLUMN -->
		<?php endif; ?>

		</div>

		<?php if ($this->countModules('right')): ?>
		<!-- BEGIN: RIGHT COLUMN -->
		<div id="ja-col2">
			<jdoc:include type="modules" name="right" style="jarounded" />
		</div><br />
		<!-- END: RIGHT COLUMN -->
		<?php endif; ?>

	</div>
	</div>
</div>
</div>

<?php
$spotlight = array ('user1','user2','top','user5');
$botsl = $tmpTools->calSpotlight ($spotlight,99,22);
if ($botsl) :
?>
<!-- BEGIN: BOTTOM SPOTLIGHT -->
<div id="ja-botslwrap">
	<div id="ja-botsl" class="clearfix">

	  <?php if ($this->countModules('user1')): ?>
	  <div class="ja-box<?php echo $botsl['user1']['class']; ?>" style="width: <?php echo $botsl['user1']['width']; ?>;">
			<jdoc:include type="modules" name="user1" style="xhtml" />
	  </div>
	  <?php endif; ?>

	  <?php if ($this->countModules('user2')): ?>
	  <div class="ja-box<?php echo $botsl['user2']['class']; ?>" style="width: <?php echo $botsl['user2']['width']; ?>;">
			<jdoc:include type="modules" name="user2" style="xhtml" />
	  </div>
	  <?php endif; ?>

	  <?php if ($this->countModules('top')): ?>
	  <div class="ja-box<?php echo $botsl['top']['class']; ?>" style="width: <?php echo $botsl['top']['width']; ?>;">
			<jdoc:include type="modules" name="top" style="xhtml" />
	  </div>
	  <?php endif; ?>

	  <?php if ($this->countModules('user5')): ?>
	  <div class="ja-box<?php echo $botsl['user5']['class']; ?>" style="width: <?php echo $botsl['user5']['width']; ?>;">
			<jdoc:include type="modules" name="user5" style="xhtml" />
	  </div>
	  <?php endif; ?>

	</div>
</div>
<!-- END: BOTTOM SPOTLIGHT -->
<?php endif; ?>

<!-- BEGIN: FOOTER -->
<div id="ja-footerwrap">
<div id="ja-footer" class="clearfix">

	<div id="ja-footnav">
		<jdoc:include type="modules" name="user3" />
	</div>

	<div class="copyright">
		<jdoc:include type="modules" name="footer" />
	</div>

	<div class="ja-cert">
		<jdoc:include type="modules" name="syndicate" />
    <a href="http://jigsaw.w3.org/css-validator/check/referer" target="_blank" title="<?php echo JText::_("CSS Validity");?>" style="text-decoration: none;">
		<img src="<?php echo $tmpTools->templateurl(); ?>/images/but-css.gif" border="none" alt="<?php echo JText::_("CSS Validity");?>" />
		</a>
		<a href="http://validator.w3.org/check/referer" target="_blank" title="<?php echo JText::_("XHTML Validity");?>" style="text-decoration: none;">
		<img src="<?php echo $tmpTools->templateurl(); ?>/images/but-xhtml10.gif" border="none" alt="<?php echo JText::_("XHTML Validity");?>" />
		</a>
	</div>

	<br />
</div>
</div>
<!-- END: FOOTER -->

</div>

<jdoc:include type="modules" name="debug" />

</body>

</html>
