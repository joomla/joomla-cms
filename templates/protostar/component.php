<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$app = JFactory::getApplication();

// Output as HTML5
$this->setHtml5(true);

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');

// Load the polyfills for IE
JHtml::_('script', 'jui/html5.js', array('relative' => true, 'conditional' => 'lt IE 9'));

// Add Stylesheets
$this->addStyleSheetVersion($this->baseurl . '/templates/' . $this->template . '/css/template.css');

// Load optional rtl Bootstrap css and Bootstrap bugfixes
JHtmlBootstrap::loadCss($includeMaincss = false, $this->direction);

$this->setMetaData('viewport', 'width=device-width, initial-scale=1, maximum-scale=1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
</head>
<body class="contentpane modal">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
