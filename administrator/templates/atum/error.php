<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** @var \Joomla\CMS\Document\ErrorDocument $this */

// Authenticated versus guest have different displays
$user = Factory::getUser();

if ($user->guest)
{
	require __DIR__ . '/error_login.php';
}
else
{
	require __DIR__ . '/error_full.php';
}
