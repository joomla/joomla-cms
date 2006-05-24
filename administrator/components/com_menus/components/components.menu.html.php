<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Writes the edit form for new and existing content item
*
* A new record is defined when <var>$row</var> is passed with the <var>id</var>
* property set to 0.
* @package Joomla
* @subpackage Menus
*/
class components_menu_html {

	/**
	 * @param object A menu object
	 * @param array A list of components
	 * @param array An array of lists
	 * @param string The URI option
	 */
	function edit( &$menu, &$component, &$components, &$lists, $option )
	{
		$helper	= JMenuHelper::getInstance( $component->option );

		$params	= $helper->getParams( $menu->params, $component->option );

		$mvcrt	= $helper->getParams( $menu->mvcrt, null, dirname( __FILE__ ) . '/mvcrt.xml' );
		$mvcrt->addParameterDir( dirname( __FILE__ ) . '/parameters' );
		$mvcrt->private_helper = $helper;

		mosCommonHTML::loadOverlib();

		if ( $menu->id ) {
			$title = '[ '. $lists['componentname'] .' ]';
		} else {
			$title = '';
		}
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			var comp_links = new Array;
			<?php
			foreach ($components as $row) {
				?>
				comp_links[ <?php echo $row->value;?> ] = 'index.php?<?php echo addslashes( $row->link );?>';
				<?php
			}
			?>
			if ( form.id.value == 0 ) {
				var comp_id = getSelectedValue( 'adminForm', 'componentid' );
				form.link.value = comp_links[comp_id];
			} else {
				form.link.value = comp_links[form.componentid.value];
			}

			if ( trim( form.name.value ) == "" ){
				alert( "<?php echo JText::_( 'Item must have a name', true ); ?>" );
			} else if (form.componentid.value == ""){
				alert( "<?php echo JText::_( 'Please select a Component', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">

		<table width="100%">
			<tr valign="top">
				<td width="60%">
					<table class="adminform">
					<?php menuHTML::MenuOutputTop( $lists, $menu, 'Component' ); ?>
						<tr>
							<td valign="top" align="right">
								<?php echo JText::_( 'Component' ); ?>:
							</td>
							<td>
								<?php echo $lists['componentid']; ?>
							</td>
						</tr>
					<?php menuHTML::MenuOutputBottom( $lists, $menu ); ?>
					</table>
				</td>
				<td width="40%">
				<?php
					menuHTML::MenuOutputParams( $params, $menu, 1 );

					if ($helper->hasMVCRT()) {
				?>
					<fieldset>
						<legend>
							<?php echo JText::_( 'MVCRT' ); ?>
						</legend>
					<?php
						echo $mvcrt->render( 'mvcrt' );
					?>
					</fieldset>
				<?php
						if ($helper->hasControllers())
						{ ?>
					<fieldset>
						<legend>
							<?php echo JText::_( 'Controller Parameters' ); ?>
						</legend>
					<?php
						$params = $helper->getContollerParams( $mvcrt->get( 'controller_name' ), $menu->params );
						echo $params->render();
					?>
					</fieldset>
			<?php		}

						if ($helper->hasViews())
						{ ?>
					<fieldset>
						<legend>
							<?php echo JText::_( 'View Parameters' ); ?>
						</legend>
					<?php
						$params = $helper->getViewParams( $mvcrt->get( 'view_name' ), $menu->params );
						echo $params->render();
					?>
					</fieldset>
			<?php		}
					}
				?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="link" value="" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}
}
?>