<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="search<?php echo $moduleclass_sfx ?>">
	<form action="<?php echo JRoute::_('index.php');?>" method="post" class="form-inline">
		<?php
			$output = '<label for="mod-search-searchword" class="element-invisible">' . $label . '</label> ';
			$output .= '<input name="searchword" id="mod-search-searchword" maxlength="' . $maxlength . '"  class="inputbox search-query" type="search" size="' . $width . '"';

			jimport('joomla.environment.browser');
			$browser        = JBrowser::getInstance();
			$browserVersion = $browser->getMajor();

			if (($browser->isBrowser('msie') && ($browserVersion < 10))
				|| ($browser->isBrowser('mozilla') && ($browserVersion < 4))
				|| ($browser->isBrowser('chrome') && ($browserVersion < 4))
				|| ($browser->isBrowser('safari') && ($browserVersion < 5))
				|| ($browser->isBrowser('opera') && ($browserVersion < 11)))
			{
				// Show old javascript variant in not HTML 5 compliant browsers
				$output .= ' value="' . $text . '" onblur="if (this.value==\'\') this.value=\'' . $text . '\';" onfocus="if (this.value==\'' . $text . '\') this.value=\'\';"';
			}
			else
			{
				// Use HTML 5 placeholder attribute if supported
				$output .= ' placeholder="' . $text . '"';
			}

			$output .= ' />';

			if ($button) :
				if ($imagebutton) :
					$btn_output = ' <input type="image" alt="' . $button_text . '" class="button" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
				else :
					$btn_output = ' <button class="button btn btn-primary" onclick="this.form.searchword.focus();">' . $button_text . '</button>';
				endif;

				switch ($button_pos) :
					case 'top' :
						$output = $btn_output . '<br />' . $output;
						break;

					case 'bottom' :
						$output .= '<br />' . $btn_output;
						break;

					case 'right' :
						$output .= $btn_output;
						break;

					case 'left' :
					default :
						$output = $btn_output . $output;
						break;
				endswitch;

			endif;

			echo $output;
		?>
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
	</form>
</div>
