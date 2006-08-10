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

/**
 * @package Joomla
 * @subpackage Menus
 * @static
 * @since 1.5
 */
class JMenuViewWizard extends JWizardView
{

	function doStart()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle(JText::_('New Menu Item Wizard'));

		$app		= &$this->get('Application');
		$type		= $app->getUserStateFromRequest('menuwizard.type', 'type', 'component');
		$menuType	= $app->getUserStateFromRequest('menuwizard.menutype', 'menutype');
		$option		= $app->getUserStateFromRequest('menuwizard.component', 'component', 'com_content');
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
			<?php echo JText::_('Click Next to create the menu item.');?>
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
						<input type="radio" name="type" id="type_component" value="component" <?php echo ($type == 'component')? "checked=\"true\"" : '';?> />
						<label for="type_component" class="_type">
							<?php echo JText::_('Component');?>
						</label>
					</td>
					<td valign="top">
						<?php echo JText::_('Link a component to this menu item');?>
						<br/>
						<?php echo mosHTML::selectList( $components, 'component', 'class="inputbox" size="8"', 'option', 'name', $option );?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_url" value="url" <?php echo ($type == 'url')? "checked=\"true\"" : '';?> />
						<label for="type_url" class="_type">
							<?php echo JText::_('URL');?>
						</label>
					</td>
					<td>
						<?php echo JText::_('Link another URL to this menu item');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_separator" value="separator" <?php echo ($type == 'separator')? "checked=\"true\"" : '';?> />
						<label for="type_separator" class="_type">
							<?php echo JText::_('Separator');?>
						</label>
					</td>
					<td valign="top">
						<?php echo JText::_('This menu item will be just plain text');?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="type" id="type_menulink" value="menulink" <?php echo ($type == 'menulink')? "checked=\"true\"" : '';?> />
						<label for="type_menulink" class="_type">
							<?php echo JText::_('Menu Link');?>
						</label>
						<br/>
					</td>
					<td>
						<?php echo JText::_('Link to an existing menu item');?>
					</td>
				</tr>
			</table>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="task" value="wizard" />
		<input type="hidden" name="step" value="1" />
		<input type="hidden" name="tmpl" value="component.html" />
	</form>
	<?php
		mosHTML::keepAlive();
	}

	function doNext()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');

		$app		= &$this->get('Application');
		$menuType	= $app->getUserStateFromRequest('menuwizard.menutype', 'menutype');

		$steps = $this->get('steps');
		$numSteps = count($steps);
		$step = $this->get('step');
		$stepName = $this->get('stepName');

		$document->setTitle(JText::_('New Menu Item Wizard').' : '.JText::_('Step').' '.$step.' : '.$stepName);
		$nextStep = $step + 1;
		$prevStep = $step - 1;

		$item	=& $this->get('form');
		$msg	= $this->get('message');
	?>
	<style type="text/css">
	._type {
		font-weight: bold;
	}
	</style>
	<form action="index2.php" method="post" name="adminForm">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="history.back();">
					<?php echo JText::_('Back');?></button>
				<button type="button" onclick="document.getElementById('step').value=<?php echo $nextStep;?>;this.form.submit();">
					<?php echo JText::_('Next');?></button>
		    </div>
			<?php echo JText::_( $msg ); ?>
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('New Menu Item');?>
			</legend>
			<?php echo $item->render('wizVal'); ?>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="task" value="wizard" />
		<input type="hidden" id="step" name="step" value="<?php echo $step;?>" />
		<input type="hidden" name="tmpl" value="component.html" />

	</form>
	<?php
		mosHTML::keepAlive();
	}

	function doFinished()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle( JText::_('New Menu Item Confirmation') );

		$menuType	= JRequest::getVar( 'menutype' );

		$steps = $this->get('steps');
		$step = $this->get('step');
		$nextStep = $step + 1;
		$prevStep = $step - 1;

		$item =& $this->get('confirmation');
?>
	<style type="text/css">
	._type {
		font-weight: bold;
	}
	</style>
	<form action="index.php" method="post" name="adminForm" target="_top">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="history.back();">
					<?php echo JText::_('Back');?></button>
				<button type="button" onclick="this.form.submit();window.top.document.popup.hide();">
					<?php echo JText::_('Finish');?></button>
			</div>
			<?php echo JText::_('Click finish to complete the wizard.');?>
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('Menu Item Confirmation');?>
			</legend>
			<?php
//			echo '<pre>';
//			print_r($item);
//			echo '</pre>';
			?>
			<?php echo JText::_('DESCWIZARDFINISHING');?>
		</fieldset>
		<?php
		foreach ($item as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $sk => $sv) {
					echo "\n".'<input type="hidden" name="'.$k.'['.$sk.']" value="'.$sv.'" />';
				}
			} else {
				echo "\n".'<input type="hidden" name="'.$k.'" value="'.$v.'" />';
			}
		}
		?>
		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" name="hidemainmenu" value="1" />
		<input type="hidden" name="task" value="edit" />
	</form>
	<?php
		mosHTML::keepAlive();
	}
}
?>