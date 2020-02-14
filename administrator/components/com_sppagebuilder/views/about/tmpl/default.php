<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();

if(!class_exists('SppagebuilderHelper')) {
	require_once dirname(__DIR__) . '/helpers/sppagebuilder.php';
}

$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );
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
			<div class="sp-pagebuilder-pages sp-pagebuilder-about-view">
				<div class="sppb-about-view-wrap row-fluid">
					<div class="span4 sppb-about-view-left span2">
						<img src="<?php echo JURI::root(); ?>administrator/components/com_sppagebuilder/assets/img/icon.svg" alt="SP Page Builder" />
					</div>
					<div class="sppb-about-view-right span8">
						<div class="sppb-about-view-texts">
							<h2>SP Page Builder Lite <span>Version: <?php echo SppagebuilderHelper::getVersion(); ?></span></h2>
							<p>Trusted by <strong>400,000+</strong> people worldwide, SP Page Builder is an extremely powerful drag &amp; drop design system.<br/> Whether you're a beginner or a professional, you must love taking control over your website design.</p>
							<p>With SP Page Builder, you can build a unique, stunning and functional site without coding a single line.<br/> Using the tool, anyone can build a professional quality site in minutes.</p>
							<div class="sppb-about-view-jed">
								<span class="fa fa-thumbs-o-up"></span>
								<h3><a href="https://extensions.joomla.org/extension/sp-page-builder/" target="_blank" style="color:#fff;">Rate us on JED</a></h3>
								<p>If you found this product useful for you then please rate this product on <a href="https://extensions.joomla.org/extension/sp-page-builder/" target="_blank">Joomla Extension Directory</a>.</p>
							</div>
						</div>
						<div class="sppb-about-view-footer">
							<div class="pagebuilder-links">
								<a class="btn btn-sppb-custom" href="https://www.joomshaper.com/page-builder" target="_blank"><span class="fa fa-shopping-cart"></span> Get Pro now!</a>
								<a class="btn btn-sppb-custom" href="index.php?option=com_sppagebuilder&amp;task=page.add" target="_blank"><span class="fa fa-plus-circle"></span> Create a New Page</a>
								<a class="btn btn-sppb-custom" href="https://www.joomshaper.com/documentation/sp-page-builder/sp-page-builder-3" target="_blank"><span class="fa fa-book"></span> Documentation</a>
								<a class="btn btn-sppb-custom" href="#"><span class="fa fa-play-circle-o"></span> Video Tutorials</a>
								<a class="btn btn-sppb-custom" href="https://www.facebook.com/groups/sppagebuilder/" target="_blank"><span class="fa fa-support"></span> Coumminity FB Group</a>
							</div>
							<div class="pagebuilder-social-links">
								<a href="https://www.facebook.com/joomshaper" target="_blank"><span class="fa fa-facebook-square"></span> Like us on FaceBook</a>
								<a href="https://twitter.com/joomshaper" target="_blank"><span class="fa fa-twitter-square"></span> Follow us on Twitter</a>
								<a href="https://www.youtube.com/user/joomshaper" target="_blank"><span class="fa fa-youtube"></span> Subscribe us on YouTube</a>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
