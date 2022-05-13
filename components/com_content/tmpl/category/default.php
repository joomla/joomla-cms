<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

$results = Factory::getApplication()->triggerEvent('onContentAfterItems', array('com_content.archive', &$item, &$item->params, 0));
$item->event->afterDisplayItems = trim(implode("\n", $results));

?>
<div class="com-content-category category-list">

<?php
$this->subtemplatename = 'articles';
echo LayoutHelper::render('joomla.content.category_default', $this);
?>
	
<?php echo $afterDisplayItems; ?>

</div>
