<?php

/**
 * @package     acorn.Framework
 * @subpackage  Main Menu Tab
 * @version     14-Nov-19
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;
$nav_Location = $this->params->get( 'nav_Location' );
$nav_style    = $this->params->get( 'nav_style' );
$nav_wide     = $this->params->get( 'nav_wide' );
$iconCaret   = $this->params->get( 'icondownCaret', 'icon-arrow-down-3' );
