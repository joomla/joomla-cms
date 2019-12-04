<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var JDocumentHtml $this */

$lang = Factory::getLanguage();
$wa   = $this->getWebAssetManager();

// Enable assets
$wa->enableAsset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'));
$wa->enableAsset('fontawesome-free');

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', ['version' => 'auto']);

// Load custom stylesheet if available
HTMLHelper::_('stylesheet', 'custom.css', ['version' => 'auto', 'relative' => true]);

// TODO: remove the following line whenever the assets are fixed to respect the overrides
HTMLHelper::_('stylesheet', 'vendor/choicesjs/choices.css', ['version' => 'auto', 'relative' => true]);
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
</head>
<body class="contentpane component">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
