<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

\Joomla\CMS\Factory::getDocument()->addScriptDeclaration(
<<<JS
    document.querySelectorAll('input[name="jform[activateMultilanguage]"]').forEach((el) => {
	el.addEventListener('click', () => {
		Joomla.changeVisibilityFromCheckbox('installLocalisedContent', 'activateMultilanguage', 1);
		Joomla.changeVisibilityFromCheckbox('activatePluginLanguageCode', 'activateMultilanguage', 1);
	});
        Joomla.changeVisibilityFromCheckbox('installLocalisedContent', 'activateMultilanguage', 1);
        Joomla.changeVisibilityFromCheckbox('activatePluginLanguageCode', 'activateMultilanguage', 1);
    });
JS
);

/** @var Joomla\CMS\Installation\View\DefaultLanguage\HtmlView */
?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<h3><?php echo Text::_('INSTL_DEFAULTLANGUAGE_MULTILANGUAGE_TITLE'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo Text::_('INSTL_DEFAULTLANGUAGE_MULTILANGUAGE_DESC'); ?></p>
	<?php echo $this->form->renderField('activateMultilanguage'); ?>
	<div id="multilanguageOptions">
		<div id="installLocalisedContent">
			<?php echo $this->form->renderField('installLocalisedContent'); ?>
		</div>
		<div id="activatePluginLanguageCode">
			<?php echo $this->form->renderField('activatePluginLanguageCode'); ?>
		</div>
	</div>
	<h3><?php echo Text::_('INSTL_DEFAULTLANGUAGE_ADMINISTRATOR'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo Text::_('INSTL_DEFAULTLANGUAGE_DESC'); ?></p>
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th>
				<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_SELECT'); ?>
			</th>
			<th>
				<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_LANGUAGE'); ?>
			</th>
			<th>
				<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_TAG'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items->administrator as $i => $lang) : ?>
			<tr>
				<td>
					<input
						id="admin-language-cb<?php echo $i; ?>"
						type="radio"
						name="administratorlang"
						value="<?php echo $lang->language; ?>"
						<?php if ($lang->published) echo 'checked="checked"'; ?>
					/>
				</td>
				<td align="center">
					<label for="admin-language-cb<?php echo $i; ?>">
						<?php echo $lang->name; ?>
					</label>
				</td>
				<td align="center">
					<?php echo $lang->language; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<h3><?php echo Text::_('INSTL_DEFAULTLANGUAGE_FRONTEND'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo Text::_('INSTL_DEFAULTLANGUAGE_DESC_FRONTEND'); ?></p>
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th>
				<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_SELECT'); ?>
			</th>
			<th>
				<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_LANGUAGE'); ?>
			</th>
			<th>
				<?php echo Text::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_TAG'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items->frontend as $i => $lang) : ?>
			<tr>
				<td>
					<input
						id="site-language-cb<?php echo $i; ?>"
						type="radio"
						name="frontendlang"
						value="<?php echo $lang->language; ?>"
						<?php if ($lang->published) echo 'checked="checked"'; ?>
					/>
				</td>
				<td align="center">
					<label for="site-language-cb<?php echo $i; ?>">
						<?php echo $lang->name; ?>
					</label>
				</td>
				<td align="center">
					<?php echo $lang->language; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<div class="row-fluid">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<a
					class="btn"
					href="#"
					onclick="return Joomla.goToPage('languages');"
					rel="prev"
					title="<?php echo Text::_('JPREVIOUS'); ?>">
					<span class="icon-arrow-left"></span>
					<?php echo Text::_('JPREVIOUS'); ?>
				</a>
				<?php // Check if there are languages in the list, if not you cannot move forward ?>
				<?php if ($this->items->administrator) : ?>
					<a
						class="btn btn-primary"
						href="#"
						onclick="Joomla.submitform(document.getElementById('adminForm'));"
						rel="next"
						title="<?php echo Text::_('JNEXT'); ?>">
						<span class="icon-arrow-right icon-white"></span>
						<?php echo Text::_('JNEXT'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php echo HtmlHelper::_('form.token'); ?>
    <input type="hidden" name="task" value="language.setdefault">
    <input type="hidden" name="format" value="json">
</form>
