<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 */
 
defined('JPATH_PLATFORM') or die;
/**
 * Form Field class for the Joomla Platform.
 * Provides text box with button which opens a drawer for entry of time
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 */
class JFormFieldTime extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type='Time';
	
	/**
	 * The starting hour of options. Valid values:0-23.
	 *
	 * @var    int
	 */
	protected $starthour=0;
	
	/**
	 * The ending hour of options. Valid values:0-23.
	 *
	 * @var    int
	 */
	protected $endhour=23;
	
	/**
	 * Boolean to show or hide minutes drawer.
	 *
	 * @var    boolean
	 */
	protected $showminutes=true;
	
	/**
	 * Number of minute divisions in an hour
	 *
	 * @var    int
	 */
	protected $minutedivisions=12;
	
	/**
	 * True to show clock in 24 hour format.
	 *
	 * @var    boolean
	 */
	protected $military=false;
	
	/**
	 * Event which opens the time drawer. Valid values are 'click', 'hover' and 'mouseover'
	 *
	 * @var    string
	 */
	protected $event='click';
	
	/**
	 * Layout of time drawer. Valid values 'horizontal' or 'vertical'
	 *
	 * @var    string
	 */
	protected $layout='vertical';
	
	/**
	 * Value of opacity of hours drawer. 0:transparent 1:opaque
	 *
	 * @var    float
	 */
	protected $hoursopacity=1;
	
	/**
	 * Value of opacity of minutes drawer. 0:transparent 1:opaque
	 *
	 * @var    float
	 */
	protected $minutesopacity=1;
	
	/**
	 * Callback function for timepick
	 *
	 * @var    string
	 */
	protected $callback;
	
	/**
	 * Boolean to show or hide time pick button. 
	 *
	 * @var    boolean
	 */
	protected $showbutton=true;
	
	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'starthour':
			case 'endhour':
			case 'showminutes':
			case 'minutedivisions':
			case 'military':
			case 'event':
			case 'layout':
			case 'hoursopacity':
			case 'minuteopacity':
			case 'callback':
			case 'showbutton':
				return $this->$name;
		}

		return parent::__get($name);
	}
	
	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'hoursopacity':
			case 'minutesopacity':
				$this->$name = (float) $value;
				break;
			case 'starthour':
			case 'endhour':
			case 'minutedivisions':
				$this->$name = (int) $value;
				break;
			case 'military':
			case 'showminutes':
			case 'showbutton' :
				$this->$name = (boolean) $value;
				break;
			case 'event':
			case 'layout':
			case 'callback':			
				$this->$name = (string) $value;
				break;				
			default:
				parent::__set($name, $value);
		}
	}
	
	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->starthour = isset($this->element['starthour']) ? (int) $this->element['starthour'] : 0;
			$this->endhour = isset($this->element['endhour']) ? (int) $this->element['endhour'] : 23;
			$this->minutedivisions = isset($this->element['minutedivisions']) ? (int) $this->element['minutedivisions'] : 12;
			$this->hoursopacity = isset($this->element['hoursopacity']) ? (float) $this->element['hoursopacity'] : 1;
			$this->minutesopacity = isset($this->element['minutesopacity']) ? (float) $this->element['minutesopacity'] : 1;
			$this->military = (isset($this->element['military']) && strtolower(trim($this->element['military']))=='true') ? true : false;
			$this->showminutes = (isset($this->element['showminutes']) && strtolower(trim($this->element['military']))=='false') ? false : true;
			$this->event = isset($this->element['event']) ? (string) $this->element['event'] : 'click';
			$this->layout = isset($this->element['layout']) ? (string) $this->element['layout'] : 'vertical';
			$this->showbutton = (isset($this->element['showbutton']) && strtolower(trim($this->element['showbutton'])) == 'false') ? false : true;
			$this->callback = isset($this->element['callback']) ? (string) $this->element['callback'] : '';
		}

		return $return;
	}
	
	/**
	 * Method to get the textarea field input markup.
	 * Use the rows and columns attributes to specify the dimensions of the area.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Translate placeholder text
		$hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$hoursopacity = !empty($this->hoursopacity) ? ' hoursopacity:' . $this->hoursopacity : 'hoursopacity:1';
		$minutesopacity = !empty($this->minutesopacity) ? ' minutesopacity:' . $this->minutesopacity : 'minutesopacity:1';
		$starthour = !empty($this->starthour) ? ' starthour:' . $this->starthour : 'starthour:0';
		$endhour = !empty($this->endhour) ? ' endhour:' . $this->endhour : 'endhour:0';
		$minutedivisions = !empty($this->minutedivisions) ? ' minutedivisions:' . $this->minutedivisions : 'minutedivisions:12';
		$event = !empty($this->event) ? ' event:\'' . $this->event . '\'' : 'event:"click"';
		$layout = !empty($this->layout) ? ' layout:\'' . $this->layout . '\'' : 'layout:"vertical"';
		$valuefield = $this->showbutton ? ' valuefield:\'' . str_replace(']','',str_replace('[','',$this->name)). '\'' : '';
		$callback = !empty($this->callback) ? $this->callback : '';
		$military = $this->military ? ' military:true' : ' military:false';
		$showminutes = $this->showminutes ? ' showminutes:true' : ' showminutes:false';
		
		$class    = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$readonly = $this->readonly ? ' readonly' : '';
		$disabled = $this->disabled ? ' disabled' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$hint     = $hint ? ' placeholder="' . $hint . '"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';

		$value = !empty($this->value) ? ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' : '';
		
		// Initialize JavaScript field attributes.
		$opts = '{'.$starthour.','.$endhour.','.$minutedivisions;
		$opts.= ','.$event.','.$layout;
		(!empty($valuefield)) ? $opts.=','.$valuefield : null;
		$opts.= ','.$minutesopacity.','.$hoursopacity;
		$opts.= ','.$military.','.$showminutes.'}';
		
		if(!empty($callback))
			$scr = 'jQuery(document).ready(function(){jQuery("#'.$this->id.'").clockpick('.$opts.','.$callback.');});';
		else
			$scr = 'jQuery(document).ready(function(){jQuery("#'.$this->id.'").clockpick('.$opts.');});';
		
		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);
		
		JHtml::_('script', 'system/clock.js', false, true);
		JHtml::_('stylesheet', 'clock.css',array(),true);
		JFactory::getDocument()->addScriptDeclaration($scr);
		
		if($this->showbutton)
			return "<input type='text' name='".str_replace(']','',str_replace('[','',$this->name))."'".$class.$readonly.$disabled.$required.$hint.$autofocus.$value."/>".'<button type="button" class="btn" id="' . $this->id . '"><i class="icon-clock"></i></button>';
		else
			return "<input type='text' name='".$this->name."'".$class.$readonly.$disabled.$required.$hint.$autofocus.$value." id = '".$this->id."'/>";
	}
}
