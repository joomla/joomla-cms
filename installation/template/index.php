<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doc = JFactory::getDocument();

// Add Stylesheets
JHtml::_('bootstrap.loadCss', true, $this->direction);
JHtml::_('stylesheet', 'installation/template/css/template.css');

// Load the JavaScript behaviors
JHtml::_('bootstrap.framework');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('script', 'installation/template/js/installation.js');

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_FTP_SETTINGS_CORRECT');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="head" />
		<!--[if lt IE 9]>
			<script src="../media/jui/js/html5.js"></script>
		<![endif]-->
		<script type="text/javascript">
			jQuery(function()
			{	// Delay instantiation after document.formvalidation and other dependencies loaded
				window.setTimeout(function(){
					window.Install = new Installation('container-installation', '<?php echo JUri::current(); ?>');
			   	}, 500);

			});
		</script>
	</head>
	<body data-basepath="<?php echo JURI::root(true); ?>">
		<!-- Header -->
		<div class="header">
			<img src="<?php echo $this->baseurl ?>/template/images/joomla.png" alt="Joomla" />
			<hr />
			<h5>
				<?php
				// Fix wrong display of Joomla!® in RTL language
				if (JFactory::getLanguage()->isRtl())
				{
					$joomla = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>&#174;&#x200E;</sup>';
				}
				else
				{
					$joomla = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>&#174;</sup>';
				}
				$license = '<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank">' . JText::_('INSTL_GNU_GPL_LICENSE') . '</a>';
				echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla, $license);
				?>
			</h5>
		</div>
		<!-- Container -->
		<div class="container">
			<jdoc:include type="message" />
			<div id="javascript-warning">
				<noscript>
					<div class="alert alert-error">
						<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
			</div>
			<div id="container-installation">
				<jdoc:include type="component" />
			</div>
			<hr />
		</div>
		<script>
			function initElements()
			{
				(function($){
					$('.hasTooltip').tooltip()

					// Chosen select boxes
					$("select").chosen({
						disable_search_threshold : 10,
						allow_single_deselect : true
					});

					// Turn radios into btn-group
				    $('.radio.btn-group label').addClass('btn');
				    $(".btn-group label:not(.active)").click(function()
					{
				        var label = $(this);
				        var input = $('#' + label.attr('for'));

				        if (!input.prop('checked'))
						{
				            label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				            if(input.val()== '')
							{
				                    label.addClass('active btn-primary');
				             } else if(input.val()==0 || input.val()=='remove')
							{
				                    label.addClass('active btn-danger');
				             } else {
				            label.addClass('active btn-success');
				             }
				            input.prop('checked', true);
				        }
				    });
				    $(".btn-group input[checked='checked']").each(function()
					{
						if ($(this).val()== '')
						{
				           $("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
				        } else if($(this).val()==0 || $(this).val()=='remove')
						{
				           $("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
				        } else {
				            $("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
				        }
				    });
				})(jQuery);
			}
			initElements();
		</script>
	</body>
</html>
