<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.rotate
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Rotate\Extension;

use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Rotate Action
 *
 * @since  4.0.0
 */
final class Rotate extends MediaActionPlugin implements SubscriberInterface
{
}
