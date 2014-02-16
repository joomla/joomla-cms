<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doTask  = $displayData['doTask'];
$class   = $displayData['class'];
$text    = $displayData['text'];
$name    = $displayData['name'];
$onClose = $displayData['onClose'];
?>

<a onclick="<?php echo $doTask; ?>" data-toggle="collapse" data-target="#collapse-<?php echo $name; ?>"<?php echo $onClose; ?>>
	<span class="<?php echo $class; ?>"></span>
	<?php echo $text; ?>
</a>
