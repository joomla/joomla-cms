<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.usergrouplist
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\UsergroupList\Extension;

use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields UsergroupList Plugin
 *
 * @since  3.7.0
 */
final class UsergroupList extends FieldsPlugin implements SubscriberInterface
{
}
