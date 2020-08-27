<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

?>
<div class="card float-right article-index">
	<div class="card-body">

		<?php if ($headingtext) : ?>
		<h3><?php echo $headingtext; ?></h3>
		<?php endif; ?>

		<ul class="nav flex-column">
		<?php foreach ($list as $listItem) : ?>
			<?php $class = $listItem->active ? ' active' : ''; ?>
			<li>
				<a href="<?php echo Route::_($listItem->link); ?>" class="toclink<?php echo $class; ?>">
					<?php echo $listItem->title; ?>
				</a>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
</div>
