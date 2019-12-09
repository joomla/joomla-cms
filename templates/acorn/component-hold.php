<?php
	/**
	 * @package     Joomla.Administrator
	 * @subpackage  Templates.acorn
	 *
	 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
	defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

// TODO this file is complete trash now.. what does it even do?

// Used with HTMLHelper::
$HTMLHelperDebug        = array('version' => 'auto', 'relative' => true, 'detectDebug' => true);
$HTMLHelperDebugNoDebug = array('version' => 'auto', 'relative' => true, 'detectDebug' => false);

/** @var JDocumentHtml $this */
// Add JavaScript Frameworks
	HTMLHelper::_('bootstrap.framework');

// Add Stylesheets
HTMLHelper::_( 'stylesheet', '/framework/joomla.fix.css', $HTMLHelperDebug );
HTMLHelper::_( 'stylesheet', '/framework/template.css', $HTMLHelperDebug );
HTMLHelper::_( 'stylesheet', '/colors/' . $theme . '.css', $HTMLHelperDebug );
HTMLHelper::_( 'stylesheet', '/framework/extension-fixes.css', $HTMLHelperDebug );
JFile::exists( $template . '/css/custom.css' ) ? HTMLHelper::_( 'stylesheet', '/custom.css', $HTMLHelperDebug ) : '';

// Load optional rtl Bootstrap css and Bootstrap bugfixes
//HTMLHelper::_('bootstrap.loadCss', false, $this->direction);
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	<jdoc:include type="head" />
</head>
<body  id="main" class="site <?php echo $bodyclass . " " . $pageclass . " " . $loggedin . " " . $this->direction; ?>">
<jdoc:include type="message" />
<jdoc:include type="component" />
</body>
</html>
