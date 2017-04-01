<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add javascripts
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'com_media/EventBus.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_media/edit.js', array('version' => 'auto', 'relative' => true));

/**
 * @var JForm $form
 */
$form = $this->form;
?>
<form action="#" method="post" name="adminForm" id="media-form" class="form-validate">
<?php
$fieldSets = $form->getFieldsets();

if ($fieldSets)
{
	echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'attrib-' . reset($fieldSets)->name));

	echo JLayoutHelper::render('joomla.edit.params', $this);

	echo JHtml::_('bootstrap.endTabSet');
}
?>
</form>

<span class="image-container">
    <img id="media-edit-file" src="<?php echo $this->fullFilePath ?>"/>
</span>
