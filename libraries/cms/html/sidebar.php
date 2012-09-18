<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * Utility class to render a list view sidebar
 *
 * NOTE: this is a temporary implementation, still using pre-existing
 * JSubmenuHelper and the layout in
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.7
 */
abstract class JHtmlSidebar
{
  /**
   * Menu entries
   *
   * @var    array
   * @since  3.0
   */
  protected static $entries = array();

  /**
   * Filters
   *
   * @var    array
   * @since  3.0
   */
  protected static $filters = array();

  /**
   * Value for the action attribute of the form.
   *
   * @var    string
   * @since  3.0
   */
  protected static $action = '';

  /**
   * Render the sidebar.
   * Currently, uses data stored in SubMenuHelper, to minimize
   * changes required to existing backend extensions
   *
   * @return  string  The necessary HTML to display the sidebar
   *
   * @since   1.7
   */
  public static function render()
  {

    // collect display data from JSubMenuHelper
    $data = new stdClass();
    $data->list = self::getEntries();
    $data->filters = self::getFilters();
    $data->action = self::getAction();
    $data->displayMenu = count($data->list);
    $data->displayFilters = count($data->filters);
    $data->hide = JFactory::getApplication()->input->getBool('hidemainmenu');

    // create a layout object and ask it to render the sidebar
    $layout = new JLayoutSidebar;
    $sidebarHtml = $layout->render( $data);
     
    return $sidebarHtml;
  }

  /**
   * Method to add a menu item to submenu.
   *
   * @param	string	$name	 Name of the menu item.
   * @param	string	$link	 URL of the menu item.
   * @param	bool	$active  True if the item is active, false otherwise.
   *
   * @return  void
   *
   * @since   1.5
   */
  public static function addEntry($name, $link = '', $active = false)
  {
    array_push(self::$entries, array($name, $link, $active));
  }

  /**
   * Returns an array of all submenu entries
   *
   * @return  array
   *
   * @since   3.0
   */
  public static function getEntries()
  {
    return self::$entries;
  }

  /**
   * Method to add a filter to the submenu
   *
   * @param	string	$label      Label for the menu item.
   * @param	string	$name       name for the filter. Also used as id.
   * @param	string	$options    options for the select field.
   * @param	bool	$noDefault  Don't the label as the empty option
   *
   * @return  void
   *
   * @since   3.0
   */
  public static function addFilter($label, $name, $options, $noDefault = false)
  {
    array_push(self::$filters, array('label' => $label, 'name' => $name, 'options' => $options, 'noDefault' => $noDefault));
  }

  /**
   * Returns an array of all filters
   *
   * @return  array
   *
   * @since   3.0
   */
  public static function getFilters()
  {
    return self::$filters;
  }

  /**
   * Set value for the action attribute of the filter form
   *
   * @return  void
   *
   * @since   3.0
   */
  public static function setAction($action)
  {
    self::$action = $action;
  }

  /**
   * Get value for the action attribute of the filter form
   *
   * @return  string
   *
   * @since   3.0
   */
  public static function getAction()
  {
    return self::$action;
  }

}

/**
 *
 * @author yannick
 *
 */
interface JLayout {

  public function render( $displayData);
  
}

/**
 *
 * @author yannick
 *
 */
class JLayoutFile implements JLayout {

  protected $path = '';

  public function __construct( $path) {
    $this->path = $path;
  }

  public function render( $displayData) {

    $layoutOutput = '';
    if(!empty($this->path) && file_exists( $this->path)) {
      ob_start();
      include $this->path;
      $layoutOutput = ob_get_contents();
      ob_end_clean();
    }

    return $layoutOutput;
  }
}

/**
 *
 * @author yannick
 *
 */
class JLayoutSidebar implements JLayout
{
  /**
   * Render a layout
   *
   * @return  string  The necessary HTML to display the sidebar
   *
   * @since   1.7
   */
  public function render( $displayData)
  {

    ob_start();

    ?>
      <div id="sidebar">
      	<div class="sidebar-nav">
      		<?php if ($displayData->displayMenu) : ?>
      		<ul id="submenu" class="nav nav-list">
      			<?php foreach ($displayData->list as $item) : ?>
      			<?php if (isset ($item[2]) && $item[2] == 1) :
      				?><li class="active"><?php
      			else :
      				?><li><?php
      			endif;
      			?>
      			<?php
      			if ($displayData->hide) :
      					?><a class="nolink"><?php echo $item[0]; ?><?php
      			else :
      				if(strlen($item[1])) :
      					?><a href="<?php echo JFilterOutput::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a><?php
      				else :
      					?><?php echo $item[0]; ?><?php
      				endif;
      			endif;
      			?>
      			</li>
      			<?php endforeach; ?>
      		</ul>
      		<?php endif; ?>
      		<?php if ($displayData->displayMenu && $displayData->displayFilters) : ?>
      		<hr />
      		<?php endif; ?>
      		<?php if ($displayData->displayFilters) : ?>
      		<div class="filter-select hidden-phone">
      			<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
      				<?php foreach ($displayData->filters as $filter) : ?>
      					<label for="<?php echo $filter['name']; ?>" class="element-invisible"><?php echo $filter['label']; ?></label>
      					<select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" class="span12 small" onchange="this.form.submit()">
      						<?php if (!$filter['noDefault']) : ?>
      							<option value=""><?php echo $filter['label']; ?></option>
      						<?php endif; ?>
      						<?php echo $filter['options']; ?>
      					</select>
      					<hr class="hr-condensed" />
      				<?php endforeach; ?>
      		</div>
      		<?php endif; ?>
      	</div>
      </div>
      
    <?php     
      
    $sidebarHtml = ob_get_contents();
    ob_end_clean();
     
    return $sidebarHtml;

  }


}
