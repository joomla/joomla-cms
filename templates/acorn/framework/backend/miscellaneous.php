<?php

/**
 * @package       acorn.Framework
 * @subpackage    Miscellaneous Tab
 * @author        Bear
 * @copyright     Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/* Variables */
$frontpageshow    = $this->params->get('frontpageshow', 0);
$gotop            = $this->params->get('gotop');
$gotopCustomize   = $this->params->get('gotopCustomize');
$gotopbuttonClass = " " . trim($this->params->get('gotopbuttonClass', 'btn-default'));
$gotopText        = $this->params->get('gotopText', 'Go To Top') . '<i class="' . $this->params->get('gotopiconClass', 'glyphicon glyphicon-arrow-up') . '"></i>';
$metadata = $this->params->get('setGeneratorTag', '');
//Set the generator metadata
$doc->setGenerator($metadata);

