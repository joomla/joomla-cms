<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$list = $displayData['list'];
?>
<ul>
	<li class="pagination-start"><?php echo $list['start']['data']; ?></li>
	<li class="pagination-prev"><?php echo $list['previous']['data']; ?></li>
	<?php foreach ($list['pages'] as $page) : ?>
		<?php echo '<li>' . $page['data'] . '</li>'; ?>
	<?php endforeach; ?>
	<li class="pagination-next"><?php echo $list['next']['data']; ?></li>
	<li class="pagination-end"><?php echo $list['end']['data']; ?></li>
</ul>
