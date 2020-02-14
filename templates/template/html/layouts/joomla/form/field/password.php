<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

extract($displayData);

if ($meter)
{
	JHtml::_('behavior.formvalidator');
	JHtml::_('script', 'system/fields/passwordstrength.min.js', array('version' => 'auto', 'relative' => true));

	$class = 'js-password-strength ' . $class;

	if ($forcePassword)
	{
		$class = $class . ' meteredPassword';
	}
}

JHtml::_('script', 'system/fields/passwordview.min.js', array('version' => 'auto', 'relative' => true));

JText::script('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
JText::script('JFIELD_PASSWORD_INDICATE_COMPLETE');
JText::script('JSHOW');
JText::script('JHIDE');

$attributes = array(
	strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
	!$autocomplete ? 'autocomplete="off"' : '',
	!empty($class) ? 'class="form-control ' . $class . '"' : 'class="form-control"',
	$readonly ? 'readonly' : '',
	$disabled ? 'disabled' : '',
	!empty($size) ? 'size="' . $size . '"' : '',
	!empty($maxLength) ? 'maxlength="' . $maxLength . '"' : '',
	$required ? 'required aria-required="true"' : '',
	$autofocus ? 'autofocus' : '',
	!empty($minLength) ? 'data-min-length="' . $minLength . '"' : '',
	!empty($minIntegers) ? 'data-min-integers="' . $minIntegers . '"' : '',
	!empty($minSymbols) ? 'data-min-symbols="' . $minSymbols . '"' : '',
	!empty($minUppercase) ? 'data-min-uppercase="' . $minUppercase . '"' : '',
	!empty($minLowercase) ? 'data-min-lowercase="' . $minLowercase . '"' : '',
	!empty($forcePassword) ? 'data-min-force="' . $forcePassword . '"' : '',
);

?>
<div class="password-group">
	<div class="input-group">
		<span class="input-group-prepend">
			<span class="input-group-text">
				<span class="fa fa-key" aria-hidden="true"></span>
				<span class="sr-only"><?php echo JText::_('JSHOW'); ?></span>
			</span>
		</span>
		<input
			type="password"
			name="<?php echo $name; ?>"
			id="<?php echo $id; ?>"
			value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
			<?php echo implode(' ', $attributes); ?>>
	</div>
</div>
