<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 * 	$options      : (array)  Optional parameters
 * 	$name         : (string) The id of the input this label is for
 * 	$label        : (string) The html code for the label
 * 	$input        : (string) The input field html code
 * 	$description  : (string) An optional description to use in a tooltip
 */

if (!empty($options['showonEnabled']))
{
	HTMLHelper::_('script', 'system/showon.min.js', array('version' => 'auto', 'relative' => true));
}
$class = empty($options['class']) ? '' : ' ' . $options['class'];
$rel   = empty($options['rel']) ? '' : ' ' . $options['rel'];
$id    = $name . '-desc';
$hide  = empty($options['hiddenLabel']) ? '' : ' sr-only';

?>
<div class="control-group<?php echo $class; ?>"<?php echo $rel; ?>>
	<div class="control-label<?php echo $hide; ?>"><?php echo $label; ?></div>
	<div class="controls">
		<?php echo $input; ?>
		<?php if (!empty($description)) : ?>
			<div id="<?php echo $id; ?>">
				<small class="form-text text-muted">
					<?php echo $description; ?>
				</small>
			</div>
		<?php endif; ?>
	</div>
</div>
