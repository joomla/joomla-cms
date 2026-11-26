<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Enum;

\defined('_JEXEC') or die;

/**
 * Autoupdate State Enum
 */
enum AutoupdateState: int
{
    case Disabled = 0;
    case Enabled  = 1;
}
