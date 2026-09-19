<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Select field type model.
 *
 * @since  __DEPLOY_VERSION__
 */
class SelectModel extends BaseDatabaseModel
{
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        $context = $app->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD');
        $this->setState('filter.context', $context);

        $parts = FieldsHelper::extract($context);

        // Extract the component name
        $this->setState('filter.component', $parts ? $parts[0] : null);

        // Extract the optional section name
        $this->setState('filter.section', ($parts && \count($parts) > 1) ? $parts[1] : null);
    }

    /**
     * Method to get the list of available field types.
     *
     * @return  \stdClass[]  An array of field type objects.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItems()
    {
        $items = [];

        foreach (FieldsHelper::getFieldTypes() as $fieldType) {
            $items[] = (object) $fieldType;
        }

        return ArrayHelper::sortObjects($items, 'label', 1, true, true);
    }
}
