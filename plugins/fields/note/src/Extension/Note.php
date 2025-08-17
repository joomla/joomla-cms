<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.note
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Note\Extension;

use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Note Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class Note extends FieldsPlugin implements SubscriberInterface
{
}
