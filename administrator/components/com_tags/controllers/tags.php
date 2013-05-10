<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Tags List Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsControllerTags extends JControllerAdmin
{

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	 * @since  3.1
	 */
	protected $redirectUrl = 'index.php?option=com_tags&view=tags';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_tags';

	/*
	 * @var  string  Model name
	* @since  3.1
	*/
	protected $name = 'Tag';

	/*
	 * @var  string   Model prefix
	* @since  3.1
	*/
	protected $prefix = 'TagsModel';

	/**
	 * @var     string  The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_TAGS_TAGS';


	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Array of configuration options.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   3.1
	 * @deprecated  3.5
	 */
	public function getModel($name = 'Tag', $prefix = 'TagsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  boolean  False on failure or error, true on success.
	 *
	 * @since   3.1
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$extension = $this->input->get('extension');
		$this->setRedirect(JRoute::_('index.php?option=com_tags&view=tags', false));

		$model = $this->getModel();

		if ($model->rebuild()) {
			// Rebuild succeeded.
			$this->setMessage(JText::_('COM_TAGS_REBUILD_SUCCESS'));
			return true;
		} else {
			// Rebuild failed.
			$this->setMessage(JText::_('COM_TAGSS_REBUILD_FAILURE'));
			return false;
		}
	}
}
