<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
JHtml::_('behavior.noframes');
$lang = JFactory::getLanguage();
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<!-- Load system style CSS -->
<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />

<!-- Load Template CSS -->
<link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />

<!-- Load additional CSS styles for colors -->
<?php
	if (!$this->params->get('colourChoice')) :
		$colour = 'standard';
	else :
		$colour = htmlspecialchars($this->params->get('colourChoice'));
	endif;
?>
<link href="templates/<?php echo $this->template ?>/css/colour_<?php echo $colour; ?>.css" rel="stylesheet" type="text/css" />

<!-- Load additional CSS styles for rtl sites -->
<?php if ($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo  $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
	<link href="templates/<?php echo $this->template ?>/css/colour_<?php echo $colour; ?>_rtl.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

<!-- Load specific language related css -->
<?php if (JFile::exists($file)) : ?>
	<link href="<?php echo $file ?>" rel="stylesheet" type="text/css" />
<?php  endif; ?>

<!-- Load additional CSS styles for bold Text -->
<?php if ($this->params->get('boldText')) : ?>
	<link href="templates/<?php echo $this->template ?>/css/boldtext.css" rel="stylesheet" type="text/css" />
<?php  endif; ?>

<!-- Load additional CSS styles for Internet Explorer -->
<!--[if IE 7]>
	<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lte IE 6]>
	<link href="templates/<?php echo  $this->template ?>/css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!-- Load Template JavaScript -->
<script type="text/javascript" src="templates/<?php  echo  $this->template  ?>/js/template.js"></script>

</head>
<body id="login-page">
	<div id="containerwrap">

		<!-- Header Logo -->
		<div id="header">
			<h1 class="title"><?php echo $this->params->get('showSiteName') ? $app->getCfg('sitename') . " " . JText::_('JADMINISTRATION') : JText::_('JADMINISTRATION'); ?></h1>
		</div><!-- end header -->

		<!-- Content Area -->
		<div id="content">

			<!-- Beginning of Actual Content -->
			<div id="element-box" class="login">
				<div class="pagetitle"><h2><?php echo JText::_('COM_LOGIN_JOOMLA_ADMINISTRATION_LOGIN') ?></h2></div>

					<!-- System Messages -->
					<jdoc:include type="message" />

					<div class="login-inst">
					<p><?php echo JText::_('COM_LOGIN_VALID') ?></p>
					<div id="lock"></div>
					<a href="<?php echo JURI::root(); ?>"><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE') ?></a>
					</div>

					<!-- Login Component -->
					<div class="login-box">
						<jdoc:include type="component" />
					</div>
				<div class="clr"></div>
			</div><!-- end element-box -->

		<noscript>
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
		</noscript>

		</div><!-- end content -->
		<div class="clr"></div>
	</div><!-- end of containerwrap -->

	<!-- Footer -->
	<div id="footer">
		<p class="copyright">
			<?php $joomla= '<a href="http://www.joomla.org">Joomla!&#174;</a>';
			echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla) ?>
		</p>
	</div>
</body>
</html>
