<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Controller;

use Joomla\CMS\Event\Finder\GarbageCollectionEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Index controller class for Finder.
 *
 * @since  2.5
 */
class IndexController extends AdminController
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   2.5
     */
    public function getModel($name = 'Index', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to optimise the index by removing orphaned entries.
     *
     * @return  boolean  True on success.
     *
     * @since   4.2.0
     */
    public function optimise()
    {
        $this->checkToken();

        $dispatcher = $this->getDispatcher();

        // Optimise the index by first running the garbage collection
        PluginHelper::importPlugin('finder', null, true, $dispatcher);
        $dispatcher->dispatch('onFinderGarbageCollection', new GarbageCollectionEvent('onFinderGarbageCollection', []));

        // Now run the optimisation method from the indexer
        $indexer = new Indexer();
        $indexer->optimize();

        $message = Text::_('COM_FINDER_INDEX_OPTIMISE_FINISHED');
        $this->setRedirect('index.php?option=com_finder&view=index', $message);

        return true;
    }

    /**
     * Method to purge all indexed links from the database.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    public function purge()
    {
        $this->checkToken();

        // Remove the script time limit.
        if (\function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        /** @var \Joomla\Component\Finder\Administrator\Model\IndexModel $model */
        $model = $this->getModel('Index', 'Administrator');

        // Attempt to purge the index.
        $return = $model->purge();

        if (!$return) {
            $message = Text::_('COM_FINDER_INDEX_PURGE_FAILED', $model->getError());
            $this->setRedirect('index.php?option=com_finder&view=index', $message);

            return false;
        }

        $message = Text::_('COM_FINDER_INDEX_PURGE_SUCCESS');
        $this->setRedirect('index.php?option=com_finder&view=index', $message);

        return true;
    }
}
