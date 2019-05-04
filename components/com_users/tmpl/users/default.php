<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

// Load the custom fields
//JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');


HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
?>
<div class="com-users-users user-list">
	<?php if ($this->items): ?>
    <form action="<?php echo Route::_('index.php?option=com_users&view=users'); ?>" method="post" name="adminForm" id="adminForm">
		<table class="com-users-users__table users table table-striped table-bordered table-hover">
			<thead>
			<tr>
				<th scope="col" id="categorylist_header_name">
					<a href="#" onclick="Joomla.tableOrdering('a.title','asc','', document.getElementById('adminForm'));return false;"
					   class="hasPopover" title="Title" data-content="Select to sort by this column" data-placement="top">
						Title
					</a>
				</th>
                <th scope="col" id="categorylist_header_author">
                    <a href="#" onclick="Joomla.tableOrdering('author','asc','');return false;" class="hasPopover" title="Author"
                       data-content="Select to sort by this column" data-placement="top">
                        Articles
                    </a>
                </th>
				<th scope="col" id="categorylist_header_author">
					<a href="#" onclick="Joomla.tableOrdering('author','asc','');return false;" class="hasPopover" title="Author"
					   data-content="Select to sort by this column" data-placement="top">
						Author
					</a>
				</th>
				<th scope="col" id="categorylist_header_hits">
					<a href="#" onclick="Joomla.tableOrdering('a.hits','asc','');return false;" class="hasPopover" title="Hits"
					   data-content="Select to sort by this column" data-placement="top">
						Hits
					</a>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item): ?>

				<tr class="cat-list-row<?php $i % 2; ?>">

					<td headers="categorylist_header_title" class="list-title">
						<a href="<?php echo Route::_('index.php?option=com_users&view=user&id=' . $item->id); ?>">
							<?php echo $item->name; ?>
						</a>
					</td>

                    <td>
                        <?php echo $item->articlesByUser;?>
                    </td>
					<td headers="categorylist_header_author" class="list-author">
						<?php echo $item->lastvisitDate; ?>
					</td>
					<td headers="categorylist_header_hits" class="list-hits">
						<?php if ($item->time): ?>
							<span class="badge-success">
								[online]
							</span>
						<?php else: ?>
							<span class="badge-warning">
								[offline]
							</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
        <input type="hidden" name="limitstart" value="">
        <div class="pagination">
            <p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
			<?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    </form>
	<?php endif; ?>
</div>
