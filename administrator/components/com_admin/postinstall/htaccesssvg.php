<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for notifying users of a change
 * in the default .htaccess file regarding hardening against XSS in SVG's
 */



/**
 * Notifies users of a change in the default .htaccess file regarding hardening against XSS in SVG's
 *
 * This check returns true regardless of condition.
 *
 * @return  boolean
 *
 * @since   3.9.21
 */
function admin_postinstall_htaccesssvg_condition()
{
    return true;
}
