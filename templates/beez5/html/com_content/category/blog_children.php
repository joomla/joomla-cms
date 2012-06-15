<?php
/**
 * @package                Joomla.Site
 * @subpackage	Templates.beez5
 * @copyright        Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license                GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$app = JFactory::getApplication();
$templateparams =$app->getTemplate(true)->params;

if ($templateparams->get('html5')!=1)
{
        require JPATH_BASE.'/components/com_content/views/category/tmpl/blog_children.php';
        //evtl. ersetzen durch JPATH_COMPONENT.'/views/...'
} else {

$class = ' class="first"';
?>


<?php if (count($this->children[$this->category->id]) > 0) : ?>
        <ul>
        <?php foreach($this->children[$this->category->id] as $id => $child) : ?>
                <?php
                if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) :
                        if (!isset($this->children[$this->category->id][$id + 1])) :
                                $class = ' class="last"';
                        endif;
                ?>
                <li<?php echo $class; ?>>
                        <?php $class = ''; ?>
                        <span class="item-title"><a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($child->id));?>">
                                <?php echo $this->escape($child->title); ?></a>
                        </span>

                       <?php if ($this->params->get('show_subcat_desc') == 1) :?>
                        <?php if ($child->description) : ?>
                                <div class="category-desc">
                                        <?php echo JHtml::_('content.prepare', $child->description, '', 'com_content.category'); ?>
                                </div>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if ( $this->params->get('show_cat_num_articles', 1)) : ?>
                        <dl>
                                <dt>
                                        <?php echo JText::_('COM_CONTENT_NUM_ITEMS') ; ?>
                                </dt>
                                <dd>
                                        <?php echo $child->getNumItems(true); ?>
                                </dd>
                        </dl>
                        <?php endif ; ?>

                        <?php if (count($child->getChildren()) > 0):
                                $this->children[$child->id] = $child->getChildren();
                                $this->category = $child;
                                $this->maxLevel--;
                                if ($this->maxLevel != 0) :
                                        echo $this->loadTemplate('children');
                                endif;
                                $this->category = $child->getParent();
                                $this->maxLevel++;
                        endif; ?>
                </li>
                <?php endif; ?>
        <?php endforeach; ?>
        </ul>
<?php endif;?>

<?php } ?>
