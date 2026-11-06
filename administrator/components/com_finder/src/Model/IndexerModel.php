<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Indexer model class for Finder.
 *
 * @since  2.5
 */
class IndexerModel extends FormModel
{
    /**
     * Method for getting a form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form
     *
     * @since   5.0.0
     *
     * @throws \Exception
     */
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_finder.indexer', 'indexer', ['control' => '', 'load_data' => $loadData]);
    }
}
