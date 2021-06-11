<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Initialise related data.


?>
<div style="height:80vh" class="container-popup">
<iframe 
	src=<?php echo Uri::root() . '?tp=1'; ?>
	id="module-position-select" 
	name="module-position-select" 
	title="module-position-select"
	height="100%" width="100%">
</iframe>
</div>