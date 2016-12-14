<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('stylesheet', 'mod_languages/template.css', array('version' => 'auto', 'relative' => true));

if ($params->get('dropdown', 1) && !$params->get('dropdownimage', 0))
{
	JHtml::_('formbehavior.chosen');
}
?>
<div class="mod-languages<?php echo $moduleclass_sfx; ?>"<?php if ($params->get('modulecss_id')) : ?> id="<?php echo $params->get('module_id'); ?>"<?php endif;?>>
<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown', 1) && !$params->get('dropdownimage', 0)) : ?>
	<form name="lang" method="post" action="<?php echo htmlspecialchars(JUri::current(), ENT_COMPAT, 'UTF-8'); ?>">
	<select class="inputbox advancedSelect" onchange="document.location.replace(this.value);" >
	<?php foreach ($list as $language) : ?>
		<option dir=<?php echo $language->rtl ? '"rtl"' : '"ltr"'; ?> value="<?php echo $language->link; ?>" <?php echo $language->active ? 'selected="selected"' : ''; ?>>
		<?php echo $language->title_native; ?></option>
	<?php endforeach; ?>
	</select>
	</form>
<?php elseif ($params->get('dropdown', 1) && $params->get('dropdownimage', 0)) : ?>
	<div class="btn-group">
		<?php foreach ($list as $language) : ?>
			<?php if ($language->active) : ?>
				<a href="#" data-toggle="dropdown" class="btn dropdown-toggle">
					<span class="caret"></span>
					<?php if ($language->image) : ?>
						&nbsp;<?php echo JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
					<?php endif; ?>
					<?php echo $language->title_native; ?>
				</a>
			<?php endif; ?>
		<?php endforeach;?>
		<ul class="<?php echo $params->get('lineheight', 1) ? 'lang-block' : 'lang-inline'; ?> dropdown-menu" dir="<?php echo JFactory::getLanguage()->isRtl() ? 'rtl' : 'ltr'; ?>">
		<?php foreach ($list as $language) : ?>
			<?php if ($params->get('show_active', 0) || !$language->active) : ?>
				<li class="<?php echo $language->active ? 'lang-active' : ''; ?>" >
				<a href="<?php echo $language->link;?>">
					<?php if ($language->image) : ?>
						<?php echo JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
					<?php endif; ?>
					<?php echo $language->title_native; ?>
				</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
	</div>
<?php else : ?>
	<ul class="<?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block'; ?>">
	<?php foreach ($list as $language) : ?>
		<?php if ($params->get('show_active', 0) || !$language->active) : ?>
			<li class="<?php echo $language->active ? 'lang-active' : ''; ?>" dir="<?php echo $language->rtl ? 'rtl' : 'ltr'; ?>">
			<a href="<?php echo $language->link; ?>">
			<?php if ($params->get('image', 1)) : ?>
				<?php if ($language->image) : ?>
					<?php echo JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
				<?php else : ?>
					<span class="label"><?php echo strtoupper($language->sef); ?></span>
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
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
