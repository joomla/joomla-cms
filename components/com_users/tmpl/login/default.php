<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var \Joomla\Component\Users\Site\View\Login\HtmlView $this */

if ((isset($this->user->cookieLogin) && !empty($this->user->cookieLogin)) || $this->user->guest) {
    // The user is not logged in or needs to provide a password.
    echo $this->loadTemplate('login');
} else {
    // The user is already logged in.
    echo $this->loadTemplate('logout');
}
