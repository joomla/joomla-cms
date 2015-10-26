<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	echo $this->tabs->startPane( 'translations');
		echo $this->tabs->startPanel(JText::_('MAIN_INFORMATION'), 'main_translation');
			$this->setLayout('normal');
			echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		if(!empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$this->massaction_name_input = "translation[massaction_name][".$language_id."]";
					$this->element->massaction_name = @$translation->massaction_name->value;
					if(isset($translation->massaction_name->published)){
						$this->massaction_name_published = $translation->massaction_name->published;
						$this->massaction_name_id = $translation->massaction_name->id;
					}
					$this->massaction_description_input = "translation[massaction_description][".$language_id."]";
					$this->element->massaction_description = @$translation->massaction_description->value;
					if(isset($translation->massaction_description->published)){
						$this->massaction_description_published = $translation->massaction_description->published;
						$this->massaction_description_id = $translation->massaction_description->id;
					}


					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
