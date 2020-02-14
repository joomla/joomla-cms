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
JHtml::_('jquery.ui', array('core', 'sortable'));
JHtml::_('formbehavior.chosen', 'select');

require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/base.php';
require_once JPATH_COMPONENT_ADMINISTRATOR .'/builder/classes/config.php';

if(!class_exists('SppagebuilderHelper')) {
	require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/sppagebuilder.php';
}

if(!class_exists('SppagebuilderHelperSite')) {
	require_once JPATH_ROOT . '/components/com_sppagebuilder/helpers/helper.php';
}

$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$params = JComponentHelper::getParams('com_sppagebuilder');

$doc->addStylesheet( JURI::base(true) . '/administrator/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/font-awesome.min.css');
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/sppagebuilder.css');
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/react-select.css');
$doc->addStyleSheet(JUri::base(true).'/components/com_sppagebuilder/assets/css/edit.css');

if ($params->get('addcontainer', 1)) {
	$doc->addStyleSheet(JUri::base(true) . '/components/com_sppagebuilder/assets/css/sppagecontainer.css');
}

$doc->addScriptdeclaration('var pagebuilder_base="' . JURI::root() . '";');
$doc->addScript( JUri::base(true).'/components/com_sppagebuilder/assets/js/edit.js' );
$doc->addScript( JURI::root(true) . '/media/editors/tinymce/tinymce.min.js' );
$doc->addScript( JUri::base(true). '/components/com_sppagebuilder/assets/js/actions.js' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.vide.js' );

if($this->item->extension == 'com_content' && $this->item->extension_view == 'article'){
	$extension_view = 'article';
} else {
	$extension_view = 'page';
}

$menus = $app->getMenu();
$menu = $menus->getActive();
$menuClassPrefix = '';
$showPageHeading = 0;

// check active menu item
if ($menu) {
	$menuClassPrefix 	= $menu->params->get('pageclass_sfx');
	$showPageHeading 	= $menu->params->get('show_page_heading');
	$menuheading 		= $menu->params->get('page_heading');
}

require_once JPATH_COMPONENT_ADMINISTRATOR . '/builder/classes/addon.php';
$this->item->text = SpPageBuilderAddonHelper::__($this->item->text, true);

SpPgaeBuilderBase::loadAddons();

$fa_icon_list     = SpPgaeBuilderBase::getIconList(); // Icon List
$animateNames     = SpPgaeBuilderBase::getAnimationsList(); // Animation Names
$accessLevels     = SpPgaeBuilderBase::getAccessLevelList(); // Access Levels
$article_cats     = SpPgaeBuilderBase::getArticleCategories(); // Article Categories
$moduleAttr       = SpPgaeBuilderBase::getModuleAttributes(); // Module Postions and Module Lits
$rowSettings      = SpPgaeBuilderBase::getRowGlobalSettings(); // Row Settings Attributes
$columnSettings   = SpPgaeBuilderBase::getColumnGlobalSettings(); // Column Settings Attributes
$global_attributes = SpPgaeBuilderBase::addonOptions();

$addons_list = SpAddonsConfig::$addons;

usort($addons_list, function($a){
	if (isset($a['pro']) && $a['pro']) {
		return 1;
	}
});

$newAddonList = array();

$templates = array('module', 'divider', 'text_block', 'testimonial', 'tab', 'image', 'icon', 'accordion', 'feature', 'empty_space', 'video', 'carousel', 'button', 'alert');

foreach ($addons_list as $addon) {
	$default_value = SpPgaeBuilderBase::getSettingsDefaultValue($addon['attr']);
	$addon['default'] = $default_value['default'];
	if(isset($default_value['attr'])){
		$addon['attr'] = $default_value['attr'];
	}
	$addon['visibility'] = true;

	if(!isset($addon['category']) || empty($addon['category'])){
		$addon['category'] = 'General';
	}

	$addon_name = preg_replace('/^sp_/i', '', $addon['addon_name']);
	$class_name = 'SppagebuilderAddon' . ucfirst($addon_name);
	if(in_array($addon_name, $templates) && method_exists($class_name, 'getTemplate')){
		$addon['js_template'] = true;
	}

	$newAddonList[$addon_name] = $addon;
}

unset($addon);

$row_default_value = SpPgaeBuilderBase::getSettingsDefaultValue($rowSettings['attr']);
$rowSettings['default'] = $row_default_value;

$column_default_value = SpPgaeBuilderBase::getSettingsDefaultValue($columnSettings['attr']);
$columnSettings['default'] = $column_default_value;


SpPgaeBuilderBase::loadAssets($newAddonList);
$addon_cats = SpPgaeBuilderBase::getAddonCategories($newAddonList);
$doc->addScriptdeclaration('var addonsJSON=' . json_encode($newAddonList) . ';');
$doc->addScriptdeclaration('var addonCats=' . json_encode($addon_cats) . ';');

// Global Attributes
$doc->addScriptdeclaration('var globalAttr=' . json_encode( $global_attributes ) . ';');
$doc->addScriptdeclaration('var faIconList=' . json_encode( $fa_icon_list ) . ';');
$doc->addScriptdeclaration('var animateNames=' . json_encode( $animateNames ) . ';');
$doc->addScriptdeclaration('var accessLevels=' . json_encode( $accessLevels ) . ';');
$doc->addScriptdeclaration('var articleCats=' . json_encode( $article_cats ) . ';');
$doc->addScriptdeclaration('var moduleAttr=' . json_encode( $moduleAttr ) . ';');
$doc->addScriptdeclaration('var rowSettings=' . json_encode( $rowSettings ) . ';');
$doc->addScriptdeclaration('var colSettings=' . json_encode( $columnSettings ) . ';');
$doc->addScriptdeclaration('var sppbVersion="' . SppagebuilderHelper::getVersion() . '";');
$doc->addScriptdeclaration('var sppbMediaPath=\'/images\';');
$doc->addScriptdeclaration('var extensionView=\'' . $extension_view . '\';');


if (!$this->item->text) {
	$doc->addScriptdeclaration('var initialState=[];');
} else {
	$doc->addScriptdeclaration('var initialState=' . $this->item->text . ';');
}

$conf   = JFactory::getConfig();
$editor   = $conf->get('editor');

if ($editor == 'jce') {
	require_once(JPATH_ADMINISTRATOR . '/components/com_jce/includes/base.php');
	wfimport('admin.models.editor');
  $editor = new WFModelEditor();

  $settings = $editor->getEditorSettings();

  $app->triggerEvent('onBeforeWfEditorRender', array(&$settings));
	echo $editor->render($settings);
}
?>

<div id="sp-page-builder" class="sp-pagebuilder <?php echo $menuClassPrefix; ?> page-<?php echo $this->item->id; ?>" data-pageid="<?php echo $this->item->id; ?>">
	<form action="<?php echo JRoute::_('index.php?option=com_sppagebuilder&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate page-builder-form" style="display: none;">
		<div id="page-options">
			<?php $fieldsets = $this->form->getFieldsets(); ?>
			
			<div class="sp-pagebuilder-form-group-toggler active">
				<span>Basic <span class="fa fa-chevron-right"></span></span>
				<div>
					<?php foreach ($this->form->getFieldset('basic') as $key => $field) : ?>
						<div class="sp-pagebuilder-form-group">
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
							<?php if($field->getAttribute('desc')) : ?>
								<span class="sp-pagebuilder-form-help"><?php echo JText::_($field->getAttribute('desc')); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if($extension_view == 'page'): ?>
				<div class="sp-pagebuilder-form-group-toggler">
					<span>SEO <span class="fa fa-chevron-right"></span></span>
					<div>
						<?php foreach ($this->form->getFieldset('seo') as $key => $field) : ?>
							<div class="sp-pagebuilder-form-group">
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
								<?php if($field->getAttribute('desc')) : ?>
									<span class="sp-pagebuilder-form-help"><?php echo JText::_($field->getAttribute('desc')); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if($extension_view == 'page'): ?>
				<div class="sp-pagebuilder-form-group-toggler">
					<span>Page CSS <span class="fa fa-chevron-right"></span></span>
					<div>
						<?php foreach ($this->form->getFieldset('pagecss') as $key => $field) : ?>
							<div class="sp-pagebuilder-form-group">
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
								<?php if($field->getAttribute('desc')) : ?>
									<span class="sp-pagebuilder-form-help"><?php echo JText::_($field->getAttribute('desc')); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
        </div>

        <input type="hidden" id="form_task" name="task" value="page.apply" />
        <?php
            $app = JFactory::getApplication();
            $input = $app->input;
            $Itemid = $input->get('Itemid', 0, 'INT');

            $url = JRoute::_('index.php?option=com_sppagebuilder&view=page&id=' . $this->item->id . '&Itemid=' . $Itemid);
            $root = JURI::base();
            $root = new JURI($root);
            $pageUrl = $root->getScheme() . '://' . $root->getHost() . $url;
        ?>
        <input type="hidden" id="return_page" name="return_page" value="<?php echo base64_encode($pageUrl); ?>" />
        <?php echo JHtml::_('form.token'); ?>
	</form>

	<div id="sp-pagebuilder-container">
		<div class="sp-pagebuilder-loading-wrapper">
			<div class="sp-pagebuilder-loading">
				<i class="pbfont pbfont-pagebuilder"></i>
			</div>
		</div>
	</div>
	<?php
		$lang = '';
		if (isset($this->item->language) && $this->item->language != '*') {
			$lang_array = explode('-',$this->item->language);
			$lang = '&lang='.$lang_array[0];
		}

		$iframe_url = JURI::root(true) . '/index.php?option=com_sppagebuilder&amp;view=form&amp;id=' . $this->item->id .'&amp;layout=edit-iframe&amp;Itemid='.$Itemid.$lang;
		$iframe_sef_url = JRoute::_($iframe_url);
	?>
	<iframe name="sp-pagebuilder-view" id="sp-pagebuilder-view" data-url="<?php echo $iframe_sef_url; ?>"></iframe>
	<div id="sp-pagebuilder-page-tools" class="sp-pagebuilder-page-tools"></div>
</div>
<div class="sp-pagebuilder-media-modal-overlay" style="display:none">
  <div id="sp-pagebuilder-media-modal">
  </div>
</div>

<?php
foreach ($newAddonList as $addon) {
	$addon_name = $addon['addon_name'];
	$addon_name = preg_replace('/^sp_/i', '', $addon['addon_name']);
  $class_name = 'SppagebuilderAddon' . ucfirst($addon_name);
	if(in_array($addon_name, $templates) && method_exists($class_name, 'getTemplate')){
		?>
		<script id="sppb-tmpl-addon-<?php echo $addon_name; ?>" type="x-tmpl-lodash">
		<#
			var addonClass = 'clearfix';
			var addonAttr = '';
			var addonId = 'sppb-addon-'+data.id;


			var textColor = data.global_text_color || '';
			var linkColor = data.global_link_color || '';
			var linkHoverColor = data.global_link_hover_color || '';
			var backgroundRepeat = data.global_background_repeat || '';
			var backgroundSize = data.global_background_size || '';
			var backgroundAttachment = data.global_background_attachment || '';
			var modern_font_style = false;
			var title_font_style = data.title_fontstyle || "";

			var backgroundColor = '';
			if(data.global_use_background && data.global_background_color){
				backgroundColor = data.global_background_color;
			}

			var backgroundImage = '';
			if(data.global_use_background && data.global_background_image  && (data.global_background_image.indexOf('http://') != -1 || data.global_background_image.indexOf('https://') != -1)){
				backgroundImage = 'url('+data.global_background_image+')';
			} else if(data.global_use_background && data.global_background_image){
				backgroundImage = 'url('+pagebuilder_base+data.global_background_image+')';
			}

			let borderWidth = '';
			let borderWidth_sm = '';
			let borderWidth_xs = '';
			if(data.global_user_border && _.isObject(data.global_border_width)){
				borderWidth = data.global_border_width.md+'px';
				borderWidth_sm = data.global_border_width.sm+'px';
				borderWidth_xs = data.global_border_width.xs+'px';
			} else {
				borderWidth = data.global_border_width+'px';
			}

			let borderColor = '';
			if(data.global_user_border && data.global_border_color){
				borderColor = data.global_border_color;
			}

			let borderStyle = '';
			if(data.global_user_border && data.global_boder_style){
				borderStyle = data.global_boder_style;
			}

			let borderRadius = data.global_border_radius || '';

			var margin = window.getMarginPadding(data.global_margin, 'margin');
			var padding = window.getMarginPadding(data.global_padding, 'padding');

			if(data.global_use_animation && data.global_animation){
				addonClass += ' sppb-wow '+data.global_animation;

				if(data.global_animationduration){
					addonAttr = ' data-sppb-wow-duration="'+data.global_animationduration+'ms"';
				}

				if(data.global_animationdelay){
					addonAttr = ' data-sppb-wow-delay="'+data.global_animationdelay+'ms"';
				}
			}

			var boxShadow = '';
			if(_.isObject(data.global_boxshadow)){
				let ho = data.global_boxshadow.ho || 0,
					vo = data.global_boxshadow.vo || 0,
					blur = data.global_boxshadow.blur || 0,
					spread = data.global_boxshadow.spread || 0,
					color = data.global_boxshadow.color || 0;
				boxShadow = ho+'px '+vo+'px '+blur+'px '+spread+'px '+color;
			} else {
				boxShadow = data.global_boxshadow || '';
			}
		#>
		<div id="{{ addonId }}" class="{{ addonClass }}" {{{ addonAttr }}} >
			<# if(data.global_use_overlay){ #>
				<div class="sppb-addon-overlayer"></div>
			<# } #>
			<style type="text/css">
				#{{ addonId }}{
					color: {{ textColor }};
					background-color: {{ backgroundColor }};
					background-image: {{ backgroundImage }};
					background-repeat: {{ backgroundRepeat }};
					background-size: {{ backgroundSize }};
					background-attachment: {{ backgroundAttachment }};
					<# if(_.isObject(borderRadius)){ #>
						border-radius: {{ borderRadius.md }}px;
					<# } else { #>
						border-radius: {{ borderRadius }}px;
					<# } #>
					<# if(_.isObject(margin)){ #>
						{{{ margin.md }}}
					<# } else { #>
						{{{ margin }}}
					<# } #>

					<# if(_.isObject(padding)){ #>
						{{{ padding.md }}}
					<# } else { #>
						{{{ padding }}}
					<# } #>
					border-width: {{ borderWidth }};
					border-color: {{ borderColor }};
					border-style: {{ borderStyle }};
					box-shadow: {{ boxShadow }};
					<# if(typeof data.use_global_width !== "undefined" && data.use_global_width && typeof data.global_width !== "undefined" && _.isObject(data.global_width)) { #>
						width: {{data.global_width.md}}%;
					<# } #>
					<# if(data.global_use_overlay){ #>
						position: relative;
						overflow: hidden;
					<# } #>
				}
				<# if(data.global_use_overlay){ #>
					#{{ addonId }} .sppb-addon-overlayer{
						background-color: {{ data.global_background_overlay }};
						position: absolute;
				    top: 0;
				    left: 0;
				    width: 100%;
				    height: 100%;
				    z-index: 0;
					}

					#{{ addonId }} > .sppb-addon{
						position: relative;
					}
				<# } #>
				#{{ addonId }} a{
					color: {{ linkColor }};
				}
				#{{ addonId }} a:hover,
				#{{ addonId }} a:focus,
				#{{ addonId }} a:active{
					color: {{ linkHoverColor }};
				}
				<# if (!_.isEmpty(data.title)){ #>
					#sppb-addon-{{ data.id }} .sppb-addon-title{
						<# if(_.isObject(data.title_fontsize)){ #>
							font-size: {{ data.title_fontsize.md }}px;
							line-height: {{ data.title_fontsize.md }}px;
						<# } else { #>
							font-size: {{ data.title_fontsize }}px;
							line-height: {{ data.title_fontsize }}px;
						<# } #>
						<# if(_.isObject(data.title_lineheight)){ #>
							line-height: {{ data.title_lineheight.md }}px;
						<# } else { #>
							line-height: {{ data.title_lineheight }}px;
						<# } #>
						letter-spacing: {{ data.title_letterspace }};
						font-weight: {{ data.title_fontweight }};
						color: {{ data.title_text_color }};
						<# if(_.isObject(data.title_margin_top)){ #>
							margin-top: {{ data.title_margin_top.md }}px;
						<# } else { #>
							margin-top: {{ data.title_margin_top }}px;
						<# } #>
						<# if(_.isObject(data.title_margin_bottom)){ #>
							margin-bottom: {{ data.title_margin_bottom.md }}px;
						<# } else { #>
							margin-bottom: {{ data.title_margin_bottom }}px;
						<# } #>

						<# if(_.isObject(data.title_font_style) && data.title_font_style.underline) { #>
							text-decoration: underline;
							<# modern_font_style = true #>
						<# } #>

						<# if(_.isObject(data.title_font_style) && data.title_font_style.italic) { #>
							font-style: italic;
							<# modern_font_style = true #>
						<# } #>

						<# if(_.isObject(data.title_font_style) && data.title_font_style.uppercase) { #>
							text-transform: uppercase;
							<# modern_font_style = true #>
						<# } #>

						<# if(_.isObject(data.title_font_style) && data.title_font_style.weight) { #>
							font-weight: {{ data.title_font_style.weight }};
							<# modern_font_style = true #>
						<# } #>

						<# if(!modern_font_style) { #>
							<# if(_.isArray(title_font_style)) { #>
								<# if(title_font_style.indexOf("underline") !== -1){ #>
									text-decoration: underline;
								<# } #>
								<# if(title_font_style.indexOf("uppercase") !== -1){ #>
									text-transform: uppercase;
								<# } #>
								<# if(title_font_style.indexOf("italic") !== -1){ #>
									font-style: italic;
								<# } #>
								<# if(title_font_style.indexOf("lighter") !== -1){ #>
									font-weight: lighter;
								<# } else if(title_font_style.indexOf("normal") !== -1){#>
									font-weight: normal;
								<# } else if(title_font_style.indexOf("bold") !== -1){#>
									font-weight: bold;
								<# } else if(title_font_style.indexOf("bolder") !== -1){#>
									font-weight: bolder;
								<# } #>
							<# } #>
						<# } #>

					}
				<# } #>

				@media (min-width: 768px) and (max-width: 991px) {
					#{{ addonId }}{
						<# if(_.isObject(borderRadius)){ #>
							border-radius: {{ borderRadius.sm }}px;
						<# } #>
						<# if(_.isObject(margin)){ #>
							{{{ margin.sm }}}
						<# } #>
						<# if(_.isObject(padding)){ #>
							{{{ padding.sm }}}
						<# } #>
						border-width: {{ borderWidth_sm }};
						<# if(typeof data.use_global_width !== "undefined" && data.use_global_width && typeof data.global_width !== "undefined" && _.isObject(data.global_width)) { #>
							width: {{data.global_width.sm}}%;
						<# } #>
					}
					<# if (!_.isEmpty(data.title)){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-title{
							<# if(_.isObject(data.title_fontsize)){ #>
								font-size: {{ data.title_fontsize.sm }}px;
								line-height: {{ data.title_fontsize.sm }}px;
							<# } #>
							<# if(_.isObject(data.title_lineheight)){ #>
								line-height: {{ data.title_lineheight.sm }}px;
							<# } else { #>
								line-height: {{ data.title_lineheight }}px;
							<# } #>
							<# if(_.isObject(data.title_margin_top)){ #>
								margin-top: {{ data.title_margin_top.sm }}px;
							<# } #>
							<# if(_.isObject(data.title_margin_bottom)){ #>
								margin-bottom: {{ data.title_margin_bottom.sm }}px;
							<# } #>
						}
					<# } #>
				}
				@media (max-width: 767px) {
					#{{ addonId }}{
						border-width: {{ borderWidth_xs }};
						<# if(_.isObject(borderRadius)){ #>
							border-radius: {{ borderRadius.xs }}px;
						<# } #>
						<# if(_.isObject(margin)){ #>
							{{{ margin.xs }}}
						<# } #>
						<# if(_.isObject(padding)){ #>
							{{{ padding.xs }}}
						<# } #>
						<# if(typeof data.use_global_width !== "undefined" && data.use_global_width && typeof data.global_width !== "undefined" && _.isObject(data.global_width)) { #>
							width: {{data.global_width.xs}}%;
						<# } #>
					}

					<# if (!_.isEmpty(data.title)){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-title{
							<# if(_.isObject(data.title_fontsize)){ #>
								font-size: {{ data.title_fontsize.xs }}px;
								line-height: {{ data.title_fontsize.xs }}px;
							<# } #>
							<# if(_.isObject(data.title_lineheight)){ #>
								line-height: {{ data.title_lineheight.xs }}px;
							<# } else { #>
								line-height: {{ data.title_lineheight }}px;
							<# } #>
							<# if(_.isObject(data.title_margin_top)){ #>
								margin-top: {{ data.title_margin_top.xs }}px;
							<# } #>
							<# if(_.isObject(data.title_margin_bottom)){ #>
								margin-bottom: {{ data.title_margin_bottom.xs }}px;
							<# } #>
						}
					<# } #>
				}
			</style>
			<?php echo $class_name::getTemplate(); ?>
		</div>
		</script>
		<?php
	}
}
?>


<script type="text/javascript" src="<?php echo JURI::base(true) . '/components/com_sppagebuilder/assets/js/engine.js'; ?>"></script>
