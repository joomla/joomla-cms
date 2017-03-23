<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.aurora
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Add Stylesheets
JHtml::_('stylesheet', 'template.css', array('version' => 'auto', 'relative' => true));

// Load optional rtl Bootstrap css and Bootstrap bugfixes
//JHtml::_('bootstrap.loadCss', false, $this->direction);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<jdoc:include type="head" />
</head>
<body class="contentpane modal">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
