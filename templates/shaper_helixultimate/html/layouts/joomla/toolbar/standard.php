<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

JHtml::_('behavior.core');

$id       = isset($displayData['id']) ? $displayData['id'] : '';
$doTask   = $displayData['doTask'];
$class    = $displayData['class'];
$text     = $displayData['text'];
$btnClass = $displayData['btnClass'];
$group    = $displayData['group'];
?>

<?php if ($group) : ?>
<a<?php echo $id; ?> href="#" onclick="<?php echo $doTask; ?>" class="dropdown-item">
	<span class="<?php echo trim($class); ?>"></span>
	<?php echo $text; ?>
</a>
<?php else : ?>
<button<?php echo $id; ?> onclick="<?php echo $doTask; ?>" class="<?php echo $btnClass; ?>">
	<span class="<?php echo trim($class); ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
<?php endif; ?>
