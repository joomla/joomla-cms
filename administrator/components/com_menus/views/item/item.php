<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.view');
jimport('joomla.presentation.pane');

/**
 * @package Joomla
 * @subpackage Menus
 * @since 1.5
 */
class JMenuViewItem extends JView
{

	function edit()
	{
		global $mainframe;
		$url 		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$menutype 	= $mainframe->getUserStateFromRequest( "com_menus.menutype", 'menutype', 'mainmenu' );

		$document	= & JFactory::getDocument();
		$document->addScript($url.'includes/js/joomla/popup.js');
		$document->addStyleSheet($url.'includes/js/joomla/popup.css');

		$item		= &$this->get('ItemForEdit');
		$component	= &$this->get('Component');
		$menuTypes 	= $this->get('MenuTypelist');
		$components	= $this->get('ComponentList');
		$control	= $this->get( 'ControlParams' );
		$params		= $this->get( 'StateParams' );
		$advanced	= $this->get( 'AdvancedParams' );
		$details	= $this->get( 'Details' );
		$name		= $this->get( 'StateName' );
		$description	= $this->get( 'StateDescription' );
		$controlFields	= $this->get( 'ControlFields' );

		$pane =& JPane::getInstance('sliders');

		if ($item->id) {
			$document->setTitle('Edit Menu Item');
		} else {
			$document->setTitle('New Menu Item');
		}


		// Build the state list options
		$put[] = mosHTML::makeOption( '0', JText::_( 'No' ));
		$put[] = mosHTML::makeOption( '1', JText::_( 'Yes' ));
		$put[] = mosHTML::makeOption( '-1', JText::_( 'Trash' ));

		// Was showing up null in some cases....
		if (!$item->published) {
			$item->published = 0;
		}
		mosCommonHTML::loadOverlib();
		$disabled = ($item->type != 'url' ? 'disabled="true"' : '');
	?>
	<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		var type = form.type.value;

		if (pressbutton == 'cancel') {
			submitform( pressbutton );
			return;
		}

		if ( (type != "separator") && (trim( form.name.value ) == "") ){
			alert( "<?php echo JText::_( 'Item must have a name', true ); ?>" );
		} else {
			submitform( pressbutton );
		}
	}
	</script>
	<form action="index2.php" method="post" name="adminForm">

		<table class="admintable" width="100%">
			<tr valign="top">
				<td width="60%">

					<fieldset>
						<legend>
							<?php echo JText::_( 'Menu Item Type' ); ?>
						</legend>
						<div style="float:right">
							<button onclick="document.popup.show('index.php?option=com_menus&amp;task=wizard&amp;menutype=<?php echo $menutype;?>&amp;tmpl=component.html&amp;id=<?php echo $item->id; ?>', 700, 500, null);return false;">
								<?php echo JText::_( 'Change Type' ); ?></button>
						</div>
						<h2><?php echo $name; ?></h2>
						<div>
							<?php echo $description; ?>
						</div>
						<table width="100%">
							</tr>
								<td align="right"  colspan="2">
								</td>
							</tr>
							<?php foreach($details as $detail) { ?>
							</tr>
								<td class="key"  width="20%">
									<?php echo $detail['label']; ?>:
								</td>
								<td width="80%">
									<?php echo $detail['name']; ?>
								</td>
							</tr>
							<?php } ?>
						</table>
					</fieldset>

					<fieldset>
						<legend>
							<?php echo JText::_( 'Menu Item Details' ); ?>
						</legend>
						<table width="100%">
							<?php if ($item->id) { ?>
							<tr>
								<td class="key" width="20%" align="right">
									<?php echo JText::_( 'ID' ); ?>:
								</td>
								<td width="80%">
									<strong><?php echo $item->id; ?></strong>
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td class="key" align="right">
									<?php echo JText::_( 'Name' ); ?>:
								</td>
								<td>
									<input class="inputbox" type="text" name="name" size="50" maxlength="255" value="<?php echo $item->name; ?>" />
								</td>
							</tr>
							<tr>
								<td class="key" align="right">
									<?php echo JText::_( 'Link' ); ?>:
								</td>
								<td>
									<input class="inputbox" type="text" name="link" size="50" maxlength="255" value="<?php echo $item->link; ?>" <?php echo $disabled;?> />
								</td>
							</tr>
							<tr>
								<td class="key" align="right">
									<?php echo JText::_( 'Display in' ); ?>:
								</td>
								<td>
									<?php echo mosHTML::selectList( $menuTypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $item->menutype );?>
								</td>
							</tr>
							<tr>
								<td class="key" align="right" valign="top">
									<?php echo JText::_( 'Parent Item' ); ?>:
								</td>
								<td>
									<?php echo JMenuHelper::Parent( $item ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'Published' ); ?>:
								</td>
								<td>
									<?php echo mosHTML::radioList( $put, 'published', '', $item->published ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'Ordering' ); ?>:
								</td>
								<td>
									<?php echo mosAdminMenus::Ordering( $item, $item->id ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'Access Level' ); ?>:
								</td>
								<td>
									<?php echo mosAdminMenus::Access( $item ); ?>
								</td>
							</tr>
							<tr>
								<td class="key" valign="top" align="right">
									<?php echo JText::_( 'On Click, Open in' ); ?>:
								</td>
								<td>
									<?php echo JMenuHelper::Target( $item ); ?>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
				<td width="40%">
					<?php
						$pane->startPane("menu-pane");
						$pane->startPanel( JText::_( 'Menu Item Parameters' ), "param-page" );

						echo $params->render('params');

						$pane->endPanel();
						$pane->startPanel( JText::_( 'Advanced Parameters' ), "advanced-page" );

						echo $advanced->render('params');

						$pane->endPanel();
						$pane->endPane();
					?>
				</td>
			</tr>
		</table>

		<?php
			if (is_array($controlFields)) {
				echo implode("\n", $controlFields);
			}
		?>
		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $item->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $item->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
	</form>
	<?php
	}
}
?>