<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doTask = $displayData['doTask'];
$class  = $displayData['class'];
$text   = $displayData['text'];
$name   = $displayData['name'];
?>
<button onclick="<?php echo $doTask; ?>" class="btn btn-small modal" data-toggle="modal" data-target="#modal-<?php echo $name; ?>">
	<i class="<?php echo $class; ?>"></i>
	<?php echo $text; ?>
</button>
