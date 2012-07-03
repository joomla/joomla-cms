<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Add Stylesheets
$doc->addStyleSheet('../templates/system/css/bootstrap.css');
$doc->addStyleSheet('../templates/system/css/bootstrap-extended.css');
$doc->addStyleSheet('../templates/system/css/bootstrap-responsive.css');
$doc->addStyleSheet('template/css/template.css');

$doc->addStyleSheet('../templates/system/css/chosen.css');

if ($this->direction == 'rtl') {
	$doc->addStyleSheet('template/css/template_rtl.css');
}

// Load the JavaScript behaviors
JHtml::_('behavior.framework', true);
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'installation/template/js/installation.js', true, false, false, false);

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_SITE_SAMPLE_LOADED');
JText::script('INSTL_FTP_SETTINGS_CORRECT');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
	<head>
		<script src="<?php echo JURI::root();?>templates/system/js/jquery.js"></script>
		<script src="<?php echo JURI::root();?>templates/system/js/bootstrap.min.js"></script>
		<script src="<?php echo JURI::root();?>templates/system/js/chosen.jquery.min.js"></script>
		<script type="text/javascript">
		  jQuery.noConflict();
		</script>
		<jdoc:include type="head" />

		<!--[if IE 7]>
			<link href="template/css/ie7.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		<script type="text/javascript">
			window.addEvent('domready', function() {
				window.Install = new Installation('container-installation', '<?php echo JURI::current(); ?>');
			});
 		</script>
	</head>
	<body>
		<!-- Header -->
		<div class="header">
			<img src="<?php echo $this->baseurl ?>/template/images/joomla.png" alt="Joomla" />
		</div>
		<!-- Container -->
		<div class="container">
			<?php echo JHtml::_('installation.stepbar'); ?>
			<div id="container-installation">
				<jdoc:include type="installation" />
			</div>
			<hr />
			<div class="footer">
				<p class="pull-right"><a href="#top" id="back-top">Back to top</a></p>
				<p><?php $joomla= '<a href="http://www.joomla.org">Joomla!&#174;</a>';
				echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla) ?></p>
			</div>
		</div>
		<script>
			(function($){
				$('*[rel=tooltip]').tooltip()
				$('*[rel=popover]').popover()

				// Chosen select boxes
				$("select").chosen({disable_search_threshold : 10 });

				// Turn radios into btn-group
				$('.radio.btn-group label').addClass('btn')
				$(".btn-group label:not(.active)").click(function(){
				    var label = $(this);
				    var input = $('#' + label.attr('for'));

				    if (!input.prop('checked')){
				        label.closest('.btn-group').find("label").removeClass('active btn-primary');
				        label.addClass('active btn-primary');
				        input.prop('checked', true);
				    }
				});
				$(".btn-group input[checked=checked]").each(function(){
				    $("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
				});
		    })(jQuery);
		</script>
	</body>
</html>
