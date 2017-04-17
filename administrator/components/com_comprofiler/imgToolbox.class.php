<?php
/**
 * $Id: imgToolbox.class.php 1880 2012-10-31 11:06:15Z beat $
-----------------------------------------------------------------------
|                                                                     |
| Date: March, 2005						      |
| Author: MamboJoe, <http://www.mambojoe.com>                         |
| Original Author: Mike de Boer, <http://www.mikedeboer.nl>           |
| Copyright: copyright (C) 2004 by Mike de Boer                       |
| Description: Abstracted Image Class			              |
| License: GPL                                                        |
| Filename: imgToolbox.class.php                                      |
| Version: 3.0                                                        |
|                                                                     |
-----------------------------------------------------------------------
-----------------------------------------------------------------------
|                                                                     |
| What is the toolbox? --> well, it's an object that bundles all      |
| medium-manipulation tools into one convenient class.                |
| These tools would include:                                          |
|                                                                     |
| - Image resizing                                                    |
| - Image rotating                                                    |
| - Image watermarking with custom TrueType fonts                     |
| - ALL tools have implementations for the following manipulation     |
|   software: ImageMagick, NetPBM, GD1.x and GD2.x.                   |
|                                                                     |
-----------------------------------------------------------------------
**/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * The class imgToolbox that was in here has moved to libraries/CBLib/CB/Legacy folder.
 */
