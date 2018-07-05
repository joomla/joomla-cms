<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$list = $displayData['list'];

$startDisabled = $list['start']['active'] ? '' : ' disabled'; 
$prevDisabled  = $list['previous']['active'] ? '' : ' disabled'; 
$nextDisabled  = $list['next']['active'] ? '' : ' disabled'; 
$endDisabled   = $list['end']['active'] ? '' : ' disabled'; 

?>
<ul class="j-pagination">
	<li class="pagination-start<?php echo $startDisabled; ?> j-page-item"><?php echo $list['start']['data']; ?></li>
	<li class="pagination-prev<?php echo $prevDisabled; ?> j-page-item"><?php echo $list['previous']['data']; ?></li>
	<?php foreach ($list['pages'] as $page) : ?>
		<?php $disabled = $page['active'] ? '' : ' disabled'; ?>
		<?php echo '<li class="j-page-item' . $disabled . '">' . $page['data'] . '</li>'; ?>
	<?php endforeach; ?>
	<li class="pagination-next<?php echo $nextDisabled; ?> j-page-item"><?php echo $list['next']['data']; ?></li>
	<li class="pagination-end<?php echo $endDisabled; ?> j-page-item"><?php echo $list['end']['data']; ?></li>
</ul>
