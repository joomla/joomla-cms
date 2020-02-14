<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

require_once JPATH_PLUGINS. '/system/helixultimate/core/classes/menu.php';

class HelixUltimateFeatureMenu
{

	private $params;

	public function __construct($params)
	{
		$this->params = $params;
		$this->position = 'menu';
	}

	public function renderFeature()
	{

		$menu_type = $this->params->get('menu_type');
		$offcanvs_position = $this->params->get('offcanvas_position', 'right');

		$output = '';

		if($menu_type == 'mega_offcanvas')
		{
			$output .= '<nav class="sp-megamenu-wrapper" role="navigation">';
			if($offcanvs_position == 'right') 
			{
				$output .= '<a id="offcanvas-toggler" aria-label="'. JText::_('HELIX_ULTIMATE_NAVIGATION') .'" class="offcanvas-toggler-right" href="#"><i class="fa fa-bars" aria-hidden="true" title="'. JText::_('HELIX_ULTIMATE_NAVIGATION') .'"></i></a>';
			}
			$menu = new HelixUltimateMenu('d-none d-lg-block','');
			$output .= $menu->render();
			$output .= '</nav>';
		}
		elseif ($menu_type == 'mega')
		{
			$output .= '<nav class="sp-megamenu-wrapper" role="navigation">';
			if($offcanvs_position == 'right') 
			{
				$output .= '<a id="offcanvas-toggler" aria-label="'. JText::_('HELIX_ULTIMATE_NAVIGATION') .'" class="offcanvas-toggler-right d-block d-lg-none" href="#"><i class="fa fa-bars" aria-hidden="true" title="'. JText::_('HELIX_ULTIMATE_NAVIGATION') .'"></i></a>';
			}
			$menu = new HelixUltimateMenu('d-none d-lg-block','');
			$output .= $menu->render();
			$output .= '</nav>';
		} else {
			if($offcanvs_position == 'right') 
			{
				$output .= '<a id="offcanvas-toggler" aria-label="'. JText::_('HELIX_ULTIMATE_NAVIGATION') .'" class="offcanvas-toggler-right" href="#"><i class="fa fa-bars" aria-hidden="true" title="'. JText::_('HELIX_ULTIMATE_NAVIGATION') .'"></i></a>';
			}
		}

		return $output;

	}
}
