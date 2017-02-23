<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="search<?php echo $moduleclass_sfx; ?>">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post">
		<?php
			$btnBlock = '';
			if ($button_pos === 'top' || $button_pos === 'bottom')
			{
				$btnBlock = ' btn-block';
			}

			$input = '<input name="searchword" id="mod-search-searchword' . $module->id . '" maxlength="' . $maxlength . '" class="form-control" type="search"';
			$input .= ' placeholder="' . $text . '">';

			$output = '';

			if ($button) :
				if ($imagebutton) :
					$btn_output = '<input type="image" alt="' . $button_text . '" class="btn btn-primary' . $btnBlock . '" src="' . $img . '" onclick="this.form.searchword.focus();">';
				else :
					$btn_output = '<button class="btn btn-primary' . $btnBlock . '" onclick="this.form.searchword.focus();">' . $button_text . '</button>';
				endif;

				switch ($button_pos) :
					case 'top' :
						$output = $btn_output . '<br>' . $input;
						break;

					case 'bottom' :
						$output .= $input . '<br>' . $btn_output;
						break;

					case 'right' :
						$output .= '<div class="input-group">';
						$output .= $input;
						$output .= '<span class="input-group-btn">';
						$output .= $btn_output;
						$output .= '</span>';
						$output .= '</div>';
						break;

					case 'left' :
					default :
						$output .= '<div class="input-group">';
						$output .= '<span class="input-group-btn">';
						$output .= $btn_output;
						$output .= '</span>';
						$output .= $input;
						$output .= '</div>';
						break;
				endswitch;
			endif;

			echo $output;
		?>
		<input type="hidden" name="task" value="search">
		<input type="hidden" name="option" value="com_search">
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>">
	</form>
</div>
