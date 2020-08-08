<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   string  $id
 * @var   string  $itemId
 * @var   string  $typeId
 * @var   string  $typeAlias
 * @var   string  $title
 */

Factory::getDocument()->getWebAssetManager()
	->useScript('core')
	->useScript('webcomponent.toolbar-button');

echo HTMLHelper::_(
	'bootstrap.renderModal',
	'versionsModal',
	array(
		'url'    => 'index.php?' . http_build_query(
			[
				'option' => 'com_contenthistory',
				'view' => 'history',
				'layout' => 'modal',
				'tmpl' => 'component',
				'item_id' => $itemId,
				Session::getFormToken() => 1
			]
		),
		'title'  => $title,
		'height' => '100%',
		'width'  => '100%',
		'modalWidth'  => '80',
		'bodyHeight'  => '60',
		'footer' => '<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
			. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
	)
);
?>
<joomla-toolbar-button id="toolbar-versions">
	<button
		class="btn btn-primary"
		type="button"
		onclick="document.getElementById('versionsModal').open()"
		data-toggle="modal">
		<span class="fas fa-code-branch" aria-hidden="true"></span>
		<?php echo $title; ?>
	</button>
</joomla-toolbar-button>
