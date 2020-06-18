<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php if ($this->error) : ?>
	<div class="error">
		<?php echo $this->escape($this->error); ?>
	</div>
<?php endif; ?>
