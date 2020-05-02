<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_mails&view=templates'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="templateList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_MAILS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<th scope="col" style="min-width:100px">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:15%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_MAILS_HEADING_COMPONENT', 'a.component', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-md-table-cell">
									<?php echo Text::_('COM_MAILS_HEADING_TEMPLATES_FOR_LANGUAGES'); ?>
								</th>
								<th scope="col" class="w-10 d-md-table-cell">
									<?php echo Text::_('COM_MAILS_HEADING_NO_TEMPLATES_FOR_LANGUAGES'); ?>
								</th>
								<th scope="col" style="width:30%" class="d-none d-md-table-cell">
									<?php echo Text::_('COM_MAILS_HEADING_DESCRIPTION'); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							list($component, $sub_id) = explode('.', $item->template_id, 2);
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="break-word">
									<div class="dropdown">
										<a class="dropdown-toggle" href="#" role="button" id="mTemplate<?php echo $i; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?>
										</a>
										<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
											<?php foreach ($this->languages as $language) : ?>
												<a class="dropdown-item" href="<?php echo Route::_('index.php?option=com_mails&task=template.edit&template_id=' . $item->template_id . '&language=' . $language->lang_code); ?>">
													<?php if (in_array($language->lang_code, $item->languages)) : ?>
														<?php echo Text::sprintf('COM_MAILS_LIST_EDIT_TEMPLATE', $language->title); ?>
													<?php else: ?>
														<?php echo Text::sprintf('COM_MAILS_LIST_CREATE_TEMPLATE', $language->title); ?>
													<?php endif; ?>
												</a>
											<?php endforeach; ?>
										</div>
									</div>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo Text::_($component); ?>
								</td>
								<td class="d-md-table-cell">
									<?php foreach ($this->languages as $language) : ?>
										<?php if (in_array($language->lang_code, $item->languages)) : ?>
											<?php if ($language->image) : ?>
												<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
											<?php else : ?>
												<span class="badge badge-secondary" title="<?php echo $language->title_native; ?>"><?php echo strtoupper($language->sef); ?></span>
											<?php endif; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</td>
								<td class="d-md-table-cell">
									<?php foreach ($this->languages as $language) : ?>
										<?php if (!in_array($language->lang_code, $item->languages)) : ?>
											<?php if ($language->image) : ?>
												<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
											<?php else : ?>
												<span class="badge badge-secondary"><?php echo strtoupper($language->sef); ?></span>
											<?php endif; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo Text::_($component . '_MAIL_' . $sub_id . '_DESC'); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->template_id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
