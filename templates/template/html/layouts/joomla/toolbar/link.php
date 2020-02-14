<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

$id     = isset($displayData['id']) ? $displayData['id'] : '';
$doTask = $displayData['doTask'];
$class  = $displayData['class'];
$text   = $displayData['text'];
$margin = (strpos($doTask, 'index.php?option=com_config') === false) ? '' : ' ml-auto';
?>
<button<?php echo $id; ?> class="btn btn-outline-danger btn-sm<?php echo $margin; ?>" onclick="location.href='<?php echo $doTask; ?>';">
	<span class="<?php echo $class; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
