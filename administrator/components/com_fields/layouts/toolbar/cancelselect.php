<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$text = Text::_('JTOOLBAR_CANCEL');
?>
<joomla-toolbar-button>
    <button onclick="location.href='index.php?option=com_fields&view=fields&context=<?php echo htmlspecialchars($displayData['context'], ENT_QUOTES, 'UTF-8'); ?>'" class="btn btn-danger">
        <span class="icon-times" aria-hidden="true"></span> <?php echo $text; ?>
    </button>
</joomla-toolbar-button>
