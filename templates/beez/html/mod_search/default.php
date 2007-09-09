<?php
// @version $Id$
defined('_JEXEC') or die('Restricted access'); 
?>

<form action="index.php"  method="post" class="search<?php echo $params->get('moduleclass_sfx'); ?>">
	<label for="mod_search_searchword">	
		<?php echo JText::_('search') ?>
	</label>
	<?php echo $inputfield; ?>
	<input type="hidden" name="option" value="com_search" />
	<input type="hidden" name="task"   value="search" />
</form>
