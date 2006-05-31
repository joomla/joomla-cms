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
 * @subpackage Content
 * @static
 * @since 1.5
 */
class JContentViewWizard extends JView
{
	function &getTemplate( $bodyHtml='', $files=null )
	{
		jimport('joomla.template.helper');
		$tmpl = JTemplateHelper::getInstance( $files );
		$tmpl->setRoot( dirname( __FILE__ ) );
		if ($bodyHtml) {
			$tmpl->setAttribute( 'body', 'src', $bodyHtml );
		}
		return $tmpl;
	}

	function display()
	{
		mosCommonHTML::loadOverlib();
		if (!$this->isStarted()) {
			$this->doStart();
		} else {
			if ($this->isFinished()) {
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
		$document->setTitle(JText::_('Content Tools'));

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$app		= &$this->get('Application');
		$model		= &$this->getModel();
		$items		= $model->getItems( $cid );
		
		$tmpl	= &$this->getTemplate( 'tmpl/dostart.html' );
		$tmpl->displayParsedTemplate( 'body' );
	}

	function doNext()
	{
		$document = &$this->getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');

		$app		= &$this->get('Application');

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
				<button type="button" onclick="document.getElementById('step').value=<?php echo $prevStep;?>;this.form.submit();">
					<?php echo JText::_('Back');?></button>
				<button type="button" onclick="document.getElementById('step').value=<?php echo $nextStep;?>;this.form.submit();">
					<?php echo JText::_('Next');?></button>
		    </div>
			<?php echo $msg; ?>
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('New Menu Item');?>
			</legend>
			<?php echo $item->render('wizVal'); ?>
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
		$document->setTitle('New Menu Item Confirmation');

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
	<form action="index2.php" method="post" name="adminForm" target="_top">
		<fieldset>
			<div style="float: right">
				<button type="button" onclick="history.back();">
					<?php echo JText::_('Back');?></button>
				<button type="button" onclick="this.form.submit();window.top.document.popup.hide();">
					<?php echo JText::_('Finish');?></button>
		    </div>
		    Click Next to create the menu item.
		</fieldset>

		<fieldset>
			<legend>
				<?php echo JText::_('Menu Item Confirmation');?>
			</legend>
			<?php 
//			foreach ($item as $k => $v) {
//				echo "Name: $k &nbsp; Value: $v <br />\n";
//				echo "<input type=\"hidden\" name=\"wizVal[$k]\" value=\"$v\" />\n";
//			}
			echo '<pre>';
			print_r($item);
			echo '</pre>';
			?>
		</fieldset>

		<input type="hidden" name="option" value="com_menus" />
		<input type="hidden" id="step" name="step" value="<?php echo $step;?>" />
		<input type="hidden" name="task" value="edit" />

	</form>
<?php
	}

	function isStarted()
	{
		return ($this->get('step'));
	}

	function isFinished()
	{
		$steps = $this->get('steps');
		return (count($steps) <= $this->get('step') - 1);
	}
}
?>
