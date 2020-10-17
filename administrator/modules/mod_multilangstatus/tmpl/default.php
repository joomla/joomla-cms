<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app->getDocument()->getWebAssetManager()
	->registerAndUseScript('mod_multilangstatus.admin', 'mod_multilangstatus/admin-multilangstatus.min.js', [], ['defer' => true]);

?>

<div class="header-item-content multilanguage">
	<a class="d-flex align-items-stretch" href="#multiLangModal" title="<?php echo Text::_('MOD_MULTILANGSTATUS'); ?>" data-toggle="modal" role="button">
		<div class="d-flex align-items-end mx-auto">
			<span class="fas fa-language" aria-hidden="true"></span>
		</div>
		<div class="tiny">
			<?php echo Text::_('MOD_MULTILANGSTATUS'); ?>
		</div>
	</a>

	<?php echo HTMLHelper::_(
		'bootstrap.renderModal',
		'multiLangModal',
		array(
			'title'      => Text::_('MOD_MULTILANGSTATUS'),
			'url'        => Route::_('index.php?option=com_languages&view=multilangstatus&tmpl=component'),
			'height'     => '400px',
			'width'      => '800px',
			'bodyHeight' => 70,
			'modalWidth' => 80,
			'footer'     => '<button type="button" class="btn btn-secondary" data-dismiss="modal">' . Text::_('JTOOLBAR_CLOSE') . '</button>',
		)
	); ?>
</div>
