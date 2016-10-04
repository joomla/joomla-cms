<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

// Add Stylesheets
JHtml::_('bootstrap.loadCss', true, $this->direction);
JHtml::_('stylesheet', 'installation/template/css/template.css');
JHtml::_('stylesheet', 'media/vendor/font-awesome/css/font-awesome.min.css');

// Load the JavaScript behaviors
JHtml::_('bootstrap.framework');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('script', 'installation/template/js/installation.js');

// Load JavaScript message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');

// Add strings for JavaScript error translations.
JText::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
JText::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
JText::script('JLIB_JS_AJAX_ERROR_OTHER');
JText::script('JLIB_JS_AJAX_ERROR_PARSE');
JText::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_FTP_SETTINGS_CORRECT');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="head" />
		<script>
			jQuery(function()
			{
				// Delay instantiation after document.formvalidation and other dependencies loaded
				window.setTimeout(function(){
					window.Install = new Installation('container-installation', '<?php echo JUri::current(); ?>');
				}, 500);
			});
		</script>
	</head>
	<body data-basepath="<?php echo JUri::root(true); ?>">
		<?php // Header ?>
		<div class="header">
			<img src="<?php echo $this->baseurl ?>/template/images/joomla.png" alt="Joomla" />
			<hr>
			<h5>
				<?php // Fix wrong display of Joomla!® in RTL language ?>
				<?php if (JFactory::getLanguage()->isRtl()) : ?>
					<?php $joomla = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>&#174;&#x200E;</sup>'; ?>
				<?php else : ?>
					<?php $joomla = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>&#174;</sup>'; ?>
				<?php endif; ?>
				<?php $license = '<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank">' . JText::_('INSTL_GNU_GPL_LICENSE') . '</a>'; ?>
				<?php echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla, $license); ?>
			</h5>
		</div>
		<?php // Container ?>
		<div class="container">
			<jdoc:include type="message" />
			<div id="javascript-warning">
				<noscript>
					<div class="alert alert-danger">
						<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
			</div>
			<div id="container-installation">
				<jdoc:include type="component" />
			</div>
			<hr>
		</div>
		<script>
			function initElements()
			{
				(function($){
					$('.hasTooltip').tooltip({html:true});

					// Chosen select boxes
					$('select').chosen({
						disable_search_threshold : 10,
						allow_single_deselect : true
					});

					// Turn radios into btn-group
					$('.radio.btn-group label').addClass('btn btn-secondary');

					$('fieldset.btn-group').each(function()
					{
						var $self = $(this);
						// Handle disabled, prevent clicks on the container, and add disabled style to each button
						if ($self.prop('disabled'))
						{
							$self.css('pointer-events', 'none').off('click');
							$self.find('.btn').addClass('disabled');
						}
					});

					$(".btn-group label:not(.active)").click(function()
					{
						var label = $(this),
						    input = label.find('input');

						if (!input.prop('checked'))
						{
							label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');

							if (label.closest('.btn-group').hasClass('btn-group-reverse'))
							{
								if (input.val() == '')
								{
									label.addClass('active btn-primary');
								}
								else if (input.val() == 0)
								{
									label.addClass('active btn-danger');
								}
								else
								{
									label.addClass('active btn-success');
								}
							}
							else
							{
								if (input.val() == '')
								{
									label.addClass('active btn-primary');
								}
								else if (input.val() == 0)
								{
									label.addClass('active btn-success');
								}
								else
								{
									label.addClass('active btn-danger');
								}
							}
							input.prop('checked', true);
						}
					});
					$(".btn-group input[checked='checked']").each(function()
					{
						var $self  = $(this),
						    parent = $self.parents('.btn-group'),
						    attrId = $self.attr('id');

						if (parent.hasClass('btn-group-reverse'))
						{
							if ($self.val() == '')
							{
								$('label[for=' + attrId + ']').addClass('active btn-primary');
							}
							else if ($self.val() == 0)
							{
								$('label[for=' + attrId + ']').addClass('active btn-danger');
							}
							else
							{
								$('label[for=' + attrId + ']').addClass('active btn-success');
							}
						}
						else
						{
							if ($self.val() == '')
							{
								$('label[for=' + attrId + ']').addClass('active btn-primary');
							}
							else if ($self.val() == 0)
							{
								$('label[for=' + attrId + ']').addClass('active btn-success');
							}
							else
							{
								$('label[for=' + attrId + ']').addClass('active btn-danger');
							}
						}
					});
				})(jQuery);
			}
			initElements();
		</script>

	</body>
</html>
