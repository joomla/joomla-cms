<?php
/**
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access');

$url = clone(JURI::getInstance());
$task = JRequest::getCmd('task');
$showRightColumn = JRequest::getCmd('layout') != 'form' && $task != 'edit';
?>
<?php echo '<?xml version="1.0" encoding="utf-8"?' .'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<jdoc:include type="head" />

	<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
	<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/position.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/layout.css" type="text/css" media="screen,projection" />
	<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/print.css" type="text/css" media="Print" />
	<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/general.css" type="text/css" />
	<!--[if lte IE 6]>
		<link href="templates/<?php echo $this->template ?>/css/ieonly.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="templates/<?php echo $this->template ?>/css/ie7only.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<script type="text/javascript" src="templates/<?php echo $this->template ?>/javascript/md_stylechanger.js"></script>
</head>
<body>
	<div id="all">
		<div id="header">
			<h1 id="logo"><img src="templates/<?php echo $this->template ?>/images/logo.gif" border="0" alt="Logo Beez, Three little Bees" width="300" height="97" />
			<span class="header1">Joomla! accessible Template Beta 1</span>
			</h1>
			<ul>
				<li><a href="<?php $url->setFragment('content'); echo htmlspecialchars($url->toString());?>" class="u2">skip to content</a></li>
				<li> <a href="<?php $url->setFragment('mainmenu'); echo htmlspecialchars($url->toString());?>" class="u2">Jump to main navigation and Login</a> </li>
				<li> <a href="<?php $url->setFragment('additional'); echo htmlspecialchars($url->toString());?>" class="u2">Jump to additional Informations</a> </li>
			</ul>

			<h2 class="unsichtbar">Search, View and Navigation </h2>

			<div id="fontsize">
			<script type="text/javascript"><!--
				var size =['<h3>Font-size:</h3><p class="fontsize">']
				var bigger  =['<a href="index.php" title="Increase size" onclick="changeFontSize(2);return false;" class="larger"> bigger</a><span class="unsichtbar">&nbsp;</span> ']  ;
				var smaller =['<a href="index.php" title="Decrease size" onclick="changeFontSize(-2);return false;" class="smaller"> smaller</a><span class="unsichtbar">&nbsp;</span> ']  ;
				var reset = ['<a href="index.php" title="Revert styles to default" onclick="revertStyles(); return false;" class="reset">  reset</a></p> '];
				document.write(size);
				document.write(bigger);
				document.write(smaller);
				document.write(reset);
			-->
			</script>
			</div>

			<jdoc:include type="modules" name="user3"  />
			<jdoc:include type="modules" name="user4" />

			<div id="breadcrumbs">
				<p> You are here: <jdoc:include type="module" name="breadcrumbs" /></p>
			</div>
			<div class="wrap">&nbsp;</div>
		</div> <!-- end header -->

<?php if ($this->countModules('user1 + user2 + right + top') && $showRightColumn) : ?>
	<div id="contentarea2">
<?php else : ?>
	<div id="contentarea">
<?php endif; ?>
		<a name="mainmenu"></a>
		<div id="left">
			<jdoc:include type="modules" name="left" style="xhtml" />
		</div> <!-- left -->

		<a name="content"></a>
<?php if ($this->countModules('user1 + user2 + right + top') && $showRightColumn) : ?>
	<div id="main2">
<?php else : ?>
	<div id="main">
<?php endif; ?>

<?php if ($this->getBuffer('message')) : ?>
	<div class="error">
		<h2> Message </h2>
		<jdoc:include type="message" />
	</div>
<?php endif; ?>

	<jdoc:include type="component" />

		</div> <!-- end main or main2 -->

	<?php if ($this->countModules('user1 + user2 + right + top') && $showRightColumn) : ?>
		<div id="right">
		<a name="additional"></a>
		<h2 class="unsichtbar">additional informations</h2>
		<?php if ($this->countModules('top')) : ?>
		<jdoc:include type="modules" name="top" style="xhtml" />
		<?php endif; ?>

		<?php if ($this->countModules('user1')) : ?>
		<jdoc:include type="modules" name="user1" style="xhtml" />
		<?php endif; ?>

		<?php if ($this->countModules('user2')) : ?>
		<jdoc:include type="modules" name="user2" style="xhtml" />
		<?php endif; ?>

		<?php if ($this->countModules('right')) : ?>
		<jdoc:include type="modules" name="right" style="xhtml"/>
		<?php endif; ?>
		</div> <!-- right -->
	<?php endif; ?>

<div class="wrap"></div>

		<div id="footer">
			<p class="syndicate">
				<jdoc:include type="modules" name="syndicate" />
			</p>
			<p>
				Powered by <a href="http://joomla.org">Joomla!</a>
			</p>

			<div class="wrap"></div>
		</div> <!-- footer -->
	</div><!-- contentarea -->

	</div> <!-- all -->

<jdoc:include type="modules" name="debug" />

</body>
</html>