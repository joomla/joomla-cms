<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.textarea
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Textarea\Extension;

use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Textarea Plugin
 *
 * @since  3.7.0
 */
final class Textarea extends FieldsPlugin implements SubscriberInterface
{
}
