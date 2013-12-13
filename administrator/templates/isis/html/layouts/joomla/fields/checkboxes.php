<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$html = array();

// Initialize some field attributes.
$class          = !empty($displayData->class) ? 'checkboxes ' . $displayData->class  : 'checkboxes';
$checkedOptions = explode(',', (string) $displayData->checkedOptions);
$required       = $displayData->required ? ' required aria-required="true"' : '';
$autofocus      = $displayData->autofocus ? ' autofocus' : '';
$options = $displayData->getOptions();

if (!empty($options)) {
	// Including fallback code for HTML5 non supported browsers.
	JHtml::_('jquery.framework');
	JHtml::_('script', 'system/html5fallback.js', false, true);
	?>
	<fieldset id="<?php echo  $displayData->id; ?>" class="<?php echo $class ?>" <?php echo $required . $autofocus; ?>>
		<ul>
			<?php 
			foreach ($options as $i => $option) {
				// Initialize some option attributes.
				if (!isset($displayData->value) || empty($displayData->value))
				{
					$checked = (in_array((string) $option->value, (array) $checkedOptions) ? ' checked' : '');
				}
				else
				{
					$value = !is_array($displayData->value) ? explode(',', $displayData->value) : $displayData->value;
					$checked = (in_array((string) $option->value, $value) ? ' checked' : '');
				}

				$checked = empty($checked) && $option->checked ? ' checked' : $checked;

				$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
				$disabled = !empty($option->disable) || $displayData->disabled ? ' disabled' : '';

				// Initialize some JavaScript option attributes.
				$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
				$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';
				?>
				<li>
					<input type="checkbox" id="<?php echo $displayData->id . $i ?>" name="<?php echo  $displayData->name ?>" value="<?php echo
					htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') ?>"<?php echo $checked . $class . $onclick . $onchange . $disabled ?>/>
					<label for="<?php echo $displayData->id . $i ?>"<?php echo $class ?>><?php echo JText::_($option->text) ?></label>
				</li>
			<?php } ?>
		</ul>
	</fieldset>
<?php }
