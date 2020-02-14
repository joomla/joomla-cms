<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

$cookieLogin = $this->user->get('cookieLogin');

if (!empty($cookieLogin) || $this->user->get('guest'))
{
	// The user is not logged in or needs to provide a password.
	echo $this->loadTemplate('login');
}
else
{
	// The user is already logged in.
	echo $this->loadTemplate('logout');
}
