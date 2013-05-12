<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();

// Add Stylesheets
$doc->addStyleSheet('../media/jui/css/bootstrap.css');
$doc->addStyleSheet('../media/jui/css/bootstrap-extended.css');
$doc->addStyleSheet('../media/jui/css/bootstrap-responsive.css');
$doc->addStyleSheet('template/css/template.css');

$doc->addStyleSheet('../media/jui/css/chosen.css');

if ($this->direction === 'rtl')
{
	$doc->addStyleSheet('../media/jui/css/bootstrap-rtl.css');
}

// Load the JavaScript behaviors
JHtml::_('behavior.framework', true);
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

JHtml::_('script', 'installation/template/js/lib/RequireJS/require.min.js', true, false, false, false);

// JHtml::_('script', 'installation/template/js/installation.min.js', true, false, false, false);
JHtml::_('script', 'installation/template/js/app.js', true, false, false, false);

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_SITE_SAMPLE_LOADED');
JText::script('INSTL_FTP_SETTINGS_CORRECT');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="head" />

		<!--[if IE 7]>
			<link href="template/css/ie7.css" rel="stylesheet" type="text/css" />
		<![endif]-->

		<script>
		var base = '<?php echo JURI::current(); ?>';
		</script>
	</head>
	<body>
		<!-- Header -->
		<div class="header">
			<img src="<?php echo $this->baseurl ?>/template/images/joomla.png" alt="Joomla" />
			<hr />
			<h5>
				<?php
				$joomla = '<a href="http://www.joomla.org">Joomla!<sup>&#174;</sup></a>';
				$license = '<a data-toggle="modal" href="#licenseModal">' . JText::_('INSTL_GNU_GPL_LICENSE') . '</a>';
				echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla, $license);
				?>
			</h5>
		</div>
		<!-- Container -->
		<div class="container">
			<jdoc:include type="message" />
			<div id="container-installation">
				<jdoc:include type="installation" />
			</div>
			<hr />
		</div>
		<div id="licenseModal" class="modal fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3><?php echo JText::_('INSTL_GNU_GPL_LICENSE'); ?></h3>
			</div>
			<div class="modal-body">
				<iframe src="gpl.html" class="thumbnail span6 license" height="250" marginwidth="25" scrolling="auto"></iframe>
			</div>
		</div>
	</body>
</html>
