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
jimport('joomla.application.component.helper');

require_once ( JPATH_COMPONENT .'/parser/addon-parser.php' );
$doc = JFactory::getDocument();
$user = JFactory::getUser();
$app = JFactory::getApplication();
$params = JComponentHelper::getParams('com_sppagebuilder');

if ($params->get('fontawesome',1)) {
	$doc->addStyleSheet(JUri::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css');
}
if (!$params->get('disableanimatecss',0)) {
	$doc->addStyleSheet(JUri::base(true) . '/components/com_sppagebuilder/assets/css/animate.min.css');
}
if (!$params->get('disablecss',0)) {
	$doc->addStyleSheet(JUri::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css');
}
if ($params->get('addcontainer', 1)) {
	$doc->addStyleSheet(JUri::base(true) . '/components/com_sppagebuilder/assets/css/sppagecontainer.css');
}

$doc->addScript(JUri::base(true) . '/components/com_sppagebuilder/assets/js/interaction.js', 'text/javascript', true);

$doc->addScript(JUri::base(true) . '/components/com_sppagebuilder/assets/js/sppagebuilder.js');

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

$page = $this->item;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/builder/classes/addon.php';
$page->text = SpPageBuilderAddonHelper::__($page->text);
$content = json_decode($page->text);

// Add page css
if(isset($page->css) && $page->css) {
	$doc->addStyledeclaration($page->css);
}

$app = JFactory::getApplication();
$input = $app->input;
$Itemid = $input->get('Itemid', 0, 'INT');

$url = JRoute::_('index.php?option=com_sppagebuilder&view=form&layout=edit&tmpl=component&id=' . $page->id . '&Itemid=' . $Itemid);
$root = JURI::base();
$root = new JURI($root);
$link = $root->getScheme() . '://' . $root->getHost() . $url;

?>

<div id="sp-page-builder" class="sp-page-builder <?php echo $menuClassPrefix; ?> page-<?php echo $page->id; ?>">

	<?php
	if($showPageHeading) {
		?>
		<div class="page-header">
			<h1 itemprop="name">
				<?php
				if($menuheading) {
					echo $menuheading;
				} else {
					echo $page->title;
				}
				?>
			</h1>
		</div>
		<?php
	}
	?>

	<div class="page-content">
		<?php $pageName = 'page-' . $page->id; ?>
		<?php echo AddonParser::viewAddons( $content, 0, $pageName ); ?>
		<?php
		$canEdit = $user->authorise('core.edit', 'com_sppagebuilder') || ($user->authorise('core.edit.own', 'com_sppagebuilder') && ($page->created_by == $user->id));
		if ($canEdit) {
			echo '<a class="sp-pagebuilder-page-edit" href="'. $link . '"><i class="fa fa-edit"></i> ' . JText::_('COM_SPPAGEBUILDER_PAGE_EDIT') . '</a>';
		}
		?>
	</div>
</div>
