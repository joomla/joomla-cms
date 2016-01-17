<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app             = JFactory::getApplication();
$doc             = JFactory::getDocument();
$this->language  = $doc->language;
$this->direction = $doc->direction;

// Define the template asset
$css = array(
	'template.css',
	'user.css',
);
$js  = array('template.js');
$dep = array('bootstrap.js');

if ($this->params->get('googleFont'))
{
	array_unshift($css, '//fonts.googleapis.com/css?family=' . $this->params->get('googleFontName'));
}

if($this->direction === 'rtl')
{
	$dep[] = 'bootstrap.css.' . $this->direction;
}

$assetTemplate = new JAssetItem('template.protostar');
$assetTemplate->setCss($css);
$assetTemplate->setJs($js);
$assetTemplate->setDependency($dep);
$assetTemplate->versionAttach(true);

// Make the template asset active
JHtml::_('asset.load', $assetTemplate);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<jdoc:include type="head" />
<!--[if lt IE 9]>
	<script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
<![endif]-->
</head>
<body class="contentpane modal">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
