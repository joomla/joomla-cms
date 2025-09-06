<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

// Evaluate runtime params
LayoutHelper::render('cassiopeia.params_evaluator', ['doc' => & $this, 'entry' => basename(__FILE__, '.php')]);

// Render the template
echo LayoutHelper::render('cassiopeia.entries', ['doc' => & $this, 'entry' => basename(__FILE__, '.php')]);
