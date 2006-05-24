<?php
/**
 * @version $Id: admin.config.php 3566 2006-05-20 14:57:33Z stingrey $
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.view' );

/**
 * @package Joomla
 * @subpackage Config
 */
class JConfigComponentEditView extends JView
{
	/**
	 * Display the view
	 */
	function display()
	{
		$model	= &$this->getModel();
		$params	= &$model->getParams();
		$table	= &$model->getTable();
		$document = &$this->getDocument();
		$document->setTitle( 'Edit Configuration' );
?>
	<form action="index3.php" method="post" name="adminForm">
		<div>
			<div style="float: right">
				<button type="button" onclick="submitbutton('save');window.top.document.popup.hide();">
					<?php echo JText::_( 'Save' );?></button>
				<button type="button" onclick="window.top.document.popup.hide();">
					<?php echo JText::_( 'Cancel' );?></button>
		    </div>
		</div>
		<div class="clr"></div>

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