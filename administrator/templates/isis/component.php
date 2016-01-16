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

// Define the template asset
$css = array(
	'template' . ($this->direction == 'rtl' ? '-rtl' : '') . '.css',
	'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css',
	'custom.css',
);
$js  = array('template.js');
$dep = array('bootstrap.js');

$assetTemplate = new JHtmlAssetItem('template.isis');
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
	<jdoc:include type="head" />
	<!--[if lt IE 9]>
		<script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script>
	<![endif]-->

	<!-- Link color -->
	<?php if ($this->params->get('linkColor')) : ?>
		<style type="text/css">
			a
			{
				color: <?php echo $this->params->get('linkColor'); ?>;
			}
		</style>
	<?php endif; ?>
</head>
<body class="contentpane component">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
