<?PHP
/**
 * Dumps templates as HTML
 *
 * $Id: Html.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Dump
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * Dumps templates as HTML
 *
 * @package		patTemplate
 * @subpackage	Dump
 * @author		Sebastian Mordziol <argh@php-tools.net>
 * @author		Stephan Schmidt <schst@php.net>
 *
 * @todo		move this into patTemplate_Dump_Dhtml and keep it free from javascript
 */
class patTemplate_Dump_Html extends patTemplate_Dump
{
	var $colors	=	array(
		'borders'			=>	'C8D3DA',
		'headerFills'		=>	'E1E7EB',
		'subHeaderFills'	=>	'F0F2F4',
		'source'			=>	'F5F7F8',
		'linkNormal'		=>	'8CA0B4',
		'linkHover'			=>	'8BC3E0',
	);

	var $_useBorders	=	false;

   /**
	* display the header
	*
	* @access	public
	*/
	function displayHeader()
	{
		echo '<html>';
		echo ' <head>';
		echo '  <style type="text/css">';
		echo '   BODY,TD{';
		echo '		font-family: Arial, Tahoma, sans-serif;';
		echo '		font-size: 12px;';
		echo '   }';
		echo '   TABLE.patTemplate{';
		echo '	  border-collapse:collapse;';
		echo '   }';
		echo '   A.patTemplate{';
		echo '		color:#'.$this->colors['linkNormal'].';';
		echo '   }';
		echo '   A.patTemplate:hover{';
		echo '		color:#'.$this->colors['linkHover'].';';
		echo '   }';
		echo '   H1.patTemplate{';
		echo '		margin: 0px;';
		echo '		padding: 4px;';
		echo '		font-size: 18px;';
		echo '   }';
		echo '   I.patTemplate{';
		echo '		font-style:italic;';
		echo '		color:#777777;';
		echo '   }';
		echo '   H2.patTemplate{';
		echo '		background-color: #'.$this->colors['headerFills'].';';
		echo '		margin: 0px;';
		echo '		margin-bottom: 15px;';
		echo '		padding: 4px;';
		echo '		border-bottom: 1px dashed #'.$this->colors['borders'].';';
		echo '		border-top: 1px dashed #'.$this->colors['borders'].';';
		echo '		font-size: 14px;';
		echo '   }';
		echo '   H3.patTemplate,H3.patTemplateSub{';
		echo '		background-color: #'.$this->colors['headerFills'].';';
		echo '		margin: 0px;';
		echo '		padding: 4px;';
		echo '		border-bottom: 1px dashed #'.$this->colors['borders'].';';
		echo '		border-top: 1px dashed #'.$this->colors['borders'].';';
		echo '		font-size: 12px;';
		echo '		font-weight: bold;';
		echo '   }';
		echo '   H3.patTemplateSub{';
		echo '		background-color: #'.$this->colors['subHeaderFills'].';';
		echo '		border:1px dashed #'.$this->colors['borders'].';';
		echo '		font-weight:normal;';
		echo '		margin-bottom:3px;';
		echo '   }';
		echo '   #patTemplateContent{';
		echo '		width: 100%;';
		echo '		background-color:#ffffff;';
		echo '		border:dashed 1px #'.$this->colors['borders'].';';
		echo '		padding:0px;';
		echo '   }';
		echo '   .patTemplateSection{';
		echo '	  margin-bottom:20px;';
		echo '   }';
		echo '   .patTemplateSubSection{';
		echo '	  margin-bottom:3px;';
		echo '   }';
		echo '   .patTemplateData{';
		echo '	  display:none;';
		echo '   }';
		echo '   .patTemplatePropBorder{';
		echo '	  white-space:nowrap;';
		echo '		font-weight:bold;';
		echo '	  color:#333333;';
		echo '	  border:solid 1px #C8D3DA;';
		echo '   }';
		echo '   .patTemplateProp{';
		echo '	  white-space:nowrap;';
		echo '		font-weight:bold;';
		echo '	  color:#333333;';
		echo '   }';
		echo '   .patTemplateSign{';
		echo '	  font-family:monospace;';
		echo '   }';
		echo '   .patTemplateClick{';
		echo '	  cursor:pointer;';
		echo '   }';
		echo '   .patTemplateCol{';
		echo '	  margin-bottom:8px;';
		echo '	  font-weight:bold;';
		echo '   }';
		echo '   .patTemplateTblCol{';
		echo '	  padding:8px;';
		echo '	  padding-right:15px;';
		echo '	  border-right:dashed 1px #'.$this->colors['borders'].';';
		echo '   }';
		echo '   .patTemplateTmplContent{';
		echo '	  padding:8px;';
		echo '	  border-top:dashed 1px #'.$this->colors['borders'].';';
		echo '   }';
		echo '   .patTemplateSource{';
		echo '	  padding:5px;';
		echo '	  font-family:monospace;';
		echo '	  background-color:#'.$this->colors['source'].';';
		echo '	  margin-bottom:8px;';
		echo '   }';
		echo '   .patTemplateVar{';
		echo '		color: #009900;';
		echo '	  font-weight:bold;';
		echo '   }';
		echo '   .patTemplateVarBorder{';
		echo '		color: #009900;';
		echo '	  font-weight:bold;';
		echo '	  border:solid 1px #C8D3DA;';
		echo '   }';
		echo '   .patTemplateVal{';
		echo '		color:#333333;';
		echo '   }';
		echo '   .patTemplateValBorder{';
		echo '		color: #333333;';
		echo '	  border:solid 1px #C8D3DA;';
		echo '   }';
		echo '   .patTemplateTmpl {';
		echo '		color: #990000;';
		echo '	  font-weight:bold;';
		echo '	  cursor:pointer;';
		echo '   }';
		echo '  </style>';
		echo ' <script language="JavaScript1.2" type="text/javascript">';
		echo '   var tmpls = new Array();';
		echo '   function patTemplateToggle( tmplName )';
		echo '   {';
		echo '	  var el = document.getElementById( \'tmpl-\' + tmplName );';
		echo '	  if( el.style.display == \'block\' )';
		echo '		patTemplateCollapse( tmplName );';
		echo '	  else';
		echo '		patTemplateExpand( tmplName );';
		echo '   }';
		echo '   function jump( tmplName )';
		echo '   {';
		echo '	  tmplName = tmplName.toLowerCase();';
		echo '	  patTemplateExpand( tmplName );';
		echo '	  document.location = \'#\' + tmplName';
		echo '   }';
		echo '   function patTemplateExpandAll()';
		echo '   {';
		echo '	 	for( var i = 0; i < tmpls.length; i++ )';
		echo '		patTemplateExpand( tmpls[i] );';
		echo '   }';
		echo '   function patTemplateCollapseAll()';
		echo '   {';
		echo '	 	for( var i = 0; i < tmpls.length; i++ )';
		echo '		patTemplateCollapse( tmpls[i] );';
		echo '   }';
		echo '   function patTemplateExpand( tmplName )';
		echo '   {';
		echo '	  var el1 = document.getElementById( \'tmpl-\' + tmplName );';
		echo '	  var el2 = document.getElementById( \'tmpl-\' + tmplName + \'-sign\' );';
		echo '	  el1.style.display = \'block\';';
		echo '	  el2.innerHTML = \'[-]\';';
		echo '   }';
		echo '   function patTemplateCollapse( tmplName )';
		echo '   {';
		echo '	  var el1 = document.getElementById( \'tmpl-\' + tmplName );';
		echo '	  var el2 = document.getElementById( \'tmpl-\' + tmplName + \'-sign\' );';
		echo '	  el1.style.display = \'none\';';
		echo '	  el2.innerHTML = \'[+]\';';
		echo '   }';
		echo '   function patTemplateAddTmpl( tmplName )';
		echo '   {';
		echo '	  tmpls.push( tmplName );';
		echo '   }';
		echo ' </script>';
		echo ' </head>';
		echo '<body>';
		echo ' <div id="patTemplateContent">';
		echo ' <h1 class="patTemplate">patTemplate Dump</h1>';
	}

   /**
	* dump the global variables
	*
	* @access	public
	* @param	array		array containing all global variables
	*/
	function dumpGlobals( $globals )
	{
		echo '<div class="patTemplateSection"><h2 class="patTemplate">Global template variables ('.count( $globals ).')</h2>';
		if( !empty( $globals ) )
		{
			echo '<div class="patTemplateSubSection" style="padding:5px;">';
			echo '  <table border="0" cellpadding="0" cellpadding="0" class="patTemplate">';
			foreach( $globals as $key => $value )
			{
				$this->_displayLine( $key, $value );
			}
			echo '  </table>';
			echo '</div></div>';
		}
	}

   /**
	* dump the templates
	*
	* @access	public
	* @param	array	templates
	*/
	function dumpTemplates( $templates, $vars )
	{
		$templates = array_reverse( $templates );

		echo '<div class="patTemplateSection"><h2 class="patTemplate">Templates ('.count( $templates ).') &nbsp; <span style="font-size:12px;font-weight:normal;"><a href="javascript:patTemplateExpandAll();" class="patTemplate">Expand all</a> | <a href="javascript:patTemplateCollapseAll();" class="patTemplate">Collapse all</a></span></h2>';
		echo '<div class="patTemplateData">Dumping selected templates...</div>';

		foreach( $templates as $name => $tmpl )
		{
			if( !isset( $vars[$name] ) )
				$vars[$name] = array();

			$vars[$name] = $this->_flattenVars( $vars[$name] );

			echo '<div class="patTemplateSubSection">';
			echo '  <h3 class="patTemplate" onclick="patTemplateToggle(\''.$name.'\');" style="cursor:pointer;"><span id="tmpl-'.$name.'-sign" class="patTemplateSign">[+]</span> Template <a name="'.$name.'">"'.$name.'"</a></h3>';
			echo '  <div class="patTemplateData" id="tmpl-'.$name.'">';
			echo '	<script language="JavaScript1.2" type="text/javascript">';
			echo '	  patTemplateAddTmpl( \''.$name.'\' );';
			echo '	</script>';
			echo '	<table cellpadding="0" cellspacing="0" border="0">';
			echo '	  <tr valign="top">';
			echo '		<td style="width:200px;" class="patTemplateTblCol">';

							$this->_displayAttributes( $tmpl, $name );

			echo '		</td>';
			echo '		<td class="patTemplateTblCol" style="width:130px;">';

							$this->_displayTemplateState( $tmpl );

			echo '		</td>';
			echo '		<td class="patTemplateTblCol" style="width:130px;">';
			echo '		  <div class="patTemplateCol">Dependencies</div>';

							if( !empty( $tmpl['dependencies'] ) )
							{
								$dependencies = array();
								foreach( $tmpl['dependencies'] as $dependency )
									array_push( $dependencies, '<a href="javascript:jump( \''.$dependency.'\' );" class="patTemplate">'.$dependency.'</a>' );

								echo implode( '<br>', $dependencies );
							}
							else
							{
								echo '<i class="patTemplate">[none]</i>';
							}

			echo '		</td>';
			echo '	  </tr>';
			echo '	</table>';


			/**
			 * ------------------------------------------------------------------
			 * display variables
			 */
			$nestedTypes = array(
				'condition',
				'modulo',
			);

			if( in_array( $tmpl['attributes']['type'], $nestedTypes ) )
			{
				$content = '';

				foreach( $tmpl['subtemplates'] as $subName => $subDetails )
				{
					$content .= $subDetails['data'];
				}
			}
			else
			{
				$content = $tmpl['content'];
			}

			$setVars = $this->_extractVars( $content );

			echo '<div class="patTemplateTmplContent">';
			echo '  <div class="patTemplateCol">Variables</div>';
					$this->_displayVariables( $tmpl, $name, $setVars, $vars );
			echo '</div>';

			if( !empty( $tmpl['comments'] ) )
			{
				echo '	<div class="patTemplateTmplContent">';
				echo '	  <div class="patTemplateCol">Comments</div>';
				echo '	  <div style="margin-bottom:7px;">';
							  echo implode( '<br>', $tmpl['comments'] );
				echo '	  </div>';
				echo '	</div>';
			}

			/**
			 * ------------------------------------------------------------------
			 * display template content
			 */
			echo '	<div class="patTemplateTmplContent">';
			switch( $tmpl['attributes']['type'] )
			{
				case 'condition':
				case 'modulo':
					$this->_displayConditions( $tmpl, $name, $vars );
					break;

				default:
					echo '	  <div class="patTemplateCol">Content</div>';
					echo '	  <div class="patTemplateSource"><pre>'.$this->_highlightVars( htmlspecialchars( $tmpl['content'] ), $vars[$name] ).'</pre></div>';
					break;
			}

			echo '	</div>';
			echo '  </div>';
			echo '</div>';
		}

		echo '</div>';
	}

	function _displayVariables( $tmpl, $tmplName, $setVars, $vars )
	{
		if( empty( $setVars ) )
		{
			echo '<i class="patTemplate">[none]</i>';
			return true;
		}

		echo '<table border="0" cellpadding="3" cellpadding="0" class="patTemplate">';
		$this->_useBorders( true );
		$this->_displayHead( 'Name', 'Assigned value', 'Modifier' );

		foreach( $setVars as $var )
		{
			if( isset( $vars[$tmplName][$var] ) )
				$value = $vars[$tmplName][$var];
			else
				$value = '<i class="patTemplate">[no value set]</i>';

			if( isset( $tmpl['modifyVars'][$var] ) )
			{
				$params	=	array();
				foreach( $tmpl['modifyVars'][$var]['params'] as $n => $val )
				{
					array_push( $params, $n.'="'.$val.'"' );
				}
				$modifier = sprintf( '%s( %s )', $tmpl['modifyVars'][$var]['mod'], implode( ', ', $params ) );
			}
			else
			{
				$modifier = '<i class="patTemplate">[none]</i>';
			}

			$this->_displayLine( $var, $value, $modifier );
		}
		echo '</table>';

		$this->_useBorders( false );
	}

	function _displayConditions( $tmpl, $tmplName, $vars )
	{
		foreach( $tmpl['subtemplates'] as $cond => $spec )
		{
			echo '  <h3 class="patTemplateSub" onclick="patTemplateToggle(\'Cond'.$cond.'\');" style="cursor:pointer;"><span id="tmpl-Cond'.$cond.'-sign" class="patTemplateSign">[+]</span> Subtemplate <a name="Cond'.$cond.'">"'.$cond.'"</a></h3>';
			echo '  <div class="patTemplateData" id="tmpl-Cond'.$cond.'" style="margin-bottom:15px;">';
			echo '	<script language="JavaScript1.2" type="text/javascript">';
			echo '	  patTemplateAddTmpl( \'Cond'.$cond.'\' );';
			echo '	</script>';

			if( !empty( $spec['comments'] ) )
			{
				echo '	<div class="patTemplateCol">Comments</div>';
				echo '	<div style="margin-bottom:7px;">';
							echo implode( '<br>', $spec['comments'] );
				echo '	</div>';
			}

				echo '	<div class="patTemplateCol">Dependencies</div>';
				echo '	<div style="margin-bottom:7px;">';
			if( !empty( $spec['dependencies'] ) )
			{
				$dependencies = array();
				foreach( $spec['dependencies'] as $dependency )
					array_push( $dependencies, '<a href="javascript:jump( \''.$dependency.'\' );" class="patTemplate">'.$dependency.'</a>' );

				echo implode( ', ', $dependencies );
			}
			else
			{
				echo '<i class="patTemplate">[none]</i>';
			}
				echo '	</div>';

			echo '	<div class="patTemplateCol">Content</div>';
			echo '	<div class="patTemplateSource"><pre>'.$this->_highlightVars( htmlspecialchars( $spec['data'] ), $vars[$tmplName] ).'</pre></div>';
			echo '  </div>';
		}
	}

   /**
	* display the attributes of a template
	*
	* @access   private
	* @param	array	   template data
	* @param	string	  name of the template
	*/
	function _displayAttributes( $tmpl, $tmplName = null )
	{
		echo '<div class="patTemplateCol">Attributes</div>';
		echo '<table border="0" cellpadding="0" cellpadding="0">';

		/**
		 * type
		 */
		switch( $tmpl['attributes']['type'] )
		{
			case 'modulo':
				if( $tmpl['attributes']['modulo'] == 2 )
					$type = 'OddEven';
				else
					$type = 'modulo (' . $tmpl['attributes']['modulo'] . ')';
			case 'condition':
				if( !isset( $type ) )
					$type	=	'condition';

				$this->_displayLine( 'Type', $tmpl['attributes']['type'] );
				/**
				 * condition variable, only used in condition templates
				 */
				if( isset( $tmpl['attributes']['conditionvar'] ) )
				{
					if( isset( $tmpl['attributes']['conditiontmpl'] ) )
						$this->_displayLine( 'ConditionVar', $tmpl['attributes']['conditiontmpl'].'.'.$tmpl['attributes']['conditionvar'] );
					elseif( isset( $tmpl['attributes']['useglobals'] ) && $tmpl['attributes']['useglobals'] == 'yes' )
						$this->_displayLine( 'ConditionVar', '__globals.'.$tmpl['attributes']['conditionvar'] );
					else
						$this->_displayLine( 'ConditionVar', $tmpl['attributes']['conditionvar'] );
				}
				break;

			case 'simplecondition':
				$this->_displayLine( 'Type', 'simplecondition' );
				$requiredvars = array();
				foreach ($tmpl['attributes']['requiredvars'] as $tmp) {
					if ($tmp[0] !== $tmplName) {
						$var = $tmp[0] . '.' . $tmp[1];
					} else {
						$var = $tmp[1];
					}
					if ($tmp[2] !== null) {
						$var = $var . '='.$tmp[2];
					}
					array_push($requiredvars, $var);
				}

				$this->_displayLine( 'RequiredVars', implode( ', ', $requiredvars ) );

				break;
			default:
				$this->_displayLine( 'Type', $tmpl['attributes']['type'] );
		}

		/**
		 * standard attributes
		 */
		$this->_displayLine( 'Visibility', $tmpl['attributes']['visibility'] );
		$this->_displayLine( 'WhiteSpace', $tmpl['attributes']['whitespace'] );
		$this->_displayLine( 'AddSystemVars', $tmpl['attributes']['addsystemvars'] );
		$this->_displayLine( 'UnusedVars', $tmpl['attributes']['unusedvars'] );

		/**
		 * external source
		 */
		if( isset( $tmpl['attributes']['src'] ) )
			$this->_displayLine( 'External Src', $tmpl['attributes']['src'] );
		/**
		 * varscope
		 */
		if (isset($tmpl['attributes']['varscope'])) {
			if (is_array($tmpl['attributes']['varscope'])) {
				$this->_displayLine( 'Varscope', implode(', ', $tmpl['attributes']['varscope'] ) );
			} else {
				$this->_displayLine( 'Varscope', $tmpl['attributes']['varscope'] );
			}
		}

		echo '		  </table>';
	}

	function _displayTemplateState( $tmpl )
	{
			echo '		  <div class="patTemplateCol">States</div>';
			echo '		  <table border="0" cellpadding="0" cellpadding="0">';
							$this->_displayLine( 'Loaded', $tmpl['loaded'] );
							$this->_displayLine( 'Parsed', $tmpl['parsed'] );
			echo '		  </table>';
	}

   /**
	* hilight variables in a template
	*
	* @access	private
	* @param	string		template content
	* @return	string		template content
	*/
	function _highlightVars( $template, $vars )
	{
		$pattern  = '/('.$this->_tmpl->getStartTag().'TMPL\:([^a-z]+)'.$this->_tmpl->getEndTag().')/U';
		$template = preg_replace( $pattern, '<span class="patTemplateTmpl" onclick="jump(\'\2\')" title="Click to view the dependency \'\2\'.">\1</span>', $template );

		$pattern = '/('.$this->_tmpl->getStartTag().'([^a-z:]+)'.$this->_tmpl->getEndTag().')/U';
		$matches = array();
		preg_match_all( $pattern, $template, $matches );
		for( $i = 0; $i < count( $matches[1] ); $i++ )
		{
			if( isset( $vars[$matches[2][$i]] ) )
			{
				$value = $vars[$matches[2][$i]];
			}
			else
			{
				$value = '[No value set]';
			}
			$replace  = '<span class="patTemplateVar" title="'.$value.'">'.$matches[1][$i].'</span>';
			$template = str_replace( $matches[1][$i], $replace, $template );
		}
		return $template;
	}

   /**
	* display a table header
	*
	* @access	private
	* @param	string		property
	* @param	mixed		value, you may pass more than one value
	*/
	function _displayHead()
	{
		$args		=	func_get_args();

		echo '  <tr valign="top">';
		foreach( $args as $head )
		{
			printf( '   <td class="'.$this->_getClassName( 'patTemplateProp' ).'">%s</td>', $head );
		}
		echo '  </tr>';
	}

	function _getClassName( $class )
	{
		if( !$this->_useBorders )
			return $class;

		return $class .= 'Border';
	}

   /**
	* sets whether to draw borders in the tables generated via the
	* {@link _displayHead()} and {@link _displayLine()} methods.
	*
	* @access	private
	* @param	bool		$state	Whether to draw the borders. true=draw, false=don't draw
	*/
	function _useBorders( $state )
	{
		$this->_useBorders = $state;
	}

   /**
	* display a line in a table
	*
	* @access	private
	* @param	string		property
	* @param	mixed		value, you may pass more than one value
	*/
	function _displayLine( $prop, $value )
	{
		$args	=	func_get_args();
		$prop	=	array_shift( $args );

		echo '  <tr valign="top">';
		printf( '   <td class="'.$this->_getClassName( 'patTemplateProp' ).'">%s</td>', $prop );
		if( count( $args ) == 1 )
		{
			echo '   <td class="'.$this->_getClassName( 'patTemplateVal' ).'">&nbsp;:&nbsp;</td>';
		}

		foreach( $args as $value )
		{
			if( is_bool( $value ) )
			{
				$value = ( $value === true ) ? 'yes' : 'no';
			}

			printf( '   <td class="'.$this->_getClassName( 'patTemplateVal' ).'">%s</td>', $value );
		}
		echo '  </tr>';
	}

   /**
	* display the footer
	*
	* @access	public
	*/
	function displayFooter()
	{
		echo ' </div>';
		echo ' </body>';
		echo '</html>';
	}
}
?>