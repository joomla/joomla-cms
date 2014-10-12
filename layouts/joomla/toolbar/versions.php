<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleDeclaration('
		@media only screen and (min-width : 768px) {
			#versionsModal {
			width: 80% !important;
			margin-left:-40% !important;
			height:auto;
			}
			#versionsModal #versionsModal-container .modal-body iframe {
			margin:0;
			padding:0;
			display:block;
			width:100%;
			height:400px !important;
			border:none;
			}
		}');

$lang = JFactory::getLanguage();
$extension = 'com_contenthistory';
$base_dir = JFactory::getApplication()->isAdmin() ? JPATH_ADMINISTRATOR : JPATH_SITE;
$language_tag = $lang->getName();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);


$link = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id='
	. (int) $displayData['itemId'] . '&amp;type_id=' . $displayData['typeId'] . '&amp;type_alias='
	. $displayData['typeAlias'] . '&amp;' . JSession::getFormToken() . '=1';

echo JHtmlBootstrap::renderModal('versionsModal', array( 'url' => $link, 'title' => JText::_('COM_CONTENTHISTORY_MODAL_TITLE'),'height' => '600px', 'width' => '800px'), '');
?>
<button onclick="jQuery('#versionsModal').modal('show')" class="btn btn-small" data-toggle="modal" title="<?php echo $displayData['title']; ?>">
<span class="icon-archive"></span><?php echo $displayData['title']; ?></button>
