<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );

require_once JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/helpers/languages.php';
$languages = SppagebuilderHelperLanguages::language_list();

?>

<div class="sp-pagebuilder-admin-top"></div>

<div class="sp-pagebuilder-admin clearfix" style="position: relative;">
	<div id="j-sidebar-container" class="span2">
		<?php echo JLayoutHelper::render('brand'); ?>
		<?php echo $this->sidebar; ?>
	</div>

	<div id="j-main-container" class="span10">
		<div class="sp-pagebuilder-main-container-inner">
			<div class="sp-pagebuilder-pages-toolbar clearfix"></div>
			<div class="sp-pagebuilder-pages top-notice-bar">
				<div class="row-fluid">
					<div class="span12">
						<div class="sppb-upgrade-pro">
							<div class="sppb-upgrade-pro-icon pull-left">
								<img src="<?php echo JURI::root(true) . '/administrator/components/com_sppagebuilder/assets/img/notice-alert.png'; ?>" alt="Notice">
							</div>
							<div class="sppp-upgrade-pro-text pull-left">
								<h4>Get SP Page Builder Pro to unlock the best experience ever</h4>
								<p>SP Page Builder Pro offers live frontend editing, 45+ addons, 90+ ready Sections, 25+ readymade templates, premium support, and more. <a href="https://www.joomshaper.com/page-builder" target="_blank"><strong>Get SP Page Builder Pro now!</strong></a></p>
							</div>
							<a href="#" class="pull-right"><img alt="Close Icon" src="<?php echo JURI::root(true) . '/administrator/components/com_sppagebuilder/assets/img/close-icon.png'; ?>"></a>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>

			<?php if(isset($messages) && count((array) $messages)) { ?>
				<div class="sp-pagebuilder-message-container">
					<?php foreach ($messages as $key => $message) { ?>
						<div class="alert alert-<?php echo str_replace(array('message', 'error', 'notice'), array('success', 'danger', 'info'), $message['type']); ?>">
							<button type="button" class="close" data-dismiss="alert">&times;</button>
							<h4 class="alert-heading"><?php echo ucfirst($message['type']); ?></h4>
							<div class="alert-message"><?php echo $message['message']; ?></div>
						</div>
						<?php
					} ?>
				</div>
			<?php } ?>

			<div class="sp-pagebuilder-pages sp-pagebuilder-languages">
				<table  class="table table-striped sp-pagebuilder-language-list" id="pageList">
					<thead>
						<tr>
							<th width="5%" class="center">
								#
							</th>
							<th width="50%">
								<?php echo JText::_('COM_SPPAGEBUILDER_FIELD_LANGUAGE'); ?>
							</th>
							<th width="12%" class="hidden-phone center">
								<?php echo JText::_('COM_SPPAGEBUILDER_FIELD_LANGUAGE_TAG'); ?>
							</th>
							<th width="8%" class="hidden-phone center">
								Image
							</th>
							<th width="10%" class="hidden-phone center">
								<?php echo JText::_('COM_SPPAGEBUILDER_FIELD_INSTALLED'); ?>
							</th>
							<th width="20%" class="hidden-phone center">
								<?php echo JText::_('COM_SPPAGEBUILDER_FIELD_ACTION'); ?>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php $item_no = 1;
						$newLang = (array)$languages;
						ksort($newLang);
						$languages = (object)$newLang;

						foreach ( $languages as $key => $language ) {
							$class = "available";
							$installed_version = 'NOT INSTALLED';
							$update_class = '';
							$update_status = '';

							if(count((array) $this->items)) {
								foreach ($this->items as $this->item) {
									if($this->item->lang_key == $key) {
										if($this->item->state == 0) {
											$class = "installed";
										} else if ($this->item->state == 1) {
											$class = "enabled";
										}

										$installed_version = $this->item->version;
										if ($language->version > $this->item->version) {
											$update_class = 'label-warning';
											$update_status = 'available';
										} else {
											$update_class = 'label-success';
											$update_status= 'updated';
										}
									}
								}
							} ?>
							<tr class="available" data-language="<?php echo $key; ?>">
								<td class="center">
									<?php echo $item_no; ?>
								</td>
								<td>
										<p>
											<span class="language-title">
												<?php echo $this->escape($language->title); ?>
											</span>
										</p>
								</td>
								<td class="center">
									<?php echo $language->lang_tag; ?>
								</td>
								<td class="center">
									<img src="<?php echo  JURI::root(true) . '/media/mod_languages/images/' . strtolower(str_ireplace('-', '_', $language->lang_tag)); ?>.gif" alt="<?php echo $language->lang_tag; ?>" title="<?php echo $language->lang_tag; ?>">
								</td>
								<td class="center installed-version">
									<span class="label <?php echo $update_class; ?>"><?php echo $installed_version; ?></span>
								</td>
								<td class="center">
									<div class="sp-pagebuilder-lang-btns">
										<a class="sp-pagebuilder-btn sp-pagebuilder-btn-success sp-pagebuilder-btn-sm sp-pagebuilder-btn-install" href="https://www.joomshaper.com/page-builder" target="_blank" style="color: #fff; margin: 5px 0;">Available in Pro</a>
									</div>
								</td>
							</tr>
						<?php $item_no++; } ?>
					</tbody>
				</table>
			</div>

		</div>
	</div>
</div>
