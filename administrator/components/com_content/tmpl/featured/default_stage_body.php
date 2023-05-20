<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <p><?php echo Text::_('COM_CONTENT_CHANGE_STAGE_AMBIGUOUS_TRANSITIONS'); ?></p>
        </div>
        <div class="col-12" id="stageModal-content">
        </div>
    </div>
</div>
