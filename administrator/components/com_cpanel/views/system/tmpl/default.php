<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<h3>System Configuration</h3>
<div class="card-columns">
	<?php if ($this->links['com_config']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_config']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_config']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_config']['desc']); ?></p>
				<a href="<?php echo $this->links['com_config']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_config']['title']); ?></a>
			</div>
		</div>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['sysinfo']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['sysinfo']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['sysinfo']['desc']); ?></p>
				<a href="<?php echo $this->links['sysinfo']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['sysinfo']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['com_postinstall']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_postinstall']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_postinstall']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_postinstall']['desc']); ?></p>
				<a href="<?php echo $this->links['com_postinstall']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_postinstall']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<hr>

<h3>Maintenance</h3>
<div class="card-columns">
	<?php if ($this->links['com_checkin']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_checkin']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_checkin']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_checkin']['desc']); ?></p>
				<a href="<?php echo $this->links['com_checkin']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_checkin']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['com_cache']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_cache']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_cache']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_cache']['desc']); ?></p>
				<a href="<?php echo $this->links['com_cache']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_cache']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['com_cache_purge']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_cache_purge']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_cache_purge']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_cache_purge']['desc']); ?></p>
				<a href="<?php echo $this->links['com_cache_purge']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_cache_purge']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['database']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['database']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['database']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['database']['desc']); ?></p>
				<a href="<?php echo $this->links['database']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['database']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['warnings']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['warnings']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['warnings']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['warnings']['desc']); ?></p>
				<a href="<?php echo $this->links['warnings']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['warnings']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<hr>

<h3>Plugins</h3>
<div class="card-columns">
	<?php if ($this->links['com_plugins']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_plugins']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_plugins']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_plugins']['desc']); ?></p>
				<a href="<?php echo $this->links['com_plugins']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_plugins']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<hr>

<h3>Templates</h3>
<div class="card-columns">
	<?php if ($this->links['com_templates']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_templates']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_templates']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_templates']['desc']); ?></p>
				<a href="<?php echo $this->links['com_templates']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_templates']['title']); ?></a>
			</div>
		</div>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_templates_styles']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_templates_styles']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_templates_styles']['desc']); ?></p>
				<a href="<?php echo $this->links['com_templates_styles']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_templates_styles']['title']); ?></a>
			</div>
		</div>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_templates_edit']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_templates_edit']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_templates_edit']['desc']); ?></p>
				<a href="<?php echo $this->links['com_templates_edit']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_templates_edit']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<hr>

<h3>Languages</h3>
<div class="card-columns">
	<?php if ($this->links['com_languages']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_languages']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_languages']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_languages']['desc']); ?></p>
				<a href="<?php echo $this->links['com_languages']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_languages']['title']); ?></a>
			</div>
		</div>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_languages_installed']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_languages_installed']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_languages_installed']['desc']); ?></p>
				<a href="<?php echo $this->links['com_languages_installed']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_languages_installed']['title']); ?></a>
			</div>
		</div>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_languages_content']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_languages_content']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_languages_content']['desc']); ?></p>
				<a href="<?php echo $this->links['com_languages_content']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_languages_content']['title']); ?></a>
			</div>
		</div>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_languages_overrides']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_languages_overrides']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_languages_overrides']['desc']); ?></p>
				<a href="<?php echo $this->links['com_languages_overrides']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_languages_overrides']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['com_languages_install']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_languages_install']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_languages_install']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_languages_install']['desc']); ?></p>
				<a href="<?php echo $this->links['com_languages_install']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_languages_install']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<hr>

<h3>Extensions</h3>

<div class="card-columns">
	<?php if ($this->links['com_installer_manage']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_installer_manage']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_installer_manage']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_installer_manage']['desc']); ?></p>
				<a href="<?php echo $this->links['com_installer_manage']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_installer_manage']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['com_installer_discover']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_installer_discover']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_installer_discover']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_installer_discover']['desc']); ?></p>
				<a href="<?php echo $this->links['com_installer_discover']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_installer_discover']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>

<h3>Updates</h3>

<div class="card-columns">
	<?php if ($this->links['com_joomlaupdate']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['com_joomlaupdate']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['com_joomlaupdate']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['com_joomlaupdate']['desc']); ?></p>
				<a href="<?php echo $this->links['com_joomlaupdate']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['com_joomlaupdate']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['extensions_update']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['extensions_update']['label']) . ' extensions'; ?></h4>
				<span class="fa fa-<?php echo $this->links['extensions_update']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['extensions_update']['desc']) . ' extensions'; ?></p>
				<a href="<?php echo $this->links['extensions_update']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['extensions_update']['title']) . ' extensions'; ?></a>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->links['update_sites']['enabled']) : ?>
		<div class="card">
			<div class="card-block">
				<h4 class="card-title"><?php echo JText::_($this->links['update_sites']['label']); ?></h4>
				<span class="fa fa-<?php echo $this->links['update_sites']['icon']; ?> fa-5x"></span>
				<p class="card-text"><?php echo JText::_($this->links['update_sites']['desc']); ?></p>
				<a href="<?php echo $this->links['update_sites']['link']; ?>" class="btn btn-primary"><?php echo JText::_($this->links['update_sites']['title']); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>
