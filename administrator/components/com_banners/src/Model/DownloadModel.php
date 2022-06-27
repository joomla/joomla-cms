<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;

/**
 * Download model.
 *
 * @since  1.5
 */
class DownloadModel extends FormModel
{
    /**
     * The model context
     *
     * @var  string
     */
    protected $_context = 'com_banners.tracks';

    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $input = Factory::getApplication()->input;

        $this->setState('basename', $input->cookie->getString(ApplicationHelper::getHash($this->_context . '.basename'), '__SITE__'));
        $this->setState('compressed', $input->cookie->getInt(ApplicationHelper::getHash($this->_context . '.compressed'), 1));
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_banners.download', 'download', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        $data = (object) array(
            'basename'   => $this->getState('basename'),
            'compressed' => $this->getState('compressed'),
        );

        $this->preprocessData('com_banners.download', $data);

        return $data;
    }
}
