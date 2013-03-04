<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

// Get additional language strings prefixed with TPL_HATHOR
$lang = JFactory::getLanguage();
$lang->load('tpl_hathor', JPATH_ADMINISTRATOR)
|| $lang->load('tpl_hathor', JPATH_ADMINISTRATOR . '/templates/hathor/language');
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<link href="templates/system/css/system.css" type="text/css" rel="stylesheet" type="text/css" />
<link href="templates/<?php echo  $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />

<?php
	if (!$this->params->get('colourChoice')) :
		$colour = 'standard';
	else :
		$colour = htmlspecialchars($this->params->get('colourChoice'));
	endif;
?>
<link href="templates/<?php echo $this->template ?>/css/colour_<?php echo $colour; ?>.css" rel="stylesheet" type="text/css" />

<!-- Load additional CSS styles for rtl sites -->
<?php if ($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo  $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
	<link href="templates/<?php echo $this->template ?>/css/colour_<?php echo $colour; ?>_rtl.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

<!-- Load specific language related css -->
<?php if (JFile::exists($file)) : ?>
	<link href="<?php echo $file ?>" rel="stylesheet" type="text/css" />
<?php  endif; ?>

<!-- Load additional CSS styles for bold Text -->
<?php if ($this->params->get('boldText')) : ?>
	<link href="templates/<?php echo $this->template ?>/css/boldtext.css" rel="stylesheet" type="text/css" />
<?php  endif; ?>


</head>
<body class="contentpane">
	<jdoc:include type="message" />
	<jdoc:include type="component" />
</body>
</html>
