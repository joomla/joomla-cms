<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($width)
{
	$moduleclass_sfx .= ' ' . 'mod_search' . $module->id;
	$css             = 'div.mod_search' . $module->id . ' input[type="search"]{ width:auto; }';
	JFactory::getDocument()->addStyleDeclaration($css);
	$width = ' size="' . $width . '"';
}
else
{
	$width = '';
}

// Break info into sections so it can be manipulated.
$label    = '<label for="mod-search-searchword' . $module->id . '" class="element-invisible sr-only">' . $label . '</label> ';
$inputbox = '<input name="searchword" id="mod-search-searchword' . $module->id . '" maxlength="' . $maxlength . '"  class="form-control" type="search"' . $width . ' placeholder="' . $text . '" />';


if ($button)
{
	if ($imagebutton) :
		$searchbtn = ' <input type="image" alt="' . $button_text . '" class="btn btn-default image" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
	else :
		$searchbtn = '<button class="btn btn-default" type="submit"  onclick="this.form.searchword.focus();"><i class="glyphicon glyphicon-search"></i>' . $button_text . '</button>';
	endif;
}
?>

<div class="search<?php echo $moduleclass_sfx; ?>">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="navbar-form" role="search">
		<?php
		switch ($button_pos) :
			case 'top' :
				echo '<div class = "input-group image-top">';
				if (!empty($searchbtn))
				{
					echo '<div class = "input-group-btn">';
					echo $searchbtn;
					echo '</div>';
				}
				echo $label . $inputbox;
				break;

			case 'bottom' :
				echo '<div class = "input-group-sm image-bottom">';
				echo $label . $inputbox;
				if (!empty($searchbtn))
				{
					echo '<div class = "input-group-btn">';
					echo $searchbtn;
					echo '</div>';
				}
				break;

			case 'right' :
			default:
				echo '<div class = "input-group-sm">';
				echo $label . $inputbox;
				if (!empty($searchbtn))
				{
					echo '<div class = "input-group-btn pull-right">';
					echo $searchbtn;
					echo '</div>';
				}
				break;

			case 'left' :
				echo '<div class = "input-group add-on">';
				if (!empty($searchbtn))
				{
					echo '<div class = "input-group-btn">';
					echo $searchbtn;
					echo '</div>';
				}
				?>
				<div id="search-icon-group">
					<?php echo $label . $inputbox; ?>
				</div>
				<?php
				break;
		endswitch;
		?>
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
		<?php echo '</div>'; ?>
	</form>
</div>
