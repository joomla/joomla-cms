<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 * Form Field to display a list of the layouts for a component view from the extension or template overrides.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldComponentLayout extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ComponentLayout';

	/**
	 * Method to get the field input.
	 *
	 * @return	string	The field input.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.

		// Get the client id.
		$clientId = (int) $this->element['client_id'];

		if (empty($clientId) && (!($this->form instanceof JForm))) {
			$clientId = (int) $this->form->getValue('client_id');
		}

		$client	= JApplicationHelper::getClientInfo($clientId);

		// Get the extension.
		$extn = (string) $this->element['extension'];

		if (empty($extn) && ($this->form instanceof JForm)) {
			$extn = $this->form->getValue('extension');
		}

		$extn = preg_replace('#\W#', '', $extn);

		// Get the template.
		$template = (string) $this->element['template'];
		$template = preg_replace('#\W#', '', $template);

		// Get the style.
		if ($this->form instanceof JForm) {
			$template_style_id = $this->form->getValue('template_style_id');
		}

		$template_style_id = preg_replace('#\W#', '', $template_style_id);

		// Get the view.
		$view = (string) $this->element['view'];
		$view = preg_replace('#\W#', '', $view);

		// If a template, extension and view are present build the options.
		if ($extn && $view && $client) {

			// Load language file
			$lang = JFactory::getLanguage();
				$lang->load($extn.'.sys', JPATH_ADMINISTRATOR, null, false, false)
			||	$lang->load($extn.'.sys', JPATH_ADMINISTRATOR.'/components/'.$extn, null, false, false)
			||	$lang->load($extn.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			||	$lang->load($extn.'.sys', JPATH_ADMINISTRATOR.'/components/'.$extn, $lang->getDefault(), false, false);

			// Get the database object and a new query object.
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);

			// Build the query.
			$query->select('e.element, e.name');
			$query->from('#__extensions as e');
			$query->where('e.client_id = '.(int) $clientId);
			$query->where('e.type = '.$db->quote('template'));
			$query->where('e.enabled = 1');

			if ($template) {
				$query->where('e.element = '.$db->quote($template));
			}

			if ($template_style_id) {
				$query->join('LEFT', '#__template_styles as s on s.template=e.element');
				$query->where('s.id='.(int)$template_style_id);
			}

			// Set the query and load the templates.
			$db->setQuery($query);
			$templates = $db->loadObjectList('element');

			// Check for a database error.
			if ($db->getErrorNum()) {
				JError::raiseWarning(500, $db->getErrorMsg());
			}

			// Build the search paths for component layouts.
			$component_path = JPath::clean($client->path.'/components/'.$extn.'/views/'.$view.'/tmpl');

			// Prepare array of component layouts
			$component_layouts = array();

			// Prepare the grouped list
			$groups=array();

			// Add the layout options from the component path.
			if (is_dir($component_path) && ($component_layouts = JFolder::files($component_path, '^[^_]*\.xml$', false, true))) {
				// Create the group for the component
				$groups['_']			= array();
				$groups['_']['id']		= $this->id.'__';
				$groups['_']['text']	= JText::sprintf('JOPTION_FROM_COMPONENT');
				$groups['_']['items']	= array();

				foreach ($component_layouts as $i=>$file)
				{
					// Attempt to load the xml file.
					if (!$xml = simplexml_load_file($file)) {
						unset($component_layouts[$i]);

						continue;
					}

					// Get the help data from the XML file if present.
					if (!$menu = $xml->xpath('layout[1]')) {
						unset($component_layouts[$i]);

						continue;
					}

					$menu = $menu[0];

					// Add an option to the component group
					$value = JFile::stripext(JFile::getName($file));
					$component_layouts[$i] = $value;
					$text = isset($menu['option']) ? JText::_($menu['option']) : (isset($menu['title']) ? JText::_($menu['title']) : $value);
					$groups['_']['items'][]	= JHTML::_('select.option', '_:'.$value, $text);
				}
			}

			// Loop on all templates
			if ($templates) {
				foreach ($templates as $template)
				{
					// Load language file
						$lang->load('tpl_'.$template->element.'.sys', $client->path, null, false, false)
					||	$lang->load('tpl_'.$template->element.'.sys', $client->path.'/templates/'.$template->element, null, false, false)
					||	$lang->load('tpl_'.$template->element.'.sys', $client->path, $lang->getDefault(), false, false)
					||	$lang->load('tpl_'.$template->element.'.sys', $client->path.'/templates/'.$template->element, $lang->getDefault(), false, false);

					$template_path = JPath::clean($client->path.'/templates/'.$template->element.'/html/'.$extn.'/'.$view);

					// Add the layout options from the template path.
					if (is_dir($template_path) && ($files = JFolder::files($template_path, '^[^_]*\.xml$', false, true))) {
						foreach ($files as $i => $file)
						{
							// Remove layout that already exist in component ones
							if (in_array(JFile::stripext(JFile::getName($file)), $component_layouts)) {
								unset($files[$i]);
							}
						}

						if (count($files)) {
							// Create the group for the template
							$groups[$template->name]=array();
							$groups[$template->name]['id']=$this->id.'_'.$template->element;
							$groups[$template->name]['text']=JText::sprintf('JOPTION_FROM_TEMPLATE', $template->name);
							$groups[$template->name]['items']=array();

							foreach ($files as $file)
							{
								// Attempt to load the xml file.
								if (!$xml = simplexml_load_file($file)) {
									continue;
								}

								// Get the help data from the XML file if present.
								if (!$menu = $xml->xpath('layout[1]')) {
									continue;
								}

								$menu = $menu[0];

								// Add an option to the template group
								$value = JFile::stripext(JFile::getName($file));
								$text = isset($menu['option']) ? JText::_($menu['option']) : (isset($menu['title']) ? JText::_($menu['title']) : $value);
								$groups[$template->name]['items'][]	= JHTML::_('select.option', $template->element.':'.$value, $text);
							}
						}
					}
				}
			}

			// Compute attributes for the grouped list
			$attr = 'multiple="multiple"';
			$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

			// Prepare HTML code
			$html = array();

			// Compute the current selected values
			$selected = array();

			if (is_array($this->value)) {
				foreach($this->value as $template=>$value)
				{
					if (!empty($value) && array_key_exists($template, $templates)) {
						$selected[] = $template.':'.$value;
					}
				}
			}

			// Add a grouped list
			$html[] = JHtml::_('select.groupedlist', $groups, '', array('id'=>$this->id, 'group.id'=>'id', 'list.attr'=>$attr, 'list.select'=>$selected));

			// Add input
			if (is_array($this->value)) {
				foreach($this->value as $template=>$value)
				{
					if (!empty($value) && array_key_exists($template, $templates)) {
						// Add a hidden input for the template layout
						$html[] = '<input type="hidden" id="'.$this->id.'_'.$template.'" name="'.$this->name.'['.$template.']" value="'.$value.'" />';
					}
				}
			}

			// Add javascript code for select tag
			$js="window.addEvent('domready', function() {
				document.id('".$this->id."').addEvent('change', function (event) {
					var options=this.getSelected();
					if (options.length<2)
					{
						this.getChildren('optgroup').each(function (group) {
							group.getParent().getSiblings('input#'+group.get('id')).each(function (input) {
								input.dispose();
							});
						});
					}
					for(var i=0;i<options.length;i++) {
						var group=options[i].getParent();
						var value=options[i].value.substr(options[i].value.indexOf(':')+1);
						var template=options[i].value.substr(0,options[i].value.indexOf(':'));
						var inputs=this.getSiblings('input#'+group.get('id'));
						if (inputs.length==0)
						{
							group.getParent().getParent().grab(new Element('input',{'type':'hidden','id':group.get('id'),'value':value,'name':'".$this->name."['+template+']'}));
						}
						else
						{
							if (inputs[0].value!=value) {
								group.getChildren().each(function (option) {
									option.selected=false;
								});
								inputs[0].value=value;
								options[i].selected=true;
								break;
							}
						}
					}
				});
			});";
			JFactory::getDocument()->addScriptDeclaration($js);

			return implode($html);
		}
		else {
			return '';
		}
	}
}
