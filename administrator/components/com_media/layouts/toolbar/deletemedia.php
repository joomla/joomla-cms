<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('JTOOLBAR_DELETE');
JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
?>
<script type="text/javascript">
(function($){
	// if any media is selected then only allow to submit otherwise show message
	deleteMedia = function(){
		if ( $('#folderframe').contents().find('input:checked[name="rm[]"]').length == 0){
			alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
			return false;
		}

	MediaManager.submit('folder.delete');
	};

})(jQuery);
</script>

<button onclick="deleteMedia()" class="btn btn-outline-danger btn-sm">
	<span class="icon-remove" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</button>
