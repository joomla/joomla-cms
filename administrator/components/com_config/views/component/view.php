<?php
/**
 * @version $Id: component.php 5173 2006-09-25 18:12:39Z Jinx $
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.view' );

/**
 * @package Joomla
 * @subpackage Config
 */
class ConfigComponentView extends JView
{
	/**
	 * Display the view
	 */
	function display()
	{
		// load the component's language file
		$lang = & JFactory::getLanguage();
		$lang->load(JRequest::getVar( 'component' ));

		$model	= &$this->getModel();
		$params	= &$model->getParams();
		$table	= &$model->getTable();

		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Edit Configuration') );
		$document->addStyleSheet('../includes/js/joomla/modal.css');
		JCommonHTML::loadOverlib();
?>
	<form action="index3.php" method="post" name="adminForm" autocomplete="off">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="submitbutton('save');window.top.document.popup.hide();">
					<?php echo JText::_( 'Save' );?></button>
				<button type="button" onclick="window.top.document.popup.hide();">
					<?php echo JText::_( 'Cancel' );?></button>
		    </div>
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_( 'Configuration' );?>
			</legend>
			<?php echo $params->render();?>
		</fieldset>

		<input type="hidden" name="id" value="<?php echo $table->id;?>" />
		<input type="hidden" name="component" value="<?php echo $table->option;?>" />

		<input type="hidden" name="c" value="component" />
		<input type="hidden" name="option" value="com_config" />
		<input type="hidden" name="task" value="" />
	</form>
<?php
	}
}
?>