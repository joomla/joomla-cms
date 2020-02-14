
<?php 
defined ('JPATH_BASE') or die();

use Joomla\CMS\Layout\LayoutHelper;
$articles = $displayData['articles'];
$mainItem = $displayData['item'];
$tmpl_params = JFactory::getApplication()->getTemplate(true)->params;
?>
<div class="related-article-list-container">
    <h3 class="related-article-title"> <?php echo $tmpl_params->get('related_article_title'); ?> </h3>
    <?php if( $tmpl_params->get('related_article_view_type') == 'thumb' ): ?> 
    <div class="article-list related-article-list">
        <div class="row">
            <?php foreach( $articles as $item ): ?> 
                <div class="col-md-<?php echo round(12 / $mainItem->params->get('num_columns')); ?>">            
                <?php echo LayoutHelper::render('joomla.content.related_article', $item); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if( $tmpl_params->get('related_article_view_type') == 'list' ): ?> 
        <ul class="article-list related-article-list">
        <?php foreach( $articles as $item ): ?> 
            <li class="related-article-list-item">     
                <?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>
                <?php echo JLayoutHelper::render('joomla.content.info_block.publish_date', array('item' => $item, 'params' => $item->params,'articleView'=>'intro')); ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>