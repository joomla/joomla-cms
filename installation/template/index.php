<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
	<head>
		<jdoc:include type="head" />

		<link href="../media/system/css/system.css" rel="stylesheet" type="text/css" />
		<link href="template/css/template.css" rel="stylesheet" type="text/css" />
		<?php if ($this->direction == 'rtl') : ?>
			<link href="template/css/template_rtl.css" rel="stylesheet" type="text/css" />
		<?php endif; ?>
		
		<!--[if IE 7]>
			<link href="template/css/ie7.css" rel="stylesheet" type="text/css" />
		<![endif]-->

		<?php
			jimport('joomla.html.html');
			JHtml::_('behavior.framework', true);
		?>
		<script type="text/javascript">
			window.addEvent('domready', function(){ new Fx.Accordion($$('h3.moofx-toggler'), $$('div.moofx-slider'), {onActive: function(toggler, i) { toggler.addClass('moofx-toggler-down'); },onBackground: function(toggler, i) { toggler.removeClass('moofx-toggler-down'); },duration: 300,opacity: false, alwaysHide:true, show: 1}); });
		</script>
	</head>
	<body>
		<div id="header1">
			<div id="header2">
				<div id="header3">
					<span class="logo"><a href="http://www.joomla.org" target="_blank"><img src="template/images/logo.png" alt="Joomla!" /></a></span>
					<span>Joomla! <?php echo JVERSION; ?> <?php echo JText::_('INSTL_INSTALLATION') ?></span>
				</div>
			</div>
		</div>
		<jdoc:include type="message" />
		<div id="content-box">
			<div id="content-pad">
				<jdoc:include type="installation" />
			</div>
		</div>
		<div id="footer1">
			<div id="footer2">
				<div id="footer3"></div>
			</div>
		</div>
		<div id="copyright">
			<?php $joomla= '<a href="http://www.joomla.org">Joomla!&#174;</a>';
			echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla) ?>
		</div>
	</body>
</html>
