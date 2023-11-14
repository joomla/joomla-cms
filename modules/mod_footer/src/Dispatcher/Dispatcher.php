<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_footer
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Footer\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_footer
 *
 * @since  4.4.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.4.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $lineOne = $this->getApplication()->getLanguage()->_('MOD_FOOTER_LINE1');
        $lineOne = str_replace('%date%', HTMLHelper::_('date', 'now', 'Y'), $lineOne);
        $lineOne = str_replace('%sitename%', $this->getApplication()->get('sitename', ''), $lineOne);

        $data['lineone'] = $lineOne;

        return $data;
    }
}
