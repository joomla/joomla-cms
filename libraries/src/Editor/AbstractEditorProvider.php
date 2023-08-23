<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Editor\Button\ButtonInterface;
use Joomla\CMS\Editor\Button\ButtonsRegistry;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract editor provider
 *
 * @since   5.0.0
 */
abstract class AbstractEditorProvider implements EditorProviderInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Load the editor buttons.
     *
     * @param   mixed   $buttons  Array with button names to be excluded. Empty array or boolean true to display all buttons.
     * @param   array   $options  Associative array with additional parameters
     *
     * @return  ButtonInterface[]
     * @throws \Exception
     *
     * @since   5.0.0
     */
    public function getButtons($buttons, array $options = []): array
    {
        if ($buttons === false) {
            return [];
        }

        $loadAll = false;

        if ($buttons === true || $buttons === []) {
            $buttons = [];
            $loadAll = true;
        }

        if (!\is_array($buttons)) {
            throw new \UnexpectedValueException('The Buttons variable should be an array of names of disabled buttons or boolean.');
        }

        // Retrieve buttons for current editor
        $result  = [];
        $btnsReg = new ButtonsRegistry();
        $btnsReg->setDispatcher($this->getDispatcher())->initRegistry([
            'editorType'      => $this->getName(),
            'disabledButtons' => $buttons,
            'editorId'        => $options['editorId'] ?? '',
            'asset'           => (int) ($options['asset'] ?? 0),
            'author'          => (int) ($options['author'] ?? 0),
        ]);

        // Go through all and leave only allowed buttons
        foreach ($btnsReg->getAll() as $button) {
            $btnName = $button->getButtonName();

            if (!$loadAll && \in_array($btnName, $buttons)) {
                continue;
            }

            $result[] = $button;
        }

        return $result;
    }

    /**
     * Helper method for rendering the editor buttons.
     *
     * @param   mixed   $buttons  Array with button names to be excluded. Empty array or boolean true to display all buttons.
     * @param   array   $options  Associative array with additional parameters
     *
     * @return  string
     *
     * @since   5.0.0
     */
    protected function displayButtons($buttons, array $options = [])
    {
        $list = $this->getButtons($buttons, $options);

        return $list ? LayoutHelper::render('joomla.editors.buttons', $list) : '';
    }
}
