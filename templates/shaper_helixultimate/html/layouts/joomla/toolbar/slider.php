<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

JHtml::_('behavior.core');

$id      = isset($displayData['id']) ? $displayData['id'] : '';
$doTask  = $displayData['doTask'];
$class   = $displayData['class'];
$text    = $displayData['text'];
$name    = $displayData['name'];
$onClose = $displayData['onClose'];
?>
<button<?php echo $id; ?> onclick="<?php echo $doTask; ?>" class="btn btn-sm btn-secondary" data-toggle="collapse" data-target="#collapse-<?php echo $name; ?>"<?php echo $onClose; ?>>
	<span class="icon-cog" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
