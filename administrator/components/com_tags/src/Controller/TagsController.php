<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Tags List Controller
 *
 * @since  3.1
 */
class TagsController extends AdminController
{
    /**
     * Proxy for getModel
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  An optional associative array of configuration settings.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   3.1
     */
    public function getModel($name = 'Tag', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
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
        $this->checkToken();

        $this->setRedirect(Route::_('index.php?option=com_tags&view=tags', false));

        /** @var \Joomla\Component\Tags\Administrator\Model\TagModel $model */
        $model = $this->getModel();

        if ($model->rebuild()) {
            // Rebuild succeeded.
            $this->setMessage(Text::_('COM_TAGS_REBUILD_SUCCESS'));

            return true;
        } else {
            // Rebuild failed.
            $this->setMessage(Text::_('COM_TAGS_REBUILD_FAILURE'));

            return false;
        }
    }

    /**
     * Method to get the JSON-encoded amount of published tags for quickicons
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('tags');

        $model->setState('filter.published', 1);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_TAGS_N_QUICKICON_SRONLY', $amount);
        $result['name']   = Text::plural('COM_TAGS_N_QUICKICON', $amount);

        echo new JsonResponse($result);
    }
}
