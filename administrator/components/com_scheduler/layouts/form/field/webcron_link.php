<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $id    DOM id of the field.
 * @var  string $label Label of the field.
 * @var  string $name  Name of the input field.
 * @var  string $value Value attribute of the field.
 */

Text::script('ERROR');
Text::script('MESSAGE');
Text::script('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_SUCCESS');
Text::script('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_FAIL');

/** @var CMSApplication $app */
$app = Factory::getApplication();
$wa  = $app->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_scheduler');
$wa->useScript('com_scheduler.scheduler-config');
?>

<div class="input-group">
    <input
        type="text"
        class="form-control"
        name="<?php echo $name; ?>"
        id="<?php echo $id; ?>"
        readonly
        value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
    >
    <button
        class="btn btn-primary"
        type="button"
        id="link-copy"
        title="<?php echo Text::_('COM_SCHEDULER_CONFIG_WEBCRON_LINK_COPY_DESC'); ?>"><?php echo Text::_('JLIB_HTML_BATCH_COPY'); ?>
    </button>
</div>

