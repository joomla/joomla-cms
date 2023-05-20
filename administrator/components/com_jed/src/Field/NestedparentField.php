<?php

/**
 * @package       JED
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use RuntimeException;

/**
 * Supports an HTML select list of categories
 *
 * @since  4.0.0
 */
class NestedparentField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'nestedparent';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   4.0.0
     * @throws Exception
     */
    protected function getOptions(): array
    {
        $options = [];
        $table   = $this->getAttribute('table');

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('DISTINCT(a.id) AS value, a.title AS text, a.level, a.lft')
            ->from($table . ' AS a');


        // Prevent parenting to children of this item.
        if ($id = $this->form->getValue('id')) {
            $query->join('LEFT', $db->quoteName($table) . ' AS p ON p.id = ' . (int) $id)
                ->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');
        }

        $query->order('a.lft ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (RuntimeException $e) {
            throw new Exception($e->getMessage(), 500);
        }

        // Pad the option text with spaces using depth level as a multiplier.
        for ($i = 0, $n = count($options); $i < $n; $i++) {
            $options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getOptions(), $options);
    }
}
