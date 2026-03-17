<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Package field.
 *
 * Selects the extension ID of an extension of the "package" type.
 *
 * @since 4.2.0
 */
class PackageField extends ListField
{
    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     * @since   4.2.0
     */
    protected function getOptions()
    {
        $options = InstallerHelper::getPackageOptions();

        return array_merge(parent::getOptions(), $options);
    }
}
