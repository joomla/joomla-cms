<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );

require_once JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/helpers/integrations.php';
$integrations = SppagebuilderHelperIntegrations::integrations_list();

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');

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

			<div class="sp-pagebuilder-integrations clearfix">
				<ul class="sp-pagebuilder-integrations-list clearfix">
					<?php
					foreach ($integrations as $key => $item) {
						$class = "available";
					?>
						<li class="<?php echo $class; ?>" data-integration="<?php echo $key; ?>">
							<div>
								<div>
									<img src="<?php echo $item->thumb; ?>" alt="<?php echo $item->title; ?>">
									<span>
										<i class="fa fa-check-circle"></i><?php echo $item->title; ?>
										<div class="sp-pagebuilder-btns">
											<a href="https://www.joomshaper.com/page-builder" target="_blank" class="sp-pagebuilder-btn sp-pagebuilder-btn-success sp-pagebuilder-btn-sm sp-pagebuilder-btn-install">Buy Pro</a>
										</div>
									</span>
								</div>
							</div>
						</li>
					<?php
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
