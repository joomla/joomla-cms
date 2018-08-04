<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('stylesheet', 'mod_languages/template.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('webcomponent', 'system/webcomponents/joomla-language-flag.min.js', ['version' => 'auto', 'relative' => true]);
?>
<div class="mod-languages">
<?php if ($headerText) : ?>
	<div class="mod-languages__pretext pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown', 1) && !$params->get('dropdownimage', 0)) : ?>
	<form name="lang" method="post" action="<?php echo htmlspecialchars(Uri::current(), ENT_COMPAT, 'UTF-8'); ?>">
	<select class="inputbox" onchange="document.location.replace(this.value);" >
	<?php foreach ($list as $language) : ?>
		<option dir=<?php echo $language->rtl ? '"rtl"' : '"ltr"'; ?> value="<?php echo $language->link; ?>" <?php echo $language->active ? 'selected="selected"' : ''; ?>>
		<?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?></option>
	<?php endforeach; ?>
	</select>
	</form>
<?php elseif ($params->get('dropdown', 1) && $params->get('dropdownimage', 0)) : ?>
	<div class="mod-languages__select btn-group">
		<?php foreach ($list as $language) : ?>
			<?php if ($language->active) : ?>
				<a href="#" data-toggle="dropdown" class="btn dropdown-toggle">
					<span class="caret"></span>
					<?php if ($language->image) : ?>
						&nbsp;<joomla-language-flag country="<?php echo substr($language->image, 3); ?>"></joomla-language-flag>
					<?php endif; ?>
					<?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
		<ul class="<?php echo $params->get('lineheight', 1) ? 'lang-block' : 'lang-inline'; ?> dropdown-menu" dir="<?php echo Factory::getLanguage()->isRtl() ? 'rtl' : 'ltr'; ?>">
		<?php foreach ($list as $language) : ?>
			<?php if (!$language->active || $params->get('show_active', 0)) : ?>
				<li<?php echo $language->active ? ' class="lang-active"' : ''; ?>>
				<a href="<?php echo $language->link; ?>">
					<?php if ($language->image) : ?>
						<joomla-language-flag country="<?php echo substr($language->image, 3); ?>"></joomla-language-flag>
					<?php endif; ?>
					<?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
				</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
	</div>
<?php else : ?>
	<ul class="mod-languages__list <?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block'; ?>">
	<?php foreach ($list as $language) : ?>
		<?php if (!$language->active || $params->get('show_active', 0)) : ?>
			<li<?php echo $language->active ? ' class="lang-active"' : ''; ?> dir="<?php echo $language->rtl ? 'rtl' : 'ltr'; ?>">
			<a href="<?php echo $language->link; ?>">
			<?php if ($params->get('image', 1)) : ?>
				<?php if ($language->image) : ?>
					<joomla-language-flag country="<?php echo substr($language->image, 3); ?>"></joomla-language-flag>
				<?php else : ?>
					<span class="badge badge-secondary"><?php echo strtoupper($language->sef); ?></span>
				<?php endif; ?>
			<?php else : ?>
				<?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef); ?>
			<?php endif; ?>
			</a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php if ($footerText) : ?>
	<div class="mod-languages__posttext posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
