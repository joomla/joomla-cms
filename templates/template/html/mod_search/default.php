<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

?>
<div class="search">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post">
		<?php
			$input  = '<input name="searchword" id="mod-search-searchword' . $module->id . '" class="form-control" type="search" placeholder="' . $text . '">';
			$output = '';

			if ($button) :
				if ($imagebutton) :
					$btn_output = '<input type="image" alt="' . $button_text . '" class="btn btn-primary" src="' . $img . '" onclick="this.form.searchword.focus();">';
				else :
					$btn_output = '<button class="btn btn-primary" onclick="this.form.searchword.focus();">' . $button_text . '</button>';
				endif;

				$output .= '<div class="input-group">';
				$output .= $input;
				$output .= '<span class="input-group-btn">';
				$output .= $btn_output;
				$output .= '</span>';
				$output .= '</div>';
			else :
				$output .= $input;
			endif;

			echo $output;
		?>
		<input type="hidden" name="task" value="search">
		<input type="hidden" name="option" value="com_search">
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>">
	</form>
</div>
