<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

/**
 * Form field for a list of extension types.
 *
 * @since  3.5
 */
class TypeField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.5
     */
    protected $type = 'Type';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.5
     */
    public function getOptions()
    {
        $options = InstallerHelper::getExtensionTypes();

        return array_merge(parent::getOptions(), $options);
    }
}
