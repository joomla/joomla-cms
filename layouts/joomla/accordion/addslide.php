<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$id        = empty($displayData['id']) ? '' : $displayData['id'];
$in        = empty($displayData['in']) ? '' : $displayData['in'];
$collapsed = empty($displayData['collapsed']) ? '' : $displayData['collapsed'];
$parent    = empty($displayData['parent']) ? '' : $displayData['parent'];
$class     = empty($displayData['class']) ? '' : $displayData['class'];
$text      = empty($displayData['text']) ? '' : $displayData['text'];

?>

<div class="card mb-2<?php echo $class; ?>">
	<a href="#<?php echo $id; ?>" data-toggle="collapse"<?php echo $parent; ?> class="card-header<?php echo $collapsed; ?>" role="tab">
		<?php echo $text; ?>
	</a>
	<div class="collapse<?php echo $in; ?>" id="<?php echo $id; ?>" role="tabpanel">
		<div class="card-block">
