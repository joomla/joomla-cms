<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Content;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Category event.
 * Example:
 *  new ItemsDisplayEvent('onEventName', ['context' => 'com_example.example', 'subject' => $contentItems, 'params' => $params, 'page' => $pageNum]);
 *
 * @since  5.0.0
 */
class ItemsDisplayEvent extends ContentPrepareEvent
{
}
