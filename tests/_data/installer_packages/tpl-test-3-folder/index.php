<?php defined('_JEXEC') or die;
/**
* @package		Test Template for Joomla! 1.6
* @author		Joomla Engineering http://joomlaengineering.com
* @copyright	Copyright (C) 2010 Matt Thomas | Joomla Engineering. All rights reserved.
* @license		GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// To enable use of site configuration
$app 					= JFactory::getApplication();
// Get and define template parameters
$fontFamily 			= $this->params->get('fontFamily');
$setGeneratorTag		= $this->params->get('setGeneratorTag');
// Change generator tag
$this->setGenerator($setGeneratorTag);
?>

<?php echo '<?'; ?>xml version="1.0" encoding="<?php echo $this->_charset ?>"
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />
  <meta name="copyright" content="<?php echo $app->getCfg('sitename');?>" />
  <link rel="stylesheet" href="<?php echo 'templates/'.$this->template; ?>/css/screen.css" type="text/css" media="screen" />
</head>

<body class="<?php echo $fontFamily;?>">
	<?php if ($this->getBuffer('message')) : ?>
		<div class="error">
			<jdoc:include type="message" />
		</div>
	<?php endif; ?>

	<jdoc:include type="component" />

</body>
</html>
