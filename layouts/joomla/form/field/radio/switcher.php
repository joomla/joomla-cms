<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\FileLayout;

// The layout is needed for transition from "btn-group" in Joomla! 3.10 to the "switcher" field in Joomla! 4

if (!$displayData['class'] || strpos($displayData['class'], 'btn-group') === false)
{
	$displayData['class'] .= ' btn-group';
}

$renderer = new FileLayout('joomla.form.field.radio', null, $this->options);
$renderer->setIncludePaths($this->includePaths);

echo $renderer->render($displayData);
