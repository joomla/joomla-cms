<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$text = Text::_('JTOOLBAR_CANCEL');
?>
<joomla-toolbar-button>
	<button onclick="location.href='index.php?option=com_modules&view=modules&client_id=<?php echo $displayData['client_id']; ?>'" class="btn btn-danger">
		<span class="fas fa-times" aria-hidden="true"></span> <?php echo $text; ?>
	</button>
</joomla-toolbar-button>
