<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2010 Webnific. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$strItems = "";

if (count($list)) :
    $strItems = $strLegends = $strLinks = "[";
    foreach ($list as $i=>$item) :
        $strItems .= $item->hits.",";
        $strLegends .= "\"%%.%% - ".$item->title."\",";
        $strLinks .= "\"index.php?option=com_content&task=article.edit&id=".$item->id."\",";
    endforeach;

    // just to trim the last comma
    // is there a better way to do this?
    $strItems = substr($strItems, 0, -1)."]";
    $strLegends = substr($strLegends, 0, -1)."]";
    $strLinks = substr($strLinks, 0, -1)."]";
endif;

?>
    <?php if(!empty($strItems)): ?>

        <script src="templates/minima/js/raphael/g.raphael.piechart.min.js" type="text/javascript" charset="utf-8"></script>        

        <script type="text/javascript" charset="utf-8">
            window.onload = function () {
                var r = Raphael("piechart");
                r.g.txtattr.font = "12px 'Fontin Sans', Fontin-Sans, sans-serif";

                //r.g.text(100, 60, "<?php echo JText::_('MOD_POPULAR'); ?>").attr({"font-size": 20});

                var pie = r.g.piechart(110, 110, 90, <?php echo $strItems; ?>, {legend: <?php echo $strLegends; ?>, legendpos: "east", href: <?php echo $strLinks; ?> });
                pie.hover(function () {
                    this.sector.stop();
                    this.sector.scale(1.1, 1.1, this.cx, this.cy);
                    if (this.label) {
                        this.label[0].stop();
                        this.label[0].scale(1.5);
                        this.label[1].attr({"font-weight": 800});
                    }
                }, function () {
                    this.sector.animate({scale: [1, 1, this.cx, this.cy]}, 500, "bounce");
                    if (this.label) {
                        this.label[0].animate({scale: 1}, 500, "bounce");
                        this.label[1].attr({"font-weight": 400});
                    }
                });

            };
        </script>

        <div id="piechart"></div>

    <?php else: ?>

        <div id="piechart" class="noresults"><p><?php echo JText::_('MOD_POPULAR_NO_MATCHING_RESULTS');?></p></div>

    <?php endif; ?>
