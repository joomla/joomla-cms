<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div role="main">
	<h1 class="mb-3"><?php echo $this->item->title; ?></h1>
	<fieldset>
		<caption><?php echo Text::_('COM_FINDER_ITEM_FIELDSET_ITEM_TITLE'); ?></caption>
		<dl class="row">
			<?php foreach ($this->item as $key => $value) : ?>
			<dt class="col-sm-3"><?php echo $key; ?></dt>
			<dd class="col-sm-9"><?php echo $value; ?></dd>
			<?php endforeach; ?>
		</dl>
	</fieldset>
	<fieldset>
		<caption><?php echo Text::_('COM_FINDER_ITEM_FIELDSET_TERMS_TITLE'); ?></caption>
		<table class="table">
			<thead>
			<tr>
				<td>id</td>
				<td>term</td>
				<td>stem</td>
				<td>common</td>
				<td>phrase</td>
				<td>weight</td>
				<td>links</td>
				<td>language</td>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->terms as $term) : ?>
				<tr>
					<td><?php echo $term->term_id; ?></td>
					<td><?php echo $term->term; ?></td>
					<td><?php echo $term->stem; ?></td>
					<td><?php echo $term->common; ?></td>
					<td><?php echo $term->phrase; ?></td>
					<td><?php echo $term->weight; ?></td>
					<td><?php echo $term->links; ?></td>
					<td><?php echo $term->language; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
	<fieldset>
		<caption><?php echo Text::_('COM_FINDER_ITEM_FIELDSET_TAXONOMIES_TITLE'); ?></caption>
		<table class="table">
			<thead>
				<tr>
					<td>id</td>
					<td>title</td>
					<td>alias</td>
					<td>lft</td>
					<td>path</td>
					<td>state</td>
					<td>access</td>
					<td>language</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->taxonomies as $taxonomy) : ?>
					<tr>
						<td><?php echo $taxonomy->id; ?></td>
						<td><?php echo $taxonomy->title; ?></td>
						<td><?php echo $taxonomy->alias; ?></td>
						<td><?php echo $taxonomy->lft; ?></td>
						<td><?php echo $taxonomy->path; ?></td>
						<td><?php echo $taxonomy->state; ?></td>
						<td><?php echo $taxonomy->access; ?></td>
						<td><?php echo $taxonomy->language; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</fieldset>
</div>
