<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var JDocumentHtml $this */

$lang = Factory::getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('script', 'media/vendor/flying-focus-a11y/js/flying-focus.min.js', ['version' => 'auto']);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'font-awesome.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));
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
