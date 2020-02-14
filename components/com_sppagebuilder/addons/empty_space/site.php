<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonEmpty_space extends SppagebuilderAddons{

	public function render() {

		$class  = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';

		return '<div class="sppb-empty-space ' . $class . ' clearfix"></div>';
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$gap = (isset($this->addon->settings->gap) && $this->addon->settings->gap) ? 'height: ' . (int) $this->addon->settings->gap . 'px;': '';

		$css = '';
		if($gap) {
			$css .= $addon_id . ' .sppb-empty-space {';
			$css .= $gap;
			$css .= '}';
		}

		$gap_sm = (isset($this->addon->settings->gap_sm) && $this->addon->settings->gap_sm) ? 'height: ' . (int) $this->addon->settings->gap_sm . 'px;': '';
		if(!empty($gap_sm)){
			$css .= '@media (min-width: 768px) and (max-width: 991px) {';
			$css .= $addon_id . ' .sppb-empty-space {';
				$css .= $gap_sm;
			$css .= '}';
			$css .= '}';
		}

		$gap_xs = (isset($this->addon->settings->gap_xs) && $this->addon->settings->gap_xs) ? 'height: ' . (int) $this->addon->settings->gap_xs . 'px;': '';
		if(!empty($gap_xs)){
			$css .= '@media (max-width: 767px) {';
			$css .= $addon_id . ' .sppb-empty-space {';
				$css .= $gap_xs;
			$css .= '}';
			$css .= '}';
		}

		return $css;
	}

	public static function getTemplate(){
		$output = '
		<style type="text/css">
			#sppb-addon-{{ data.id }} .sppb-empty-space {
				<# if(_.isObject(data.gap)){ #>
					height: {{ data.gap.md }}px;
				<# } else { #>
					height: {{ data.gap }}px;
				<# } #>
			}

			@media (min-width: 768px) and (max-width: 991px) {
				#sppb-addon-{{ data.id }} .sppb-empty-space {
					<# if(_.isObject(data.gap)){ #>
						height: {{ data.gap.sm }}px;
					<# } #>
				}
			}
			@media (max-width: 767px) {
				#sppb-addon-{{ data.id }} .sppb-empty-space {
					<# if(_.isObject(data.gap)){ #>
						height: {{ data.gap.xs }}px;
					<# } #>
				}
			}
		</style>
		<div class="sppb-empty-space sppb-empty-space-edit {{ data.class }} clearfix"></div>
		';

		return $output;
	}

}
