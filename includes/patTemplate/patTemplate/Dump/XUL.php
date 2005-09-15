<?PHP
/**
 * Dumps templates as XUL
 *
 * $Id: XUL.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Dump
 * @author		Stephan Schmidt <schst@php.net>
 */

require_once 'XML/XUL.php';

/**
 * Dumps templates as XUL, using PEAR::XML_XUL
 *
 * @package		patTemplate
 * @subpackage	Dump
 * @author		Stephan Schmidt <schst@php.net>
 *
 * @todo		move this into patTemplate_Dump_Dhtml and keep it free from javascript
 */
class patTemplate_Dump_XUL extends patTemplate_Dump
{
	var $_doc = null;

	var $_root = null;

	var $_templates = null;

	var $_addedTemplates = array();
	var $_vars = array();

   /**
	* display the header
	*
	* @access	public
	*/
	function displayHeader()
	{
		$this->_addedTemplates = array();

		$this->_doc = &XML_XUL::createDocument( );

		$this->_doc->addStylesheet('chrome://global/skin/');

		$win = &$this->_doc->createElement('Window', array('title'=> 'patTemplate Dump'));
		$this->_doc->addRoot($win);

		$this->_root = &$this->_doc->createElement( 'Tabbox', array('flex' => 1) );
		$win->appendChild($this->_root);

	}

   /**
	* dump the global variables
	*
	* @access	public
	* @param	array		array containing all global variables
	*/
	function dumpGlobals( $globals )
	{
		$gbox = &$this->_doc->createElement('Groupbox', array('orient'=>'vertical', 'flex' => 1));
		$gbox->setCaption('Global variables');

		$grid = &$this->_doc->createElement('Grid');
		$grid->setColumns(2, array( 'flex' => 1 ), array( 'flex' => 1 ));

		$gbox->appendChild($grid);

		$headers = array(
						   $this->_doc->createElement( 'Description', array( 'style' => 'font-weight:bold;' ), 'Variable' ),
						   $this->_doc->createElement( 'Description', array( 'style' => 'font-weight:bold;' ), 'Value' ),
				);
		$grid->addRow($headers);
		foreach ($globals as $var => $value) {
			$row = array($var, $value);
			$grid->addRow($row);
		}
		$this->_root->addTab('Global Variables', $gbox);

	}

   /**
	* dump the templates
	*
	* @access	public
	* @param	array	templates
	*/
	function dumpTemplates( $templates, $vars )
	{
		$container = &$this->_doc->createElement('VBox', array('flex' => 1));

		$gbox = &$this->_doc->createElement('Groupbox', array('orient'=>'vertical', 'flex' => '2'));
		$gbox->setCaption('Templates');
		$container->appendChild($gbox);

		$this->_templates = $templates;
		$this->_vars = $vars;

		$templates = array_reverse( $templates );

		$tree = &$this->_doc->createElement( 'Tree', array( 'flex' => 1, 'enableColumnDrag' => 'true', 'height' => '500' ) );
		$tree->setColumns( 5,
						array(
								'id'  => 'name',
								'label' => 'Name',
								'flex'  => 2,
								'primary' => 'true',
							  ),
						array(
								'id'  => 'value',
								'label' => 'Value',
								'flex'  => 1,
							  ),
						array(
								'id'  => 'type',
								'label' => 'Type',
								'flex'  => 1,
							  ),
						array(
								'id'  => 'visibility',
								'label' => 'Visibility',
								'flex'  => 1,
							  ),
						array(
								'id'  => 'loaded',
								'label' => 'Loaded',
								'flex'  => 1,
							  )
				 );

		foreach( $templates as $name => $tmpl )
		{
			if (in_array($name, $this->_addedTemplates)) {
				continue;
			}
			$this->_addToTree($name, $tree);
		}

		$gbox->appendChild($tree);

		$splitter = &$this->_doc->createElement('Splitter');
		$splitter->useGrippy();

		$container->appendChild($splitter);

		$gbox2 = &$this->_doc->createElement('Groupbox', array('orient'=>'vertical', 'flex' => '2'));
		$gbox2->setCaption('Details');

		$container->appendChild($gbox2);

		$deck = &$this->_doc->createElement('Deck');

		$gbox2->appendChild($deck);


		$this->_root->addTab('Templates', $container);
		return true;
	}

	function _addToTree($name, &$tree)
	{
		$tmpl	= $this->_getTemplate($name);
		$item	= array(
							$name,
							'',
							$tmpl['attributes']['type'],
							$tmpl['attributes']['visibility'],
							$tmpl['loaded'] ? 'yes' : 'no',
						);
		$current = &$tree->addItem($item);
		array_push($this->_addedTemplates, $name);
		if (!empty($tmpl['dependencies'])) {
			$deps = &$current->addItem(array( 'Dependencies' ));
			foreach ($tmpl['dependencies'] as $dependency) {
				$this->_addToTree($dependency, $deps);
			}
		}

		if (!isset($this->_vars[$name])) {
			$this->_vars[$name] = array();
		}
		$vars = $this->_flattenVars( $this->_vars[$name] );

		if (empty($vars)) {
			return true;
		}
		$varItem = &$current->addItem(array( 'Variables' ));
		foreach ($vars as $key => $value) {
			$varItem->addItem(array($key, $value));
		}
	}

	function _getTemplate($name)
	{
		if (isset($this->_templates[$name])) {
			return $this->_templates[$name];
		}
	}

   /**
	* display the footer
	*
	* @access	public
	*/
	function displayFooter()
	{
		if ($_GET['mode'] == 'debug') {
			require_once 'XML/Beautifier.php';
			$fmt = &new XML_Beautifier( array( 'indent' => '  ' ) );
			echo '<pre>';
			echo htmlspecialchars( $fmt->formatString($this->_doc->serialize()) );
			echo '</pre>';
		} elseif ($_GET['mode'] == 'source') {
			highlight_file( __FILE__ );
		} elseif ($_GET['mode'] == 'debug2') {
			echo '<pre>';
			echo htmlspecialchars( $this->_doc->getDebug());
			echo '</pre>';
		} elseif ($_GET['mode'] == 'source') {		} else {
			$this->_doc->send();
		}
	}
}
?>