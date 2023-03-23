<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Redirect link list controller class.
 *
 * @since  1.6
 */
class LinksController extends AdminController
{
    /**
     * Method to update a record.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function activate()
    {
        // Check for request forgeries.
        $this->checkToken();

        $ids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (empty($ids)) {
            $this->app->enqueueMessage(Text::_('COM_REDIRECT_NO_ITEM_SELECTED'), 'warning');
        } else {
            $newUrl  = $this->input->getString('new_url');
            $comment = $this->input->getString('comment');

            // Get the model.
            $model = $this->getModel();

            // Remove the items.
            if (!$model->activate($ids, $newUrl, $comment)) {
                $this->app->enqueueMessage($model->getError(), 'warning');
            } else {
                $this->setMessage(Text::plural('COM_REDIRECT_N_LINKS_UPDATED', count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_redirect&view=links');
    }

    /**
     * Method to duplicate URLs in records.
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function duplicateUrls()
    {
        // Check for request forgeries.
        $this->checkToken();

        $ids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (empty($ids)) {
            $this->app->enqueueMessage(Text::_('COM_REDIRECT_NO_ITEM_SELECTED'), 'warning');
        } else {
            $newUrl  = $this->input->getString('new_url');
            $comment = $this->input->getString('comment');

            // Get the model.
            $model = $this->getModel();

            // Remove the items.
            if (!$model->duplicateUrls($ids, $newUrl, $comment)) {
                $this->app->enqueueMessage($model->getError(), 'warning');
            } else {
                $this->setMessage(Text::plural('COM_REDIRECT_N_LINKS_UPDATED', count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_redirect&view=links');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  The prefix of the model.
     * @param   array   $config  An array of settings.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel The model instance
     *
     * @since   1.6
     */
    public function getModel($name = 'Link', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Executes the batch process to add URLs to the database
     *
     * @return  void
     */
    public function batch()
    {
        // Check for request forgeries.
        $this->checkToken();

        $batch_urls_request = $this->input->post->get('batch_urls', [], 'array');
        $batch_urls_lines   = array_map('trim', explode("\n", $batch_urls_request[0]));

        $batch_urls = [];

        foreach ($batch_urls_lines as $batch_urls_line) {
            if (!empty($batch_urls_line)) {
                $params = ComponentHelper::getParams('com_redirect');
                $separator = $params->get('separator', '|');

                // Basic check to make sure the correct separator is being used
                if (!\Joomla\String\StringHelper::strpos($batch_urls_line, $separator)) {
                    $this->setMessage(Text::sprintf('COM_REDIRECT_NO_SEPARATOR_FOUND', $separator), 'error');
                    $this->setRedirect('index.php?option=com_redirect&view=links');

                    return;
                }

                $batch_urls[] = array_map('trim', explode($separator, $batch_urls_line));
            }
        }

        // Set default message on error - overwrite if successful
        $this->setMessage(Text::_('COM_REDIRECT_NO_ITEM_ADDED'), 'error');

        if (!empty($batch_urls)) {
            $model = $this->getModel('Links');

            // Execute the batch process
            if ($model->batchProcess($batch_urls)) {
                $this->setMessage(Text::plural('COM_REDIRECT_N_LINKS_ADDED', count($batch_urls)));
            }
        }

        $this->setRedirect('index.php?option=com_redirect&view=links');
    }

    /**
     * Clean out the unpublished links.
     *
     * @return  void
     *
     * @since   3.5
     */
    public function purge()
    {
        // Check for request forgeries.
        $this->checkToken();

        $model = $this->getModel('Links');

        if ($model->purge()) {
            $message = Text::_('COM_REDIRECT_CLEAR_SUCCESS');
        } else {
            $message = Text::_('COM_REDIRECT_CLEAR_FAIL');
        }

        $this->setRedirect('index.php?option=com_redirect&view=links', $message);
    }
}
