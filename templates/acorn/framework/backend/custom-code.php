<?php

/**
 * @package     acorn.Framework
 * @subpackage  Custom Code Tab
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

// TODO Nothing happening yet.. check old version

$gaId             = $this->params->get( 'gaId' );
$googleMap        = $this->params->get( 'googleMap' );
$mapApi           = $this->params->get( 'mapApi' );
$mapWidth         = checkPX( $this->params->get( 'mapWidth' ) );
$mapHeight        = checkPX( $this->params->get( 'mapHeight' ) );
$maptopMargin     = checkPX( $this->params->get( 'maptopMargin' ) );
$mapbottomMargin  = checkPX( $this->params->get( 'mapbottomMargin' ) );
$useAddress       = $this->params->get( 'useAddress' );
$useName          = $this->params->get( 'useName' );
$usecustomjsFiles = $this->params->get( 'usecustomjsFiles' );
$usetagTitle      = $this->params->get( 'usetagTitle' );
