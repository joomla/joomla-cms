<?php
/**
 * @package     acorn.Framework
 * @subpackage  acorn Bottom Jquery includes
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var string $mmenucolor */
/** @var string $mmenuslide */
/** @var string $mmenueffect */
/** @var string $mmenutitle */
/** @var string $mmenuheader */

// If there are .js files in the custom.js folder & param is true then we need to load them.
if ($this->params->get('usecustomjsFiles'))
{
	Folder::exists($templatePath . '/js/custom') ?
		getCustomJsFiles($templatePath, $HTMLHelperDebug) :
		Factory::getApplication()->enqueueMessage(Text::_('TPL_ACORN_CUSTOMJS_FOLDER_ERROR'), 'danger');
}

?>
