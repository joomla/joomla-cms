<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$lang            = JFactory::getLanguage();
$this->language  = $doc->language;
$this->direction = $doc->direction;

// Output as HTML5
$doc->setHtml5(true);

// Load the template asset
JHtml::_('asset.load', $doc->direction == 'rtl' ? 'template.isis.rtl' : 'template.isis.ltr');

// Link color
if ($this->params->get('linkColor'))
{
	$doc->addStyleDeclaration('a { color: ' . $this->params->get('linkColor') . '; }');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
</head>
<body class="contentpane component">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
