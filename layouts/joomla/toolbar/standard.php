<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');

$id       = isset($displayData['id']) ? $displayData['id'] : '';
$doTask   = $displayData['doTask'];
$class    = $displayData['class'];
$text     = $displayData['text'];
$btnClass = $displayData['btnClass'];
$group    = $displayData['group'];
$task     = $displayData['task'];
$list     = $displayData['list'] ? 'list-confirmation' : '';
$form     = $displayData['form'] ? ' form=' . $displayData['formId'] . '"' : '';
$validate = $displayData['validate'] ? ' form-validation' : '';

if ($list)
{
	\JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
	\JText::script('ERROR');
}
?>

<?php /*if ($group) : ?>
<a<?php echo $id; ?> href="#" onclick="<?php echo $doTask; ?>" class="dropdown-item">
	<span class="<?php echo trim($class); ?>"></span>
	<?php echo $text; ?>
</a>
<?php else : ?>
<button<?php echo $id; ?> onclick="<?php echo $doTask; ?>" class="<?php echo $btnClass; ?>">
	<span class="<?php echo trim($class); ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
<?php endif;*/ ?>

<joomla-toolbar-button <?php echo $id; ?> task="<?php echo $task; ?>" <?php echo $list.$form.$validate; ?> class="<?php echo $btnClass; ?>">
	<span class="<?php echo trim($class); ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</joomla-toolbar-button>
