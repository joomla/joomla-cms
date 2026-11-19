<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$text = Text::_('JTOOLBAR_CANCEL');
?>
<joomla-toolbar-button>
    <a href="index.php?option=com_modules&amp;view=modules&amp;client_id=<?php echo (int) $displayData['client_id']; ?>" class="btn btn-danger">
        <span class="icon-times" aria-hidden="true"></span> <?php echo $text; ?>
    </a>
</joomla-toolbar-button>
