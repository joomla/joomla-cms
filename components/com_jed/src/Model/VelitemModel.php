<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Uri\Uri;

use function defined;

/**
 * VEL Vulnerable Item Model Class.
 *
 * @since 4.0.0
 */
class VelitemModel extends ItemModel
{
    /**
     * The item object
     *
     * @var    object
     * @since  4.0.0
     */
    public $item;

    /**
     * Method to get a single Vulnerable Item.
     *
     * @param   null  $pk  The id of the object to get.
     *
     * @return object|bool Object on success, false on failure.
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    public function getItem($pk = null)
    {

        if ($this->item === null) {
            $this->item = false;

            if (empty($pk)) {
                $pk = $this->getState('velitem.id');
            }
            try {
                // Get a db connection.
                $db = Factory::getContainer()->get('DatabaseDriver');

                // Create a new query object.
                $query = $db->getQuery(true);

                // Get from #__jed_vel_vulnerable_item as a
                $query->select($db->quoteName(
                    ['a.id', 'a.title', 'a.public_description', 'a.alias', 'a.state'],
                    ['id', 'title', 'public_description', 'alias', 'state']
                ));
                $query->from($db->quoteName('#__jed_vel_vulnerable_item', 'a'));
                $query->where('a.state=1 and a.id = ' . (int) $pk);

                // Reset the query using our newly populated query object.
                $db->setQuery($query);
                // Load the results as a stdClass object.
                $data = $db->loadObject();

                if (empty($data)) {
                    $app = Factory::getApplication();
                    // If no data is found redirect to default page and show warning.
                    $app->enqueueMessage("Cannot access Vulnerable Item List entry", 'warning');
                    $app->redirect(Uri::root());

                    return false;
                }

                // set data object to item.
                $this->item = $data;
            } catch (Exception $e) {
                if ($e->getCode() == 404) {
                    // Need to go through the error handler to allow Redirect to work.
                    throw $e;
                } else {
                    $this->setError($e);
                    $this->item = false;
                }
            }
        }

        return $this->item;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     * Note. Function not needed as model is only displayed
     *
     * @return void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    protected function populateState()
    {
        $app  = Factory::getApplication();
        $user = JedHelper::getUser();

        // Check published state
        if ((!$user->authorise('core.edit.state', 'com_jed')) && (!$user->authorise('core.edit', 'com_jed'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_jed.edit.velitem.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_jed.edit.velitem.id', $id);
        }

        $this->setState('velitem.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('velitem.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }
}
