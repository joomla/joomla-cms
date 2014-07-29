<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersControllerBanners extends JControllerAdmin
{
	/**
	 * @var     string  The prefix to use with controller messages.
	 *
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_BANNERS_BANNERS';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('sticky_unpublish', 'sticky_publish');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Banner', $prefix = 'BannersModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Stick items
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function sticky_publish()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('sticky_publish' => 1, 'sticky_unpublish' => 0);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_BANNERS_NO_BANNERS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Change the state of the records.
			if (!$model->stick($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_BANNERS_N_BANNERS_STUCK';
				}
				else
				{
					$ntext = 'COM_BANNERS_N_BANNERS_UNSTUCK';
				}

				$this->setMessage(JText::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_banners&view=banners');
	}
}
