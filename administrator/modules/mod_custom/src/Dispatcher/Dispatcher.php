<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_custom
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Custom\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_custom
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        if ($data['params']->def('prepare_content', 1)) {
            PluginHelper::importPlugin('content');
            $this->module->content = HTMLHelper::_('content.prepare', $this->module->content, '', 'mod_custom.content');
        }

        // Replace 'images/' to '../images/' when using an image from /images in backend.
        $this->module->content = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $this->module->content);

        return $data;
    }
}
