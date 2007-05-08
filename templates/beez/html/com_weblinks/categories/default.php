<?php
defined('_JEXEC') or die('Restricted access');

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
        $templateParams = new JParameter($content);
} else {
        $templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$hlevel = $templateParams->get('headerLevelComponent', '2');
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');

if ($this->params->get('show_page_title', 1))
{
        echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
        echo $this->params->get('page_title');
        echo '</h' . $ptlevel . '>';
}

echo '<div class="weblinks'. $this->params->get( 'pageclass_sfx' ).'">';

if ($this->params->def('show_comp_description', 1) || $this->params->def('image', -1) != -1)
{
	$wrap='';
	echo '<div class="contentdescription'.$this->params->get( 'pageclass_sfx' ).'">';
	if ($this->params->def('image', -1) != -1)
	{
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo '<img src="images/stories/' . $this->params->get('image') . '" class="image_' . $this->params->get('image_align') . '" />';
	}

	if ($this->params->get('show_comp_description') && $this->params->get('comp_description'))
	{
		echo $this->params->get('comp_description');
	}
	echo $wrap;
	echo '</div>';
}
echo '</div>';

if (count($this->categories))
{
	echo '<ul>';
	foreach ( $this->categories as $category )
	{
    	echo '<li>';
        echo '<a href="'. $category->link.'" class="category'. $this->params->get( 'pageclass_sfx' ).'">';
        echo $category->title;
    	echo '</a>&nbsp;<span class="small">('.$category->numlinks.')</span>';
      	echo '</li>';
	}
	echo '</ul>';
}
