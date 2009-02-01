<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

/**
 * Contactdirectory Component Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Contact
 * @since 1.5
 */
class ContactdirectoryController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$document =& JFactory::getDocument();

		$viewName	= JRequest::getCmd( 'view' );
		$viewType	= $document->getType();

		$view = &$this->getView($viewName, $viewType);

		$model	= &$this->getModel( $viewName );
		if (!JError::isError( $model )) {
			$view->setModel( $model, true );
		}

		$view->assign('error', $this->getError());
		$view->display();
	}

	/**
	 * Method to send an email to a contact
	 *
	 * @static
	 * @since 1.0
	 */
	function submit(){
		global $mainframe;

		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$user = &JFactory::getUser();
		$model =& $this->getModel('contact');

		if($model->mailTo($user)) {
			$msg = JText::_( 'THANK_MESSAGE');
			$contact = $model->getData($user->get('aid', 0));
			$mainframe->enqueueMessage($msg, "message");
			$this->display();
			//$link = JRoute::_('index.php?option=com_contactdirectory&view=contact&id='.$contact->slug, false);
			//$this->setRedirect($link, $msg);
		} else {
			$this->setError($model->getError());
			$this->display();
		}
	}

	/**
	 * Get the captcha image from securimage library
	 */
	function captcha()
	{
		require_once JPATH_COMPONENT . DS . 'includes' . DS . 'securimage' . DS . 'securimage.php';
		@ob_end_clean();
		$img = new securimage();
		$img->draw_lines = false;
		$img->arc_linethrough = false;
		$img->use_transparent_text = true;
		$img->text_transparency_percentage = 40;
		$img->text_color = '#0000ff';
		$img->use_multi_text = false;
		$img->font_size = 40;
		$img->bgimg = JPATH_COMPONENT . DS . 'includes' . DS . 'securimage' . DS . 'images' . DS . 'pattern.gif';
		$img->ttf_file = JPATH_COMPONENT . DS . 'includes' . DS . 'securimage' . DS . 'artistamp.ttf';
		$img->image_width = 150;
		$img->show();
	}
}
