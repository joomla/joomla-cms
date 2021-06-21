<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$displayData = [
	'textPrefix' => 'COM_REDIRECT',
	'formURL'    => 'index.php?option=com_redirect&view=links',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Redirects:_Links',
	'icon'       => 'icon-map-signs redirect',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_redirect'))
{
	$displayData['createURL'] = 'index.php?option=com_redirect&task=link.add';
}

if ($user->authorise('core.create', 'com_redirect')
	&& $user->authorise('core.edit', 'com_redirect')
	&& $user->authorise('core.edit.state', 'com_redirect'))
{
	$displayData['formAppend'] = HTMLHelper::_(
		'bootstrap.renderModal',
		'collapseModal',
		[
			'title' => Text::_('COM_REDIRECT_BATCH_OPTIONS'),
			'footer' => $this->loadTemplate('batch_footer'),
		],
		$this->loadTemplate('batch_body')
	);
} ?>
<?php if ($this->redirectPluginId) : ?>
	<?php $link = Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->redirectPluginId . '&tmpl=component&layout=modal'); ?>
	<?php echo HTMLHelper::_(
		'bootstrap.renderModal',
		'plugin' . $this->redirectPluginId . 'Modal',
		array(
			'url'         => $link,
			'title'       => Text::_('COM_REDIRECT_EDIT_PLUGIN_SETTINGS'),
			'height'      => '400px',
			'width'       => '800px',
			'bodyHeight'  => '70',
			'modalWidth'  => '80',
			'closeButton' => false,
			'backdrop'    => 'static',
			'keyboard'    => false,
			'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"'
				. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->redirectPluginId . 'Modal\', buttonSelector: \'#closeBtn\'})">'
				. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
				. '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->redirectPluginId . 'Modal\', buttonSelector: \'#saveBtn\'})">'
				. Text::_('JSAVE') . '</button>'
				. '<button type="button" class="btn btn-success" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->redirectPluginId . 'Modal\', buttonSelector: \'#applyBtn\'}); return false;">'
				. Text::_('JAPPLY') . '</button>'
		)
	); ?>
<?php endif; ?>
<?php echo LayoutHelper::render('joomla.content.emptystate', $displayData);
