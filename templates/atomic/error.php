<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
if (!isset($this->error)) {
	$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false; 
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<title><?php echo $this->error->getCode(); ?> - <?php echo $this->title; ?></title>
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/error.css" type="text/css" />
</head>
<body>
	<div class="error">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxheader"><?php echo $this->error->getCode(); ?> - <?php echo $this->error->getMessage(); ?></div>
			<div id="errorboxbody">
			<p><strong><?php echo JText::_('YOU_MAY_NOT_BE_ABLE_TO_VISIT_THIS_PAGE_BECAUSE_OF'); ?>:</strong></p>
				<ol>
					<li><?php echo JText::_('AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
					<li><?php echo JText::_('A_SEARCH_ENGINE_THAT_HAS_AN_OUT_OF_DATE_LISTING_FOR_THIS_SITE'); ?></li>
					<li><?php echo JText::_('A_MIS_TYPED_ADDRESS'); ?></li>
					<li><?php echo JText::_('YOU_HAVE_NO_ACCESS_TO_THIS_PAGE'); ?></li>
					<li><?php echo JText::_('The requested resource was not found'); ?></li>
					<li><?php echo JText::_('An error has occurred while processing your request.'); ?></li>
				</ol>
			<p><strong><?php echo JText::_('PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?>:</strong></p>

				<ul>
					<li><a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('HOME_PAGE'); ?></a></li>
				</ul>

			<p><?php echo JText::_('IF_DIFFICULTIES_PERSIST__PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR_OF_THIS_SITE'); ?></p>
			<div id="techinfo">
			<p><?php echo $this->error->getMessage(); ?></p>
			<p>
				<?php if ($this->debug) :
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
