<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Controller;

use Joomla\CMS\Language\Text;

/**
 * Featured content controller class.
 *
 * @since  1.6
 */
class FeaturedController extends ArticlesController
{
    /**
     * Removes an item.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function delete()
    {
        // Check for request forgeries
        $this->checkToken();

        $user = $this->app->getIdentity();
        $ids  = (array) $this->input->get('cid', array(), 'int');

        // Access checks.
        foreach ($ids as $i => $id) {
            // Remove zero value resulting from input filter
            if ($id === 0) {
                unset($ids[$i]);

                continue;
            }

            if (!$user->authorise('core.delete', 'com_content.article.' . (int) $id)) {
                // Prune items that you can't delete.
                unset($ids[$i]);
                $this->app->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'notice');
            }
        }

        if (empty($ids)) {
            $this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
        } else {
            /** @var \Joomla\Component\Content\Administrator\Model\FeatureModel $model */
            $model = $this->getModel();

            // Remove the items.
            if (!$model->featured($ids, 0)) {
                $this->app->enqueueMessage($model->getError(), 'error');
            }
        }

        $this->setRedirect('index.php?option=com_content&view=featured');
    }

    /**
     * Method to publish a list of articles.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function publish()
    {
        parent::publish();

        $this->setRedirect('index.php?option=com_content&view=featured');
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Feature', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }
}
