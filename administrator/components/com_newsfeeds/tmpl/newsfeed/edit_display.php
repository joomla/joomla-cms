<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Newsfeeds\Administrator\View\Newsfeed\HtmlView $this */

$this->fieldset = 'jbasic';
?>

<fieldset id="fieldset-display" class="options-form">
    <legend><?php echo Text::_('JGLOBAL_FIELDSET_DISPLAY_OPTIONS'); ?></legend>
    <div>
    <?php echo LayoutHelper::render('joomla.edit.fieldset', $this); ?>
    </div>
</fieldset>
