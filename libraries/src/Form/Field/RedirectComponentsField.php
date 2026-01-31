<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperRedirectAwareInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  __DEPLOYMENT_VERSION__
 */
class RedirectComponentsField extends ComponentsField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since  __DEPLOYMENT_VERSION__
     */
    protected $type = 'RedirectComponents';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  object[]  An array of JHtml options.
     *
     * @since   __DEPLOYMENT_VERSION__
     */
    protected function getOptions()
    {
        $app       = Factory::getApplication();
        $items     = parent::getOptions();
        $options   = [];

        foreach ($items as $item) {
            if (!str_starts_with($item->value, 'com_')) {
                continue;
            }

            $component = $app->bootComponent($item->value);

            if (!($component instanceof HelperRedirectAwareInterface)) {
                continue;
            }

            $options[] = HTMLHelper::_('select.option', $item->value, Text::sprintf('JFIELD_REDIRECT_COMPONENTS_OPTION', $item->text, $item->value));
        }

        return $options;
    }
}
