<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Factory::getDocument()->getWebAssetManager()
	->useScript('webcomponent.toolbar-button');

/**
 * Generic toolbar button layout to open a modal
 * -----------------------------------------------
 * @param   array   $displayData    Button parameters. Default supported parameters:
 *                                  - selector  string  Unique DOM identifier for the modal. CSS id without #
 *                                  - class     string  Button class
 *                                  - icon      string  Button icon
 *                                  - text      string  Button text
 */

$selector = $displayData['selector'];
$id       = isset($displayData['id']) ? $displayData['id'] : '';
$class    = isset($displayData['class']) ? $displayData['class'] : 'btn btn-primary';
$icon     = isset($displayData['icon']) ? $displayData['icon'] : 'icon-download';
$text     = isset($displayData['text']) ? $displayData['text'] : '';

// Enqueue the modal at the body bottom
Factory::getDocument()->appendBodyEnd(
	HTMLHelper::_('bootstrap.renderModal',
		'modal_' . $selector,
		[
			'url'         => $displayData['doTask'],
			'title'       => $text,
			'height'      => '100%',
			'width'       => '100%',
			'modalWidth'  => 80,
			'bodyHeight'  => 60,
			'closeButton' => true,
			'footer'      => '<button class="btn btn-secondary" data-bs-dismiss="modal" type="button"'
							. ' onclick="window.parent.Joomla.Modal.getCurrent().close();">'
							. Text::_('COM_BANNERS_CANCEL') . '</button>'
							. '<button class="btn btn-success" type="button"'
							. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#modal_downloadModal\', buttonSelector: \'#exportBtn\'})">'
							. Text::_('COM_BANNERS_TRACKS_EXPORT') . '</button>',
		]
	)
);
?>
<joomla-toolbar-button<?php echo $id; ?>>
	<button
		class="btn btn-primary"
		type="button"
		data-toggle="modal"
		data-target="#modal_<?php echo $selector; ?>">
		<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
		<?php echo $text; ?>
	</button>
</joomla-toolbar-button>
