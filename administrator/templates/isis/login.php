<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// Add Stylesheets
$doc->addStyleSheet('templates/' .$this->template. '/css/template.css');

// If Right-to-Left
if ($this->direction == 'rtl') :
	$doc->addStyleSheet('../media/jui/css/bootstrap-rtl.css');
endif;
// Chosen Selects
$doc->addStyleSheet('../media/jui/css/chosen.css');

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->getCfg('sitename');
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<script src="<?php echo JUri::root(); ?>media/jui/js/jquery.min.js"></script>
	<script src="<?php echo JUri::root(); ?>media/jui/js/bootstrap.min.js"></script>
	<script src="<?php echo JUri::root(); ?>media/jui/js/chosen.jquery.min.js"></script>
	<script type="text/javascript">
	  jQuery.noConflict();
	</script>
	<jdoc:include type="head" />
	<script type="text/javascript">
		window.addEvent('domready', function () {
			document.getElementById('form-login').username.select();
			document.getElementById('form-login').username.focus();
		});
	</script>
	<style type="text/css">
		/* Responsive Styles */
		@media (max-width: 480px) {
			.view-login .container{
				margin-top: -170px;
			}
			.btn{
				font-size: 13px;
				padding: 4px 10px 4px;
			}
		}
	</style>
</head>

<body class="site <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " itemid-" . $itemid . " ";?>">
	<!-- Container -->
	<div class="container">
		<div id="content">
			<!-- Begin Content -->
			<div id="element-box" class="login well">
				<img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/images/joomla.png" alt="Joomla!" />
				<hr />
				<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
			<noscript>
				<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
			</noscript>
			<!-- End Content -->
		</div>
	</div>
	<div class="navbar navbar-fixed-bottom hidden-phone">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<p>&copy; <?php echo $sitename; ?> <?php echo date('Y');?></p>
			</div>
			<div class="btn-group">
				<a class="login-joomla" href="http://www.joomla.org" rel="tooltip" title="<?php echo JText::_('TPL_ISIS_ISFREESOFTWARE');?>">Joomla!&#174;</a>
			</div>
			<div class="btn-group pull-left">
				<a href="<?php echo JURI::root(); ?>"><i class="icon-share icon-white"></i> <?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE') ?></a>
			</div>
		</div>
	</div>
	<jdoc:include type="modules" name="debug" style="none" />
	<script>
		(function($){
			$('*[rel=tooltip]').tooltip()
			$('*[rel=popover]').popover()
		    // Chosen select boxes
		    $("select").chosen({disable_search_threshold : 10 });
	    })(jQuery);
	</script>
</body>
</html>
