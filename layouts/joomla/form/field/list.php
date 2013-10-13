<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$data = (object) $displayData;

$attributes = array();

$attributes['id']            = $data->id;
$attributes['class']         = !empty($data->class) ? (string) $data->class : null;
$attributes['size']          = !empty($data->size) ? (int) $data->size : null;
$attributes['multiple']      = $data->multiple ? 'multiple' : null;
$attributes['required']      = $data->required ? 'required' : null;
$attributes['aria-required'] = $data->required ? 'true' : null;
$attributes['onchange']      = $data->element['onchange'] ? (string) $data->element['onchange'] : null;
$attributes['autofocus']     = $data->autofocus ? ' autofocus' : null;

if ((string) $data->element['readonly'] == 'true' || (string) $data->element['disabled'] == 'true')
{
	$attributes['disabled'] = 'disabled';
}

$renderedAttributes = null;

if ($attributes)
{
	foreach ($attributes as $attribute => $value)
	{
		if (null !== $value)
		{
			$renderedAttributes .= ' ' . $attribute . '="' . (string) $value . '"';
		}
	}
}

$readOnly = ((string) $data->element['readonly'] == 'true');

// If it's readonly the select will have no name
$selectName = $readOnly ? '' : $data->name;
?>

<select name="<?php echo $selectName; ?>" <?php echo $renderedAttributes; ?>>
	<?php if ($data->options) : ?>
		<?php foreach ($data->options as $option) :?>
				<option value="<?php echo $option->value; ?>" <?php if ($option->value == $data->value): ?>selected="selected"<?php endif; ?>>
					<?php echo $option->text; ?>
				</option>
		<?php endforeach; ?>
	<?php endif; ?>

</select>
<?php if ($readOnly) : ?>
	<input type="hidden" name="<?php echo $data->name; ?>" value="<?php echo $data->value; ?>"/>
<?php endif;