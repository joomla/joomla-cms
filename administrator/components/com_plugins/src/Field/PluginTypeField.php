<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\Plugins\Administrator\Helper\PluginsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin Type field.
 *
 * @since  3.5
 */
class PluginTypeField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.5
     */
    protected $type = 'PluginType';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.5
     */
    public function getOptions()
    {
        $options = PluginsHelper::folderOptions();

        return array_merge(parent::getOptions(), $options);
    }
}
