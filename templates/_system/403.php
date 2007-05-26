<?php
/**
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.environment.uri');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<title>403 - <?php echo $this->title; ?></title>
	<base href="<?php echo JURI::base() ?>" />
	<link rel="stylesheet" href="templates/_system/css/error.css" type="text/css" />
</head>
<body>
	<div align="center">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxheader">403 - <?php echo JText::_('Access denied'); ?></div>
			<div id="errorboxbody">
			<p><strong><?php echo JText::_('You may not be able to visit this page because of:'); ?></strong></p>
				<ol>
					<li><?php echo JText::_('An <strong>out-of-date bookmark/favourite</strong>'); ?></li>
					<li><?php echo JText::_('A search engine that has an <strong>out-of-date listing for this site</strong>'); ?></li>
					<li><?php echo JText::_('A <strong>mis-typed address</strong>'); ?></li>
					<li><?php echo JText::_('You have <strong>no access</strong> to this page'); ?></li>
					<li><?php echo JText::_('The requested resource was <strong>not found</strong>'); ?></li>
				</ol>
			<p><strong><?php echo JText::_('Please try one of the following pages:'); ?></strong></p>
			<p>
				<ul>
					<li><a href="index.php" title="<?php echo JText::_('Go to the home page'); ?>"><?php echo JText::_('Home Page'); ?></a></li>
				</ul>
			</p>
			<div id="techinfo">
			<p><?php echo $this->message;   ?></p>
			<p>
				<?php if($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</p>
			</div>
			</div>
		</div>
		</div>
	</div>
</body>
</html>