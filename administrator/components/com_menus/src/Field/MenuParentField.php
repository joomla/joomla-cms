<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Parent field.
 *
 * @since  1.6
 */
class MenuParentField extends ListField
{
    /**
     * The form field type.
     *
     * @var        string
     * @since   1.6
     */
    protected $type = 'MenuParent';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   1.6
     */
    protected function getOptions()
    {
        $options = [];

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    'DISTINCT ' . $db->quoteName('a.id', 'value'),
                    $db->quoteName('a.title', 'text'),
                    $db->quoteName('a.level'),
                    $db->quoteName('a.lft'),
                ]
            )
            ->from($db->quoteName('#__menu', 'a'));

        // Filter by menu type.
        if ($menuType = $this->form->getValue('menutype')) {
            $query->where($db->quoteName('a.menutype') . ' = :menuType')
                ->bind(':menuType', $menuType);
        } else {
            // Skip special menu types
            $query->where($db->quoteName('a.menutype') . ' != ' . $db->quote(''));
            $query->where($db->quoteName('a.menutype') . ' != ' . $db->quote('main'));
        }

        // Filter by client id.
        $clientId = $this->getAttribute('clientid');

        if (!is_null($clientId)) {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('a.client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER);
        }

        // Prevent parenting to children of this item.
        if ($id = (int) $this->form->getValue('id')) {
            $query->join('LEFT', $db->quoteName('#__menu', 'p'), $db->quoteName('p.id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER)
                ->where(
                    'NOT(' . $db->quoteName('a.lft') . ' >= ' . $db->quoteName('p.lft')
                    . ' AND ' . $db->quoteName('a.rgt') . ' <= ' . $db->quoteName('p.rgt') . ')'
                );
        }

        $query->where($db->quoteName('a.published') . ' != -2')
            ->order($db->quoteName('a.lft') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Pad the option text with spaces using depth level as a multiplier.
        for ($i = 0, $n = count($options); $i < $n; $i++) {
            if ($clientId != 0) {
                // Allow translation of custom admin menus
                $options[$i]->text = str_repeat('- ', $options[$i]->level) . Text::_($options[$i]->text);
            } else {
                $options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
