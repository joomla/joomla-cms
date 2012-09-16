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
$this->language = $doc->language;
$this->direction = $doc->direction;

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Add Stylesheets
$doc->addStyleSheet('templates/' .$this->template. '/css/template.css');

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
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<!--[if lt IE 9]>
		<script src="../media/jui/js/html5.js"></script>
	<![endif]-->
</head>
<body class="contentpane component">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
	<script>
		(function($){
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
