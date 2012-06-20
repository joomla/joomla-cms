<?php
/**
* @copyright Copyright (C) 2008 JoomlaPraise. All rights reserved.
*/

// no direct access
defined('_JEXEC') or die;

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

// Add Stylesheets
$doc->addStyleSheet('../templates/system/css/bootstrap.css');
$doc->addStyleSheet('../templates/system/css/bootstrap-extended.css');
$doc->addStyleSheet('../templates/system/css/chosen.css');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
	<script src="../templates/system/js/jquery.js"></script>
	<script src="../templates/system/js/bootstrap.min.js"></script>
	<script src="../templates/system/js/chosen.jquery.min.js"></script>
	<script type="text/javascript">
	  jQuery.noConflict();
	</script>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
</head>
<body class="contentpane modal">
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
