<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
