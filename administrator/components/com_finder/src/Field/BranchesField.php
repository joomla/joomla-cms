<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Search Branches field for the Finder package.
 *
 * @since  3.5
 */
class BranchesField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.5
     */
    protected $type = 'Branches';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.5
     */
    public function getOptions()
    {
        Factory::getApplication()->bootComponent('com_finder');

        return HTMLHelper::_('finder.mapslist');
    }
}
