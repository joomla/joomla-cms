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
JHtml::_('script', 'installation/template/js/installation.js', true, false, false, false);

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_SITE_SAMPLE_LOADED');
JText::script('INSTL_FTP_SETTINGS_CORRECT');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<script src="<?php echo JURI::root();?>media/jui/js/jquery.js"></script>
		<script src="<?php echo JURI::root();?>media/jui/js/bootstrap.min.js"></script>
		<script src="<?php echo JURI::root();?>media/jui/js/chosen.jquery.min.js"></script>
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
		<script>
			function initElements() {
				(function($){
					$('*[rel=tooltip]').tooltip()
					$('*[rel=popover]').popover()

					// Chosen select boxes
					$("select").chosen({
						disable_search_threshold : 10,
						allow_single_deselect : true
					});

					// Turn radios into btn-group
					$('.radio.btn-group label').addClass('btn')
					$(".btn-group label:not(.active)").click(function() {
						var label = $(this);
						var input = $('#' + label.attr('for'));

						if (!input.prop('checked')){
							label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger');
							if (input.val() === 0) {
								label.addClass('active btn-danger');
							} else {
								label.addClass('active btn-success');
							}
							input.prop('checked', true);
						}
					});
					$(".btn-group input[checked=checked]").each(function() {
						var label = $("label[for=" + $(this).attr('id') + "]");
						if ($(this).val() === 0) {
							label.addClass('active btn-danger');
						} else {
							label.addClass('active btn-success');
						}
					});
				})(jQuery);
			}
			initElements();
		</script>
	</body>
</html>
