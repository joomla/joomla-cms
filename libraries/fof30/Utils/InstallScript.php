<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils;

defined('_JEXEC') || die;

use FOF30\Utils\InstallScript\Component;

// Make sure the new class can be loaded
if (!class_exists('FOF30\\Utils\\InstallScript\\Component', true))
{
	require_once __DIR__ . '/InstallScript/Component.php';
}

/**
 * A helper class which you can use to create component installation scripts.
 *
 * This is the old location of the installation script class, maintained for backwards compatibility with FOF 3.0. Please
 * use the new class FOF30\Utils\InstallScript\Component instead.
 */
class InstallScript extends Component
{

}
