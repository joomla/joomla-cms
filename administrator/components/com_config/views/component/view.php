<?php
/**
 * @version		$Id: component.php 5173 2006-09-25 18:12:39Z Jinx $
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * @package		Joomla
 * @subpackage	Config
 */
class ConfigViewComponent extends JView
{
	/**
	 * Display the view
	 */
	function display()
	{
		$model		= &$this->getModel();
		$params		= &$model->getParams();
		$component	= JComponentHelper::getInfo(JRequest::getVar( 'component' ));

		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Edit Preferences') );
		$document->addStyleSheet('../includes/js/joomla/modal.css');
		jimport('joomla.html.tooltips');
?>
	<form action="index3.php" method="post" name="adminForm" autocomplete="off">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="submitbutton('save');window.setTimeout('window.top.document.popup.hide(null, true)',200);">
					<?php echo JText::_( 'Save' );?></button>
				<button type="button" onclick="window.top.document.popup.hide();">
					<?php echo JText::_( 'Cancel' );?></button>
			</div>
			<div class="configuration" >
				<?php echo $this->component->name ?>
			</div>
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_( 'Configuration' );?>
			</legend>
			<?php echo $params->render();?>
		</fieldset>

		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="component" value="<?php echo $this->component->option;?>" />

		<input type="hidden" name="controller" value="component" />
		<input type="hidden" name="option" value="com_config" />
		<input type="hidden" name="task" value="" />
	</form>
<?php
	}
}