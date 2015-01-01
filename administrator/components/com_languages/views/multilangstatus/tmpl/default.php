<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$notice_homes     = $this->homes == 2 || $this->homes == 1 || $this->homes - 1 != count($this->contentlangs) && ($this->language_filter || $this->switchers != 0);
$notice_disabled  = !$this->language_filter	&& ($this->homes > 1 || $this->switchers != 0);
$notice_switchers = !$this->switchers && ($this->homes > 1 || $this->language_filter);
?>
<div class="mod-multilangstatus">
	<?php if (!$this->language_filter && $this->switchers == 0) : ?>
		<?php if ($this->homes == 1) : ?>
			<div class="alert alert-info"><?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_NONE'); ?></div>
		<?php else: ?>
			<div class="alert alert-info"><?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_USELESS_HOMES'); ?></div>
		<?php endif; ?>
	<?php else: ?>
	<table class="table table-striped table-condensed">
		<tbody>
		<?php if ($notice_homes) : ?>
			<tr class="warning">
				<td>
					<i class="icon-pending"></i>
				</td>
				<td>
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_MISSING'); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ($notice_disabled) : ?>
			<tr class="warning">
				<td>
					<i class="icon-pending"></i>
				</td>
				<td>
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_LANGUAGEFILTER_DISABLED'); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ($notice_switchers) : ?>
			<tr class="warning">
				<td>
					<i class="icon-pending"></i>
				</td>
				<td>
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_LANGSWITCHER_UNPUBLISHED'); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php foreach ($this->contentlangs as $contentlang) : ?>
			<?php if (array_key_exists($contentlang->lang_code, $this->homepages) && (!array_key_exists($contentlang->lang_code, $this->site_langs) || !$contentlang->published)) : ?>
				<tr class="warning">
					<td>
						<i class="icon-pending"></i>
					</td>
					<td>
						<?php echo JText::sprintf('COM_LANGUAGES_MULTILANGSTATUS_ERROR_CONTENT_LANGUAGE', $contentlang->lang_code); ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if (!array_key_exists($contentlang->lang_code, $this->site_langs)) : ?>
				<tr class="warning">
					<td>
						<i class="icon-pending"></i>
					</td>
					<td>
						<?php echo JText::sprintf('COM_LANGUAGES_MULTILANGSTATUS_ERROR_LANGUAGE_TAG', $contentlang->lang_code); ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ($this->listUsersError) : ?>
			<tr class="info">
				<td>
					<i class="icon-help"></i>
				</td>
				<td>
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_CONTACTS_ERROR_TIP'); ?>
					<ul>
					<?php foreach ($this->listUsersError as $user) : ?>
						<li>
						<?php echo JText::sprintf('COM_LANGUAGES_MULTILANGSTATUS_CONTACTS_ERROR', $user->name); ?>
						</li>
					<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
	<table class="table table-striped table-condensed" style="border-top: 1px solid #CCCCCC;">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('JDETAILS'); ?>
				</th>
				<th>
					<?php echo JText::_('JSTATUS'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row">
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_LANGUAGEFILTER'); ?>
				</th>
				<td class="center">
					<?php if ($this->language_filter) : ?>
						<?php echo JText::_('JENABLED'); ?>
					<?php else : ?>
						<?php echo JText::_('JDISABLED'); ?>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_LANGSWITCHER_PUBLISHED'); ?>
				</th>
				<td class="center">
					<?php if ($this->switchers != 0) : ?>
						<?php echo $this->switchers; ?>
					<?php else : ?>
						<?php echo JText::_('JNONE'); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php if ($this->homes > 1) : ?>
						<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED_INCLUDING_ALL'); ?>
					<?php else : ?>
						<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED'); ?>
					<?php endif; ?>
				</th>
				<td class="center">
					<?php if ($this->homes > 1) : ?>
						<?php echo $this->homes; ?>
					<?php else : ?>
						<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED_ALL'); ?>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<table class="table table-striped table-condensed" style="border-top: 1px solid #CCCCCC;">
		<thead>
			<tr>
				<th>
					<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
				</th>
				<th class="center">
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_SITE_LANG_PUBLISHED'); ?>
				</th>
				<th class="center">
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_LANGUAGE_PUBLISHED'); ?>
				</th>
				<th class="center">
					<?php echo JText::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->statuses as $status) : ?>
				<?php if ($status->element) : ?>
					<tr>
						<td>
							<?php echo $status->element; ?>
						</td>
				<?php endif; ?>
				<?php if ($status->element) : // Published Site languages ?>
						<td class="center">
							<i class="icon-ok"></i>
						</td>
				<?php else : ?>
						<td class="center">
							<?php echo JText::_('JNO'); ?>
						</td>
				<?php endif; ?>
				<?php if ($status->lang_code && $status->published) : // Published Content languages ?>
						<td class="center">
							<i class="icon-ok"></i>
						</td>
				<?php else : ?>
						<td class="center">
							<i class="icon-pending"></i>
						</td>
				<?php endif; ?>
				<?php if ($status->home_language) : // Published Home pages ?>
						<td class="center">
							<i class="icon-ok"></i>
						</td>
				<?php else : ?>
						<td class="center">
							<i class="icon-not-ok"></i>
						</td>
				<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			<?php foreach ($this->contentlangs as $contentlang) : ?>
				<?php if (!array_key_exists($contentlang->lang_code, $this->site_langs)) : ?>
					<tr>
						<td>
							<?php echo $contentlang->lang_code; ?>
						</td>
						<td class="center">
							<i class="icon-pending"></i>
						</td>
						<td class="center">
							<?php if ($contentlang->published) : ?>
								<i class="icon-ok"></i>
							<?php elseif (!$contentlang->published && array_key_exists($contentlang->lang_code, $this->homepages)) : ?>
								<i class="icon-not-ok"></i>
							<?php elseif (!$contentlang->published) : ?>
								<i class="icon-pending"></i>
							<?php endif; ?>
						</td>
						<td class="center">
							<?php if (!array_key_exists($contentlang->lang_code, $this->homepages)) : ?>
								<i class="icon-pending"></i>
							<?php else : ?>
								<i class="icon-ok"></i>
							<?php endif; ?>
						</td>
				<?php endif; ?>
			<?php endforeach; ?>
			</tr>
		</tbody>
	</table>
	<?php endif; ?>
</div>
