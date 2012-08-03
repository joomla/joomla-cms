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
$lang = JFactory::getLanguage();

// Add Stylesheets
$doc->addStyleSheet('templates/' .$this->template. '/css/template.css');
$doc->addStyleSheet('../media/jui/css/chosen.css');

// If Right-to-Left
if ($this->direction == 'rtl') :
	$doc->addStyleSheet('../media/jui/css/bootstrap-rtl.css');
endif;

// Load specific language related CSS
$file = 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css';
if (is_file($file)) :
	$doc->addStyleSheet($file);
endif;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
	<script src="../media/jui/js/jquery.js"></script>
	<script src="../media/jui/js/bootstrap.min.js"></script>
	<script src="../media/jui/js/chosen.jquery.min.js"></script>
	<script type="text/javascript">
	  jQuery.noConflict();
	</script>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
</head>
<body class="contentpane component">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
	<script>
		(function($){
			$('*[rel=tooltip]').tooltip()
			$('.tip-bottom').tooltip({placement: "bottom"})
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
