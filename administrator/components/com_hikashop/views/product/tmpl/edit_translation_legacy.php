<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('save_translation');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">

<?php
	echo $this->tabs->startPane( 'translations');
		if(!empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				$this->product_name_input = "translation[product_name][".$language_id."]";
				$this->element->product_name = @$translation->product_name->value;
				if(isset($translation->product_name->published)){
					$this->product_name_published = $translation->product_name->published;
					$this->product_name_id = $translation->product_name->id;
				}

				$this->editor->name = 'translation_product_description_'.$language_id;
				$this->element->product_description = @$translation->product_description->value;
				if(isset($translation->product_description->published)){
					$this->product_description_published = $translation->product_description->published;
					$this->product_description_id = $translation->product_description->id;
				}
				if($this->element->product_type=='main'){
					$this->product_url_input = "translation[product_url][".$language_id."]";
					$this->element->product_url = @$translation->product_url->value;
					if(isset($translation->product_url->published)){
						$this->product_url_published = $translation->product_url->published;
						$this->product_url_id = $translation->product_url->id;
					}

					$this->product_meta_description_input = "translation[product_meta_description][".$language_id."]";
					$this->element->product_meta_description = @$translation->product_meta_description->value;
					if(isset($translation->product_meta_description->published)){
						$this->product_meta_description_published = $translation->product_meta_description->published;
						$this->product_meta_description_id = $translation->product_meta_description->id;
					}

					$this->product_keywords_input = "translation[product_keywords][".$language_id."]";
					$this->element->product_keywords = @$translation->product_keywords->value;
					if(isset($translation->product_keywords->published)){
						$this->product_keywords_published = $translation->product_keywords->published;
						$this->product_keywords_id = $translation->product_keywords->id;
					}
					$this->product_page_title_input = "translation[product_page_title][".$language_id."]";
					$this->element->product_page_title = @$translation->product_page_title->value;
					if(isset($translation->product_page_title->published)){
						$this->product_page_titlepublished = $translation->product_page_title->published;
						$this->product_page_title_id = $translation->product_page_title->id;
					}
					$this->product_alias_input = "translation[product_alias][".$language_id."]";
					$this->element->product_alias = @$translation->product_alias->value;
					if(isset($translation->product_alias->published)){
						$this->product_alias_published = $translation->product_alias->published;
						$this->product_alias_id = $translation->product_alias->id;
					}
					$this->product_canonical_input = "translation[product_canonical][".$language_id."]";
					$this->element->product_canonical = @$translation->product_canonical->value;
					if(isset($translation->product_canonical->published)){
						$this->product_canonical_published = $translation->product_canonical->published;
						$this->product_canonical_id = $translation->product_canonical->id;
					}
				}
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
?>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->product_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
