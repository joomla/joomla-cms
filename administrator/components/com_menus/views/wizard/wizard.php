<?php
/**
* @version $Id: admin.menus.html.php 3593 2006-05-22 15:48:29Z Jinx $
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

jimport('joomla.application.view');

/**
 * @package Joomla
 * @subpackage Menus
 * @static
 * @since 1.5
 */
class JMenuViewWizard extends JView
{

	function display()
	{
		$model =& $this->getModel();
		if (!$model->isStarted()) {
			$this->doStart();
		} else {
			if ($model->isFinished()) {
				$this->doFinished();
			} else {
				$this->doNext();
			}
		}
	}

	function doStart()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle('New Menu Wizard');

		$menuType	= JRequest::getVar( 'menutype' );

		$model		= &$this->getModel();
		$menuTypes 	= $this->get('MenuTypelist');
		$components	= $this->get('ComponentList');
?>
	<style type="text/css">
	._type {
		font-weight: bold;
	}
	</style>
	<form action="index2.php" method="post" name="adminForm">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="this.form.submit();">
					<?php echo JText::_('Next');?></button>
		    </div>
		    Click Next to create the menu item.
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('New Menu Item');?>
			</legend>

			<table class="adminform">
				<tr>
					<td width="20%">
					</td>
					<td valign="top">
						<label for="menutype">
							<?php echo JText::_('Create in Menu');?>
						</label>
						<br/>
						<?php echo mosHTML::selectList( $menuTypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $menuType );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_component" value="component" checked="true" />
						<label for="type_component" class="_type">
							<?php echo JText::_('Component');?>
						</label>
					</td>
					<td valign="top">
						<?php echo JText::_('Link a component to this menu item');?>
						<br/>
						<?php echo mosHTML::selectList( $components, 'component', 'class="inputbox" size="8"', 'option', 'name', $components[0]->option );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_url" value="url" />
						<label for="type_url" class="_type">
							<?php echo JText::_('URL');?>
						</label>
					</td>
					<td valign="top">
						<label for="type_url">
							<?php echo JText::_('URL Address');?>
						</label>
						<br/>
						<input type="text" name="link" size="40" value="http://" />
						<br/>
						<?php echo JText::_('Link another URL to this menu item');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_separator" value="separator" />
						<label for="type_separator" class="_type">
							<?php echo JText::_('Text Label');?>
						</label>
					</td>
					<td valign="top">
						<label for="type_url">
							<?php echo JText::_('Text');?>
						</label>
						<br/>
						<input type="text" name="name" size="40" value="" />
						<br/>
						<?php echo JText::_('This menu item will be just plain text');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_component_item_link" value="component_item_link" />
						<label for="type_component_item_link" class="_type">
							<?php echo JText::_('Menu Item');?>
						</label>
						<br/>
					</td>
					<td valign="top">
						<?php echo JText::_('Link to an existing menu item');?>
						<br/>
						LIST
					</td>
				</tr>
			</table>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="task" value="newwiz" />
		<input type="hidden" name="step" value="1" />
		<input type="hidden" name="tmpl" value="component.html" />
	</form>
<?php
	}

	function doNext()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle('New Menu Wizard');

		$menuType	= JRequest::getVar( 'menutype' );

		$steps = $this->get('steps');
		$step = $this->get('step');
		$nextStep = $step + 1;
		$prevStep = $step - 1;

		$item =& $this->get('item');
	?>
	<style type="text/css">
	._type {
		font-weight: bold;
	}
	</style>
	<form action="index2.php" method="post" name="adminForm">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="document.getElementById('step').value=<?php echo $prevStep;?>;this.form.submit();">
					<?php echo JText::_('Back');?></button>
				<button type="button" onclick="document.getElementById('step').value=<?php echo $nextStep;?>;this.form.submit();">
					<?php echo JText::_('Next');?></button>
		    </div>
		    Click Next to create the menu item.
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('New Menu Item');?>
			</legend>
			<?php $item->render('wizVal'); ?>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="task" value="newwiz" />
		<input type="hidden" id="step" name="step" value="<?php echo $step;?>" />
		<input type="hidden" name="tmpl" value="component.html" />

	</form>
<?php
	}

	function doFinished()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle('New Menu Wizard');

		$menuType	= JRequest::getVar( 'menutype' );

		$model		= &$this->getModel();
		$menuTypes 	= $model->getMenuTypelist();
		$components	= $model->getComponentList();
?>
	<style type="text/css">
	._type {
		font-weight: bold;
	}
	</style>
	<form action="index2.php" method="post" name="adminForm" target="_top">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="this.form.submit();window.top.document.popup.hide();">
					<?php echo JText::_('Next');?></button>
		    </div>
		    Click Next to create the menu item.
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('New Menu Item');?>
			</legend>

			<table class="adminform">
				<tr>
					<td width="20%">
					</td>
					<td valign="top">
						<label for="menutype">
							<?php echo JText::_('Create in Menu');?>
						</label>
						<br/>
						<?php echo mosHTML::selectList( $menuTypes, 'menutype', 'class="inputbox" size="1"', 'menutype', 'title', $menuType );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_component" value="component" checked="true" />
						<label for="type_component" class="_type">
							<?php echo JText::_('Component');?>
						</label>
					</td>
					<td valign="top">
						<?php echo JText::_('Link a component to this menu item');?>
						<br/>
						<?php echo mosHTML::selectList( $components, 'componentid', 'class="inputbox" size="8"', 'id', 'name', $components[0]->id );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_url" value="url" />
						<label for="type_url" class="_type">
							<?php echo JText::_('URL');?>
						</label>
					</td>
					<td valign="top">
						<label for="type_url">
							<?php echo JText::_('URL Address');?>
						</label>
						<br/>
						<input type="text" name="link" size="40" value="http://" />
						<br/>
						<?php echo JText::_('Link another URL to this menu item');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_separator" value="separator" />
						<label for="type_separator" class="_type">
							<?php echo JText::_('Text Label');?>
						</label>
					</td>
					<td valign="top">
						<label for="type_url">
							<?php echo JText::_('Text');?>
						</label>
						<br/>
						<input type="text" name="name" size="40" value="" />
						<br/>
						<?php echo JText::_('This menu item will be just plain text');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_component_item_link" value="component_item_link" />
						<label for="type_component_item_link" class="_type">
							<?php echo JText::_('Menu Item');?>
						</label>
						<br/>
					</td>
					<td valign="top">
						<?php echo JText::_('Link to an existing menu item');?>
						<br/>
						LIST
					</td>
				</tr>
			</table>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="task" value="edit2" />

	</form>
<?php
	}
}
?>
