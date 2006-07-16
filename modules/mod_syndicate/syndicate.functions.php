<?php
/**
* @version $Id: mod_rssfeed.php 588 2005-10-23 15:20:09Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class JModSyndicateController extends JController
{
	var $params;

	function display()
	{
		$option	= JRequest::getVar( 'option', '', 'get' );
		$task	= JRequest::getVar( 'task', '', 'get' );

		// paramters
		$this->params->def('text', 'Feed Entries');
		$this->params->def('format', 'rss');
		
		if($link = $this->_getSyndicateLink()) {
			$this->_outputSyndicateLink( $link );
		}
	}

	function _outputSyndicateLink( $link )
	{
		$img = mosAdminMenus::ImageCheck('livemarks.png', '/images/M_images/');
		?>
			<a href="<?php echo $link ?>">
				<?php echo $img ?> <span><?php echo $this->params->get('text') ?></span>
			</a>
		<?php
	}
	
	function _getSyndicateLink()
	{
		global $mainframe;
	
		$document =& $mainframe->getDocument();
	
		foreach($document->_links as $link)
		{
			if(strpos($link, 'application/'.$this->params->get('format').'+xml')) {
				preg_match("#href=\"(.*?)\"#s", $link, $matches);
				return $matches[1];
			}
		}
	
	}
}

?>