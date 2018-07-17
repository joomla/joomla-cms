<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * @var  string  $id
 * @var  string  $itemId
 * @var  string  $typeId
 * @var  string  $typeAlias
 * @var  string  $title
 */
extract($displayData, EXTR_OVERWRITE);

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
				'item_id' => (int) $itemId,
				'type_id' => $typeId,
				'type_alias' => $typeAlias,
				Session::getFormToken() => 1
			]
		),
		'title'  => $title,
		'height' => '100%',
		'width'  => '100%',
		'modalWidth'  => '80',
		'bodyHeight'  => '60',
		'footer' => '<a role="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
			. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
	)
);
?>
<button<?php echo $id ?? ''; ?>
    onclick="document.getElementById('versionsModal').open()"
	class="btn btn-primary"
	data-toggle="modal"
	title="<?php echo $title; ?>">
	<span class="fa fa-code-fork" aria-hidden="true"></span><?php echo $title; ?>
</button>
