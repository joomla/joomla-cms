<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** @var JDocumentHtml $this */

// Add JavaScript Frameworks
HTMLHelper::_('bootstrap.framework');

// Add Stylesheets
HTMLHelper::_('stylesheet', 'template.css', ['version' => 'auto', 'relative' => true]);

// Load optional rtl Bootstrap css and Bootstrap bugfixes
//HTMLHelper::_('bootstrap.loadCss', false, $this->direction);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<jdoc:include type="head" />
</head>
<body>
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
