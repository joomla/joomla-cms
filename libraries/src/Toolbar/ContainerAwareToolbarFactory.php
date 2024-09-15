<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default factory for creating toolbar objects
 *
 * @since  4.0.0
 */
class ContainerAwareToolbarFactory implements ToolbarFactoryInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Creates a new toolbar button.
     *
     * @param   Toolbar  $toolbar  The Toolbar instance to attach to the button
     * @param   string   $type     Button Type
     *
     * @return  ToolbarButton
     *
     * @since   3.8.0
     * @throws  \InvalidArgumentException
     */
    public function createButton(Toolbar $toolbar, string $type): ToolbarButton
    {
        $normalisedType = ucfirst($type);
        $buttonClass    = $this->loadButtonClass($normalisedType);

        if (!$buttonClass) {
            $dirs = $toolbar->getButtonPath();

            $file = InputFilter::getInstance()->clean(str_replace('_', DIRECTORY_SEPARATOR, strtolower($type)) . '.php', 'path');

            if ($buttonFile = Path::find($dirs, $file)) {
                include_once $buttonFile;
            } else {
                Log::add(Text::sprintf('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile), Log::WARNING, 'jerror');

                throw new \InvalidArgumentException(Text::sprintf('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile));
            }
        }

        if (!class_exists($buttonClass)) {
            throw new \InvalidArgumentException(\sprintf('Class `%1$s` does not exist, could not create a toolbar button.', $buttonClass));
        }

        // Check for a possible service from the container otherwise manually instantiate the class
        if ($this->getContainer()->has($buttonClass)) {
            return $this->getContainer()->get($buttonClass);
        }

        /** @var ToolbarButton $button */
        $button = new $buttonClass($normalisedType);

        return $button->setParent($toolbar);
    }

    /**
     * Creates a new Toolbar object.
     *
     * @param   string  $name  The toolbar name.
     *
     * @return  Toolbar
     *
     * @since   4.0.0
     */
    public function createToolbar(string $name = 'toolbar'): Toolbar
    {
        return new Toolbar($name, $this);
    }

    /**
     * Load the button class including the deprecated ones.
     *
     * @param   string  $type  Button Type (normalized)
     *
     * @return  string|null
     *
     * @since   4.0.0
     */
    private function loadButtonClass(string $type)
    {
        $buttonClasses = [
            'Joomla\\CMS\\Toolbar\\Button\\' . $type . 'Button',
            /**
             * @deprecated  4.3 will be removed in 6.0
             */
            'JToolbarButton' . $type,
        ];

        foreach ($buttonClasses as $buttonClass) {
            if (!class_exists($buttonClass)) {
                continue;
            }

            return $buttonClass;
        }

        return null;
    }
}
