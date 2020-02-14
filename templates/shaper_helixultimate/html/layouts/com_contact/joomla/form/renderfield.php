<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

extract($displayData);

if (!empty($options['showonEnabled']))
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}

$class = empty($options['class']) ? '' : ' ' . $options['class'];
$rel   = empty($options['rel']) ? '' : ' ' . $options['rel'];

/**
* @TODO:
*
* As mentioned in #8473 (https://github.com/joomla/joomla-cms/pull/8473), ...
* as long as we cannot access the field properties properly, this seems to
* be the way to go for now.
*
* On a side note: Parsing html is seldom a good idea.
* https://stackoverflow.com/questions/1732348/regex-match-open-tags-except-xhtml-self-contained-tags/1732454#1732454
*/
preg_match('/class=\"([^\"]+)\"/i', $input, $match);

$required      = (strpos($input, 'aria-required="true"') !== false || (!empty($match[1]) && strpos($match[1], 'required') !== false));
$typeOfSpacer  = (strpos($label, 'spacer-lbl') !== false);

?>
<div class="form-group<?php echo $class; ?>"<?php echo $rel; ?>>
	<?php if (empty($options['hiddenLabel'])) : ?>
		<?php echo $label; ?>
		<?php if (!$required && !$typeOfSpacer) : ?>
			<span class="optional"><?php echo JText::_('COM_CONTACT_OPTIONAL'); ?></span>
		<?php endif; ?>
	<?php endif; ?>
	<?php echo $input; ?>
</div>
