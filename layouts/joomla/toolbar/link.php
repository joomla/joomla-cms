<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$doTask     = $displayData['doTask'];
$class      = $displayData['class'];
$text       = $displayData['text'];
$floatRight = (strpos($doTask, 'index.php?option=com_config') === false) ? '' : ' float-sm-right';
?>
<button onclick="location.href='<?php echo $doTask; ?>';" class="btn btn-outline-danger btn-sm<?php echo $floatRight; ?>">
	<span class="<?php echo $class; ?>"></span>
	<?php echo $text; ?>
</button>
