<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
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

jimport('joomla.application.component.view');

/**
 * @package Joomla
 * @subpackage Content
 * @static
 * @since 1.5
 */
class JContentViewWizard extends JWizardView
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

	function doStart()
	{
		$document = & JFactory::getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle(JText::_('Content Tools'));

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$model		= &$this->getModel();
		$items		= $model->getItems( $cid );

		$tmpl	= &$this->getTemplate( 'tmpl/dostart.html' );
		$tmpl->displayParsedTemplate( 'body' );
	}

	function doNext()
	{
		global $mainfame;

		$document = & JFactory::getDocument();
		$document->addStyleSheet('components/com_menumanager/includes/popup.css');

		$steps		= $this->get('steps');
		$numSteps	= count($steps);
		$step		= $this->get('step');
		$stepName	= $this->get('stepName');
		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$item		=& $this->get('form');

		$document->setTitle(JText::_('Content Tools Wizard').' : '.JText::_('Step').' '.$step.' : '.$stepName);

		$tmpl	= &$this->getTemplate( 'tmpl/donext.html' );

		$tmpl->addVar( 'body', 'prevstep',	$step - 1 );
		$tmpl->addVar( 'body', 'step',		$step );
		$tmpl->addVar( 'body', 'nextstep',	$step + 1 );
		$tmpl->addVar( 'body', 'params',	$item->render('wizVal') );
		$tmpl->addVar( 'body', 'message',	$this->get('message') );
		$tmpl->addVar( 'cid', 'id',			$cid );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function doFinished()
	{
		$document = & JFactory::getDocument();

		$document->addStyleSheet('components/com_menumanager/includes/popup.css');
		$document->setTitle(JText::_('Content Tools Wizard').' : '.JText::_('Finished'));

		$steps	= $this->get('steps');
		$step	= $this->get('step');

		$tmpl	= &$this->getTemplate( 'tmpl/dofinish.html' );

		$tmpl->addVar( 'body', 'prevstep',	$step - 1 );
		$tmpl->addVar( 'body', 'step',		$step );
		$tmpl->addVar( 'body', 'nextstep',	$step + 1 );
		$tmpl->addVar( 'log',  'message',	$this->get('confirmation') );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>
