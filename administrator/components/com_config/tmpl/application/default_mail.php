<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/** @var \Joomla\Component\Config\Administrator\View\Application\HtmlView $this */

HTMLHelper::_('form.csrf');
$this->getDocument()->getWebAssetManager()
    ->useScript('webcomponent.field-send-test-mail');

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

// Add strings for JavaScript error translations.
Text::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
Text::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
Text::script('JLIB_JS_AJAX_ERROR_PARSE');
Text::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

// Ajax request data.
$ajaxUri = Route::_('index.php?option=com_config&task=application.sendtestmail&format=json');

$this->name = Text::_('COM_CONFIG_MAIL_SETTINGS');
$this->description = '';
$this->fieldsname = 'mail';
$this->formclass = 'options-form';

?>

<joomla-field-send-test-mail uri="<?php echo $ajaxUri; ?>">
    <?php echo LayoutHelper::render('joomla.content.options_default', $this); ?>

    <button class="btn btn-primary" type="button" id="sendtestmail">
        <span><?php echo Text::_('COM_CONFIG_SENDMAIL_ACTION_BUTTON'); ?></span>
    </button>
</joomla-field-send-test-mail>
