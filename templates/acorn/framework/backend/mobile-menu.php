<?php

	/**
	 * @package     acorn.Framework
	 * @subpackage  Mobile Menu Tab
	 *
	 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
	defined('_JEXEC') or die;

// Variables
	$iconHome = $this->params->get('iconhomeClass', '');
	$iconClose = $this->params->get('iconcloseClass', '');
	$iconHamburger = $this->params->get('icon$Class');
	$slide = $this->params->get('mmenuslide', 'left');
	$effect = $this->params->get('mmenueffect', 'behind');
	$color = $this->params->get('mmenucolor', 'mm-light');
	$header = $this->params->get('mmenuheader', 'true');
	$title = $this->params->get('mmenutitle');
	$Color = $this->params->get('mmenuhamburgerColor');
