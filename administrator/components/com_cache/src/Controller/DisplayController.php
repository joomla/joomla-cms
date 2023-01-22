<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cache\Administrator\Controller;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Cache Controller
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $default_view = 'cache';

    /**
     * Method to get The Cache Size
     *
     * @since   4.0.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('Cache');

        $data = $model->getData();

        $size = 0;

        if (!empty($data)) {
            foreach ($data as $d) {
                $size += $d->size;
            }
        }

        // Number bytes are returned in format xxx.xx MB
        $bytes = HTMLHelper::_('number.bytes', $size, 'MB', 1);

        if (!empty($bytes)) {
            $result['amount'] = $bytes;
            $result['sronly'] = Text::sprintf('COM_CACHE_QUICKICON_SRONLY', $bytes);
        } else {
            $result['amount'] = 0;
            $result['sronly'] = Text::sprintf('COM_CACHE_QUICKICON_SRONLY_NOCACHE');
        }

        echo new JsonResponse($result);
    }

    /**
     * Method to delete a list of cache groups.
     *
     * @return  void
     */
    public function delete()
    {
        // Check for request forgeries
        $this->checkToken();

        $cid = (array) $this->input->post->get('cid', [], 'string');

        if (empty($cid)) {
            $this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'warning');
        } else {
            $result = $this->getModel('cache')->cleanlist($cid);

            if ($result !== []) {
                $this->app->enqueueMessage(Text::sprintf('COM_CACHE_EXPIRED_ITEMS_DELETE_ERROR', implode(', ', $result)), 'error');
            } else {
                $this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_DELETED'), 'message');
            }
        }

        $this->setRedirect('index.php?option=com_cache');
    }

    /**
     * Method to delete all cache groups.
     *
     * @return  void
     *
     * @since  3.6.0
     */
    public function deleteAll()
    {
        // Check for request forgeries
        $this->checkToken();

        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model      = $this->getModel('cache');
        $allCleared = true;

        $mCache = $model->getCache();

        foreach ($mCache->getAll() as $cache) {
            if ($mCache->clean($cache->group) === false) {
                $this->app->enqueueMessage(
                    Text::sprintf(
                        'COM_CACHE_EXPIRED_ITEMS_DELETE_ERROR',
                        Text::_('JADMINISTRATOR') . ' > ' . $cache->group
                    ),
                    'error'
                );
                $allCleared = false;
            }
        }

        if ($allCleared) {
            $this->app->enqueueMessage(Text::_('COM_CACHE_MSG_ALL_CACHE_GROUPS_CLEARED'), 'message');
        } else {
            $this->app->enqueueMessage(Text::_('COM_CACHE_MSG_SOME_CACHE_GROUPS_CLEARED'), 'warning');
        }

        $this->app->triggerEvent('onAfterPurge', []);
        $this->setRedirect('index.php?option=com_cache&view=cache');
    }

    /**
     * Purge the cache.
     *
     * @return  void
     */
    public function purge()
    {
        // Check for request forgeries
        $this->checkToken();

        if (!$this->getModel('cache')->purge()) {
            $this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_PURGING_ERROR'), 'error');
        } else {
            $this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_PURGED'), 'message');
        }

        $this->setRedirect('index.php?option=com_cache&view=cache');
    }
}
