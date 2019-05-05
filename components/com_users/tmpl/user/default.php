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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
	<h1>
		<?php echo $this->escape($this->item->name); ?>
	</h1>

<?php if (empty($this->item->articles)) : ?>
	<p class="com-users-users__no-users"><?php echo JText::_('COM_USERS_NO_ARTICLES'); ?></p>
<?php else : ?>
	<div class="com-users-users user-list">
		<table class="com-users-users__table users table table-striped table-bordered table-hover">
			<thead>
			<tr>
				<th scope="col" id="userlist_header_name">
					<?php echo Text::_('COM_USERS_USER_ARTICLE_TITLE'); ?>
				</th>
				<th scope="col" id="userlist_header_articles">
					<?php echo Text::_('COM_USERS_USER_ARTICLE_DATE'); ?>
				</th>
				<th scope="col" id="userlist_header_lastvisit">
					<?php echo Text::_('COM_USERS_USER_ARTICLE_HITS'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->item->articles as $i => $article): ?>

				<tr class="cat-list-row<?php $i % 2; ?>">

					<td headers="categorylist_header_title" class="list-title">
						<a href="<?php echo Route::_('index.php?option=com_users&view=user&id=' . $article->id); ?>">
							<?php echo $article->title; ?>
						</a>
					</td>
					<td headers="categorylist_header_author" class="list-author">
						<?php echo HTMLHelper::_(
							'date', $article->created,
							$this->escape(
								Text::_('DATE_FORMAT_LC1')
							)
						); ?>
					</td>

					<td headers="categorylist_header_hits" class="list-hits">
						<?php echo $article->hits; ?>
					</td>
				</tr>

			<?php endforeach; ?>

			</tbody>
		</table>

	</div>
<?php endif;

