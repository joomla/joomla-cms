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

echo '<dl>';
echo '<dt>'.JText::_( 'Number of Voters' ).'</dt><dd>'.$this->votes[0]->voters.'</dd>';
echo '<dt>'.JText::_( 'First Vote' ).'</dt><dd>'.$this->first_vote.'</dd>';
echo '<dt>'. JText::_( 'Last Vote' ).'</dt><dd>'.$this->last_vote.'</dd>';
echo '</dl>';
$polltitlelevel = $hlevel + 1;

echo '<h'.$polltitlelevel.'>';
echo $this->poll->title;
echo '</h'.$polltitlelevel.'>';

echo '<table class="pollstableborder">';
/* Translation is missing */
echo '<tr><th id="itema" class="td_1">hits</th><th id="itemb" class="td_2">Prozent</th><th id="itemc" class="td_3">Graph</th></tr>';
for ($i=0;$i<count($this->votes);$i++)
{
	$vote=$this->votes[$i];
	echo '<tr><td colspan="3" id="question'.$i.'" class="question">';
	echo $vote->text.'</td></tr>';
    echo '<tr class="sectiontableentry'.$vote->odd.'">';
	echo '<td headers="itema question'.$i.'"  class="td_1">';
	echo $vote->hits;
	echo '</td>';
	echo '<td  headers="itemb question'. $i.'"  class="td_2">';
	echo $vote->percent.'%';
 	echo '</td>';
	echo '<td  headers="itemc question'. $i.'" class="td_3">';
 	echo '<div class="'. $vote->class.'" style="height:'. $vote->barheight .'px;width:'. $vote->percent .'% !important"></div>';
	echo '</td></tr>';
}
echo '</table>';
?>