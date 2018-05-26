<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('webcomponent', 'system/webcomponents/joomla-toolbar-button.min.js', ['relative' => true, 'version' => 'auto', 'detectDebug' => true]);

/**
 * @var  string  $id
 * @var  string  $onclick
 * @var  string  $class
 * @var  string  $text
 * @var  string  $btnClass
 * @var  string  $tagName
 * @var  string  $htmlAttributes
 */
extract($displayData, EXTR_OVERWRITE);

$tagName  = $tagName ?? 'button';

$task     = '';
$id       = !empty($displayData['id'])       ? ' id="' . $displayData['id'] . '"' : '';
$list     = !empty($displayData['list'])     ? ' list-selection' : '';
$form     = !empty($displayData['form'])     ? ' form="' . $this->escape($displayData['form']) . '"' : '';
$validate = !empty($displayData['validate']) ? ' form-validation' : '';
$msg      = !empty($displayData['msg'])      ? ' confirm-message="' . $this->escape($displayData['msg']) . '"' : '';

if (!empty($displayData['task']))
{
	$task = ' task="' . $displayData['task'] . '"';
}
elseif (!empty($displayData['doTask']))
{
	$task = ' execute="' . $displayData['doTask'] . '"';
}
?>

<joomla-toolbar-button <?php echo $id.$task.$list.$form.$validate.$msg; ?>>
<?php if (!empty($group)) : ?>
<a href="#" class="dropdown-item">
	<span class="<?php echo trim($class ?? ''); ?>"></span>
	<?php echo $text ?? ''; ?>
</a>
<?php else : ?>
<<?php echo $tagName; ?>
	class="<?php echo $btnClass ?? ''; ?>"
	<?php echo $htmlAttributes ?? ''; ?>
	>
	<span class="<?php echo trim($class ?? ''); ?>" aria-hidden="true"></span>
	<?php echo $text ?? ''; ?>
</<?php echo $tagName; ?>>
<?php endif; ?>
</joomla-toolbar-button>
