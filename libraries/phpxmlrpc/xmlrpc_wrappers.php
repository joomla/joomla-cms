<?php
/**
 * PHP-XMLRPC "wrapper" functions
 * Generate stubs to transparently access xmlrpc methods as php functions and viceversa
 *
 * @version $Id: xmlrpc_wrappers.inc,v 1.10 2006/09/01 21:49:19 ggiunta Exp $
 * @copyright G. Giunta (C) 2006
 * @author Gaetano Giunta
 *
 * @todo separate introspection from code generation for func-2-method wrapping
 * @todo use some better templating system from code generation?
 * @todo implement method wrapping with preservation of php objs in calls
 * @todo when wrapping methods without obj rebuilding, use return_type = 'phpvals' (faster)
 * @todo implement self-parsing of php code for PHP <= 4
 */

	// requires: xmlrpc.inc

	/**
	* Given a string defining a php type or phpxmlrpc type (loosely defined: strings
	* accepted come from javadoc blocks), return corresponding phpxmlrpc type.
	* NB: for php 'resource' types returns empty string, since resources cannot be serialized;
	* for php class names returns 'struct', since php objects can be serialized as xmlrpc structs
	* @param string $phptype
	* @return string
	*/
	function php_2_xmlrpc_type($phptype)
	{
		switch(strtolower($phptype))
		{
			case 'string':
				return $GLOBALS['xmlrpcString'];
			case 'integer':
			case $GLOBALS['xmlrpcInt']: // 'int'
			case $GLOBALS['xmlrpcI4']:
				return $GLOBALS['xmlrpcInt'];
			case 'double':
				return $GLOBALS['xmlrpcDouble'];
			case 'boolean':
				return $GLOBALS['xmlrpcBoolean'];
			case 'array':
				return $GLOBALS['xmlrpcArray'];
			case 'object':
				return $GLOBALS['xmlrpcStruct'];
			case $GLOBALS['xmlrpcBase64']:
			case $GLOBALS['xmlrpcStruct']:
				return strtolower($phptype);
			case 'resource':
				return '';
			default:
				if(class_exists($phptype))
				{
					return $GLOBALS['xmlrpcStruct'];
				}
				else
				{
					// unknown: might be any 'extended' xmlrpc type
					return $GLOBALS['xmlrpcValue'];
				}
		}
	}

	/**
	* Given a string defining a phpxmlrpc type return corresponding php type.
	* @param string $xmlrpctype
	* @return string
	*/
	function xmlrpc_2_php_type($xmlrpctype)
	{
		switch(strtolower($xmlrpctype))
		{
			case 'base64':
			case 'datetime.iso8601':
			case 'string':
				return $GLOBALS['xmlrpcString'];
			case 'int':
			case 'i4':
				return 'integer';
			case 'struct':
			case 'array':
				return 'array';
			case 'double':
				return 'float';
			case 'undefined':
				return 'mixed';
			case 'boolean':
			case 'null':
			default:
				// unknown: might be any xmlrpc type
				return strtolower($xmlrpctype);
		}
	}

	/**
	* Given a user-defined PHP function, create a PHP 'wrapper' function that can
	* be exposed as xmlrpc method from an xmlrpc_server object and called from remote
	* clients (as well as its corresponding signature info).
	*
	* Since php is a typeless language, to infer types of input and output parameters,
	* it relies on parsing the javadoc-style comment block associated with the given
	* function. Usage of xmlrpc native types (such as datetime.dateTime.iso8601 and base64)
	* in the @param tag is also allowed, if you need the php function to receive/send
	* data in that particular format (note that base64 encoding/decoding is transparently
	* carried out by the lib, while datetime vals are passed around as strings)
	*
	* Known limitations:
	* - requires PHP 5.0.3 +
	* - only works for user-defined functions, not for PHP internal functions
	*   (reflection does not support retrieving number/type of params for those)
	* - functions returning php objects will generate special xmlrpc responses:
	*   when the xmlrpc decoding of those responses is carried out by this same lib, using
	*   the appropriate param in php_xmlrpc_decode, the php objects will be rebuilt.
	*   In short: php objects can be serialized, too (except for their resource members),
	*   using this function.
	*   Other libs might choke on the very same xml that will be generated in this case
	*   (i.e. it has a nonstandard attribute on struct element tags)
	* - usage of javadoc @param tags using param names in a different order from the
	*   function prototype is not considered valid (to be fixed?)
	*
	* Note that since rel. 2.0RC3 the preferred method to have the server call 'standard'
	* php functions (ie. functions not expecting a single xmlrpcmsg obj as parameter)
	* is by making use of the functions_parameters_type class member.
	*
	* @param string $funcname the name of the PHP user function to be exposed as xmlrpc method; array($obj, 'methodname') might be ok too, in the future...
	* @param string $newfuncname (optional) name for function to be created
	* @param array $extra_options (optional) array of options for conversion. valid values include:
	*        bool  return_source when true, php code w. function definition will be returned, not evaluated
	*        bool  encode_php_objs let php objects be sent to server using the 'improved' xmlrpc notation, so server can deserialize them as php objects
	*        bool  decode_php_objs --- WARNING !!! possible security hazard. only use it with trusted servers ---
	*        bool  suppress_warnings  remove from produced xml any runtime warnings due to the php function being invoked
	* @return false on error, or an array containing the name of the new php function,
	*         its signature and docs, to be used in the server dispatch map
	*
	* @todo decide how to deal with params passed by ref: bomb out or allow?
	* @todo finish using javadoc info to build method sig if all params are named but out of order
	* @todo add a check for params of 'resource' type
	* @todo add some trigger_errors / error_log when returning false?
	* @todo what to do when the PHP function returns NULL? we are currently an empty string value...
	* @todo add an option to suppress php warnings in invocation of user function, similar to server debug level 3?
	*/
	function wrap_php_function($funcname, $newfuncname='', $extra_options=array())
	{
		$buildit = isset($extra_options['return_source']) ? !($extra_options['return_source']) : true;
		$prefix = isset($extra_options['prefix']) ? $extra_options['prefix'] : 'xmlrpc';
		$encode_php_objects = isset($extra_options['encode_php_objs']) ? (bool)$extra_options['encode_php_objs'] : false;
		$decode_php_objects = isset($extra_options['decode_php_objs']) ? (bool)$extra_options['decode_php_objs'] : false;
		$catch_warnings = isset($extra_options['suppress_warnings']) && $extra_options['suppress_warnings'] ? '@' : '';

		if(version_compare(phpversion(), '5.0.3') == -1)
		{
			// up to php 5.0.3 some useful reflection methods were missing
			error_log('XML-RPC: cannot not wrap php functions unless running php version bigger than 5.0.3');
			return false;
		}
		if((is_array($funcname) && !method_exists($funcname[0], $funcname[1])) || !function_exists($funcname))
		{
			error_log('XML-RPC: function to be wrapped is not defined: '.$funcname);
			return false;
		}
		else
		{
			// determine name of new php function
			if($newfuncname == '')
			{
				if(is_array($funcname))
				{
					$xmlrpcfuncname = "{$prefix}_".implode('_', $funcname);
				}
				else
				{
					$xmlrpcfuncname = "{$prefix}_$funcname";
				}
			}
			else
			{
				$xmlrpcfuncname = $newfuncname;
			}
			while($buildit && function_exists($xmlrpcfuncname))
			{
				$xmlrpcfuncname .= 'x';
			}

			// start to introspect PHP code
			$func =& new ReflectionFunction($funcname);
			if($func->isInternal())
			{
				// Note: from PHP 5.1.0 onward, we will possibly be able to use invokeargs
				// instead of getparameters to fully reflect internal php functions ?
				error_log('XML-RPC: function to be wrapped is internal: '.$funcname);
				return false;
			}

			// retrieve parameter names, types and description from javadoc comments

			// function description
			$desc = '';
			// type of return val: by default 'any'
			$returns = $GLOBALS['xmlrpcValue'];
			// desc of return val
			$returnsDocs = '';
			// type + name of function parameters
			$paramDocs = array();

			$docs = $func->getDocComment();
			if($docs != '')
			{
				$docs = explode("\n", $docs);
				$i = 0;
				foreach($docs as $doc)
				{
					$doc = trim($doc, " \r\t/*");
					if(strlen($doc) && strpos($doc, '@') !== 0 && !$i)
					{
						if($desc)
						{
							$desc .= "\n";
						}
						$desc .= $doc;
					}
					elseif(strpos($doc, '@param') === 0)
					{
						// syntax: @param type [$name] desc
						if(preg_match('/@param\s+(\S+)(\s+\$\S+)?\s+(.+)/', $doc, $matches))
						{
							if(strpos($matches[1], '|'))
							{
								//$paramDocs[$i]['type'] = explode('|', $matches[1]);
								$paramDocs[$i]['type'] = 'mixed';
							}
							else
							{
								$paramDocs[$i]['type'] = $matches[1];
							}
							$paramDocs[$i]['name'] = trim($matches[2]);
							$paramDocs[$i]['doc'] = $matches[3];
						}
						$i++;
					}
					elseif(strpos($doc, '@return') === 0)
					{
						// syntax: @return type desc
						//$returns = preg_split('/\s+/', $doc);
						if(preg_match('/@return\s+(\S+)\s+(.+)/', $doc, $matches))
						{
							$returns = php_2_xmlrpc_type($matches[1]);
							if(isset($matches[2]))
							{
								$returnsDocs = $matches[2];
							}
						}
					}
				}
			}

			// execute introspection of actual function prototype
			$params = array();
			$i = 0;
			foreach($func->getParameters() as $paramobj)
			{
				$params[$i] = array();
				$params[$i]['name'] = '$'.$paramobj->getName();
				$params[$i]['isoptional'] = $paramobj->isOptional();
				$i++;
			}


			// start  building of PHP code to be eval'd
			$innercode = '';
			$i = 0;
			$parsvariations = array();
			$pars = array();
			$pnum = count($params);
			foreach($params as $param)
			{
				if (isset($paramDocs[$i]['name']) && $paramDocs[$i]['name'] && strtolower($paramDocs[$i]['name']) != strtolower($param['name']))
				{
					// param name from phpdoc info does not match param definition!
					$paramDocs[$i]['type'] = 'mixed';
				}

				if($param['isoptional'])
				{
					// this particular parameter is optional. save as valid previous list of parameters
					$innercode .= "if (\$paramcount > $i) {\n";
					$parsvariations[] = $pars;
				}
				$innercode .= "\$p$i = \$msg->getParam($i);\n";
				if ($decode_php_objects)
				{
					$innercode .= "if (\$p{$i}->kindOf() == 'scalar') \$p$i = \$p{$i}->scalarval(); else \$p$i = php_{$prefix}_decode(\$p$i, array('decode_php_objs'));\n";
				}
				else
				{
					$innercode .= "if (\$p{$i}->kindOf() == 'scalar') \$p$i = \$p{$i}->scalarval(); else \$p$i = php_{$prefix}_decode(\$p$i);\n";
				}

				$pars[] = "\$p$i";
				$i++;
				if($param['isoptional'])
				{
					$innercode .= "}\n";
				}
				if($i == $pnum)
				{
					// last allowed parameters combination
					$parsvariations[] = $pars;
				}
			}

			$sigs = array();
			$psigs = array();
			if(count($parsvariations) == 0)
			{
				// only known good synopsis = no parameters
				$parsvariations[] = array();
				$minpars = 0;
			}
			else
			{
				$minpars = count($parsvariations[0]);
			}

			if($minpars)
			{
				// add to code the check for min params number
				// NB: this check needs to be done BEFORE decoding param values
				$innercode = "\$paramcount = \$msg->getNumParams();\n" .
				"if (\$paramcount < $minpars) return new {$prefix}resp(0, {$GLOBALS['xmlrpcerr']['incorrect_params']}, '{$GLOBALS['xmlrpcstr']['incorrect_params']}');\n" . $innercode;
			}
			else
			{
				$innercode = "\$paramcount = \$msg->getNumParams();\n" . $innercode;
			}

			$innercode .= "\$np = false;\n";
			foreach($parsvariations as $pars)
			{
				$innercode .= "if (\$paramcount == " . count($pars) . ") \$retval = {$catch_warnings}$funcname(" . implode(',', $pars) . "); else\n";
				// build a 'generic' signature (only use an appropriate return type)
				$sig = array($returns);
				$psig = array($returnsDocs);
				for($i=0; $i < count($pars); $i++)
				{
					if (isset($paramDocs[$i]['type']))
					{
						$sig[] = php_2_xmlrpc_type($paramDocs[$i]['type']);
					}
					else
					{
						$sig[] = $GLOBALS['xmlrpcValue'];
					}
					$psig[] = isset($paramDocs[$i]['doc']) ? $paramDocs[$i]['doc'] : '';
				}
				$sigs[] = $sig;
				$psigs[] = $psig;
			}
			$innercode .= "\$np = true;\n";
			$innercode .= "if (\$np) return new {$prefix}resp(0, {$GLOBALS['xmlrpcerr']['incorrect_params']}, '{$GLOBALS['xmlrpcstr']['incorrect_params']}'); else {\n";
			//$innercode .= "if (\$_xmlrpcs_error_occurred) return new xmlrpcresp(0, $GLOBALS['xmlrpcerr']user, \$_xmlrpcs_error_occurred); else\n";
			$innercode .= "if (is_a(\$retval, '{$prefix}resp')) return \$retval; else\n";
			if($returns == $GLOBALS['xmlrpcDateTime'] || $returns == $GLOBALS['xmlrpcBase64'])
			{
				$innercode .= "return new {$prefix}resp(new {$prefix}val(\$retval, '$returns'));";
			}
			else
			{
				if ($encode_php_objects)
					$innercode .= "return new {$prefix}resp(php_{$prefix}_encode(\$retval, array('encode_php_objs')));\n";
				else
					$innercode .= "return new {$prefix}resp(php_{$prefix}_encode(\$retval));\n";
			}
			// shall we exclude functions returning by ref?
			// if($func->returnsReference())
			// 	return false;
			$code = "function $xmlrpcfuncname(\$msg) {\n" . $innercode . "}\n}";
			//print_r($code);
			if ($buildit)
			{
				$allOK = 0;
				eval($code.'$allOK=1;');
				// alternative
				//$xmlrpcfuncname = create_function('$m', $innercode);

				if(!$allOK)
				{
					error_log('XML-RPC: could not create function '.$xmlrpcfuncname.' to wrap php function '.$funcname);
					return false;
				}
			}

			/// @todo examine if $paramDocs matches $parsvariations and build array for
			/// usage as method signature, plus put together a nice string for docs

			$ret = array('function' => $xmlrpcfuncname, 'signature' => $sigs, 'docstring' => $desc, 'signature_docs' => $psigs, 'source' => $code);
			return $ret;
		}
	}

	/**
	* Given an xmlrpc client and a method name, register a php wrapper function
	* that will call it and return results using native php types for both
	* params and results. The generated php function will return an xmlrpcresp
	* oject for failed xmlrpc calls
	*
	* Known limitations:
	* - server must support system.methodsignature for the wanted xmlrpc method
	* - for methods that expose many signatures, only one can be picked (we
	*   could in priciple check if signatures differ only by number of params
	*   and not by type, but it would be more complication than we can spare time)
	* - nested xmlrpc params: the caller of the generated php function has to
	*   encode on its own the params passed to the php function if these are structs
	*   or arrays whose (sub)members include values of type datetime or base64
	*
	* Notes: the connection properties of the given client will be copied
	* and reused for the connection used during the call to the generated
	* php function.
	* Calling the generated php function 'might' be slow: a new xmlrpc client
	* is created on every invocation and an xmlrpc-connection opened+closed.
	* An extra 'debug' param is appended to param list of xmlrpc method, useful
	* for debugging purposes.
	*
	* @param xmlrpc_client $client     an xmlrpc client set up correctly to communicate with target server
	* @param string        $methodname the xmlrpc method to be mapped to a php function
	* @param array         $extra_options array of options that specify conversion details. valid ptions include
	*        integer       signum      the index of the method signature to use in mapping (if method exposes many sigs)
	*        integer       timeout     timeout (in secs) to be used when executing function/calling remote method
	*        string        protocol    'http' (default), 'http11' or 'https'
	*        string        new_function_name the name of php function to create. If unsepcified, lib will pick an appropriate name
	*        string        return_source if true return php code w. function definition instead fo function name
	*        bool          encode_php_objs let php objects be sent to server using the 'improved' xmlrpc notation, so server can deserialize them as php objects
	*        bool          decode_php_objs --- WARNING !!! possible security hazard. only use it with trusted servers ---
	*        mixed         return_on_fault a php value to be returned when the xmlrpc call fails/returns a fault response (by default the xmlrpcresp object is returned in this case). If a string is used, '%faultCode%' and '%faultString%' tokens will be substituted with actual error values
	*        bool          debug        set it to 1 or 2 to see debug results of querying server for method synopsis
	* @return string                   the name of the generated php function (or false) - OR AN ARRAY...
	*/
	function wrap_xmlrpc_method($client, $methodname, $extra_options=0, $timeout=0, $protocol='', $newfuncname='')
	{
		// mind numbing: let caller use sane calling convention (as per javadoc, 3 params),
		// OR the 2.0 calling convention (no ptions) - we really love backward compat, don't we?
		if (!is_array($extra_options))
		{
			$signum = $extra_options;
			$extra_options = array();
		}
		else
		{
			$signum = isset($extra_options['signum']) ? (int)$extra_options['signum'] : 0;
			$timeout = isset($extra_options['timeout']) ? (int)$extra_options['timeout'] : 0;
			$protocol = isset($extra_options['protocol']) ? $extra_options['protocol'] : '';
			$newfuncname = isset($extra_options['new_function_name']) ? $extra_options['new_function_name'] : '';
		}
		//$encode_php_objects = in_array('encode_php_objects', $extra_options);
		//$verbatim_client_copy = in_array('simple_client_copy', $extra_options) ? 1 :
		//	in_array('build_class_code', $extra_options) ? 2 : 0;

		$encode_php_objects = isset($extra_options['encode_php_objs']) ? (bool)$extra_options['encode_php_objs'] : false;
		$decode_php_objects = isset($extra_options['decode_php_objs']) ? (bool)$extra_options['decode_php_objs'] : false;
		$simple_client_copy = isset($extra_options['simple_client_copy']) ? (int)($extra_options['simple_client_copy']) : 0;
		$buildit = isset($extra_options['return_source']) ? !($extra_options['return_source']) : true;
		$prefix = isset($extra_options['prefix']) ? $extra_options['prefix'] : 'xmlrpc';
		if (isset($extra_options['return_on_fault']))
		{
			$decode_fault = true;
			$fault_response = $extra_options['return_on_fault'];
		}
		else
		{
			$decode_fault = false;
			$fault_response = '';
		}
		$debug = isset($extra_options['debug']) ? ($extra_options['debug']) : 0;

		$msgclass = $prefix.'msg';
		$valclass = $prefix.'val';
		$decodefunc = 'php_'.$prefix.'_decode';

		$msg =& new $msgclass('system.methodSignature');
		$msg->addparam(new $valclass($methodname));
		$client->setDebug($debug);
		$response =& $client->send($msg, $timeout, $protocol);
		if($response->faultCode())
		{
			error_log('XML-RPC: could not retrieve method signature from remote server for method '.$methodname);
			return false;
		}
		else
		{
			$msig = $response->value();
			if ($client->return_type != 'phpvals')
			{
				$msig = $decodefunc($msig);
			}
			if(!is_array($msig) || count($msig) <= $signum)
			{
				error_log('XML-RPC: could not retrieve method signature nr.'.$signum.' from remote server for method '.$methodname);
				return false;
			}
			else
			{
				// pick a suitable name for the new function, avoiding collisions
				if($newfuncname != '')
				{
					$xmlrpcfuncname = $newfuncname;
				}
				else
				{
					// take care to insure that methodname is translated to valid
					// php function name
					$xmlrpcfuncname = $prefix.'_'.preg_replace(array('/\./', '/[^a-zA-Z0-9_\x7f-\xff]/'),
						array('_', ''), $methodname);
				}
				while($buildit && function_exists($xmlrpcfuncname))
				{
					$xmlrpcfuncname .= 'x';
				}

				$msig = $msig[$signum];
				$mdesc = '';
				// if in 'offline' mode, get method description too.
				// in online mode, favour speed of operation
				if(!$buildit)
				{
					$msg =& new $msgclass('system.methodHelp');
					$msg->addparam(new $valclass($methodname));
					$response =& $client->send($msg, $timeout, $protocol);
					if (!$response->faultCode())
					{
						$mdesc = $response->value();
						if ($client->return_type != 'phpvals')
						{
							$mdesc = $mdesc->scalarval();
						}
					}
				}

				$results = build_remote_method_wrapper_code($client, $methodname,
					$xmlrpcfuncname, $msig, $mdesc, $timeout, $protocol, $simple_client_copy,
					$prefix, $decode_php_objects, $encode_php_objects, $decode_fault,
					$fault_response);

				//print_r($code);
				if ($buildit)
				{
					$allOK = 0;
					eval($results['source'].'$allOK=1;');
					// alternative
					//$xmlrpcfuncname = create_function('$m', $innercode);
					if($allOK)
					{
						return $xmlrpcfuncname;
					}
					else
					{
						error_log('XML-RPC: could not create function '.$xmlrpcfuncname.' to wrap remote method '.$methodname);
						return false;
					}
				}
				else
				{
					$results['function'] = $xmlrpcfuncname;
					return $results;
				}
			}
		}
	}

	/**
	* Similar to wrap_xmlrpc_method, but will generate a php class that wraps
	* all xmlrpc methods exposed by the remote server as own methods.
	* For more details see wrap_xmlrpc_method.
	* @param xmlrpc_client $client the client obj all set to query the desired server
	* @param array $extra_options list of options for wrapped code
	* @return mixed false on error, the name of the created class if all ok or an array with code, class name and comments (if the appropriatevoption is set in extra_options)
	*/
	function wrap_xmlrpc_server($client, $extra_options=array())
	{
		$methodfilter = isset($extra_options['method_filter']) ? $extra_options['method_filter'] : '';
		$signum = isset($extra_options['signum']) ? (int)$extra_options['signum'] : 0;
		$timeout = isset($extra_options['timeout']) ? (int)$extra_options['timeout'] : 0;
		$protocol = isset($extra_options['protocol']) ? $extra_options['protocol'] : '';
		$newclassname = isset($extra_options['new_class_name']) ? $extra_options['new_class_name'] : '';
		$encode_php_objects = isset($extra_options['encode_php_objs']) ? (bool)$extra_options['encode_php_objs'] : false;
		$decode_php_objects = isset($extra_options['decode_php_objs']) ? (bool)$extra_options['decode_php_objs'] : false;
		$verbatim_client_copy = isset($extra_options['simple_client_copy']) ? !($extra_options['simple_client_copy']) : true;
		$buildit = isset($extra_options['return_source']) ? !($extra_options['return_source']) : true;
		$prefix = isset($extra_options['prefix']) ? $extra_options['prefix'] : 'xmlrpc';

		$msgclass = $prefix.'msg';
		//$valclass = $prefix.'val';
		$decodefunc = 'php_'.$prefix.'_decode';

		$msg =& new $msgclass('system.listMethods');
		$response =& $client->send($msg, $timeout, $protocol);
		if($response->faultCode())
		{
			error_log('XML-RPC: could not retrieve method list from remote server');
			return false;
		}
		else
		{
			$mlist = $response->value();
			if ($client->return_type != 'phpvals')
			{
				$mlist = $decodefunc($mlist);
			}
			if(!is_array($mlist) || !count($mlist))
			{
				error_log('XML-RPC: could not retrieve meaningful method list from remote server');
				return false;
			}
			else
			{
				// pick a suitable name for the new function, avoiding collisions
				if($newclassname != '')
				{
					$xmlrpcclassname = $newclassname;
				}
				else
				{
					$xmlrpcclassname = $prefix.'_'.preg_replace(array('/\./', '/[^a-zA-Z0-9_\x7f-\xff]/'),
						array('_', ''), $client->server).'_client';
				}
				while($buildit && class_exists($xmlrpcclassname))
				{
					$xmlrpcclassname .= 'x';
				}

				/// @todo add function setdebug() to new class, to enable/disable debugging
				$source = "class $xmlrpcclassname\n{\nvar \$client;\n\n";
				$source .= "function $xmlrpcclassname()\n{\n";
				$source .= build_client_wrapper_code($client, $verbatim_client_copy, $prefix);
				$source .= "\$this->client =& \$client;\n}\n\n";
				$opts = array('simple_client_copy' => 2, 'return_source' => true,
					'timeout' => $timeout, 'protocol' => $protocol,
					'encode_php_objs' => $encode_php_objects, 'prefix' => $prefix,
					'decode_php_objs' => $decode_php_objects
					);
				/// @todo build javadoc for class definition, too
				foreach($mlist as $mname)
				{
					if ($methodfilter == '' || preg_match($methodfilter, $mname))
					{
						$opts['new_function_name'] = preg_replace(array('/\./', '/[^a-zA-Z0-9_\x7f-\xff]/'),
							array('_', ''), $mname);
						$methodwrap = wrap_xmlrpc_method($client, $mname, $opts);
						if ($methodwrap)
						{
							if (!$buildit)
							{
								$source .= $methodwrap['docstring'];
							}
							$source .= $methodwrap['source']."\n";
						}
						else
						{
							error_log('XML-RPC: will not create class method to wrap remote method '.$mname);
						}
					}
				}
				$source .= "}\n";
				if ($buildit)
				{
					$allOK = 0;
					eval($source.'$allOK=1;');
					// alternative
					//$xmlrpcfuncname = create_function('$m', $innercode);
					if($allOK)
					{
						return $xmlrpcclassname;
					}
					else
					{
						error_log('XML-RPC: could not create class '.$xmlrpcclassname.' to wrap remote server '.$client->server);
						return false;
					}
				}
				else
				{
					return array('class' => $xmlrpcclassname, 'code' => $source, 'docstring' => '');
				}
			}
		}
	}

	/**
	* Given the necessary info, build php code that creates a new function to
	* invoke a remote xmlrpc method.
	* Take care that no full checking of input parameters is done to ensure that
	* valid php code is emitted.
	* Note: real spaghetti code follows...
	* @access private
	*/
	function build_remote_method_wrapper_code($client, $methodname, $xmlrpcfuncname,
		$msig, $mdesc='', $timeout=0, $protocol='', $client_copy_mode=0, $prefix='xmlrpc',
		$decode_php_objects=false, $encode_php_objects=false, $decode_fault=false,
		$fault_response='')
	{
		$code = "function $xmlrpcfuncname (";
		if ($client_copy_mode < 2)
		{
			// client copy mode 0 or 1 == partial / full client copy in emitted code
			$innercode = build_client_wrapper_code($client, $client_copy_mode, $prefix);
			$innercode .= "\$client->setDebug(\$debug);\n";
			$this_ = '';
		}
		else
		{
			// client copy mode 2 == no client copy in emitted code
			$innercode = '';
			$this_ = 'this->';
		}
		$innercode .= "\$msg =& new {$prefix}msg('$methodname');\n";

		if ($mdesc != '')
		{
			// take care that PHP comment is not terminated unwillingly by method description
			$mdesc = "/**\n* ".str_replace('*/', '* /', $mdesc)."\n";
		}
		else
		{
			$mdesc = "/**\nFunction $xmlrpcfuncname\n";
		}

		// param parsing
		$plist = array();
		$pcount = count($msig);
		for($i = 1; $i < $pcount; $i++)
		{
			$plist[] = "\$p$i";
			$ptype = $msig[$i];
			if($ptype == 'i4' || $ptype == 'int' || $ptype == 'boolean' || $ptype == 'double' ||
				$ptype == 'string' || $ptype == 'dateTime.iso8601' || $ptype == 'base64' || $ptype == 'null')
			{
				// only build directly xmlrpcvals when type is known and scalar
				$innercode .= "\$p$i =& new {$prefix}val(\$p$i, '$ptype');\n";
			}
			else
			{
				if ($encode_php_objects)
				{
					$innercode .= "\$p$i =& php_{$prefix}_encode(\$p$i, array('encode_php_objs'));\n";
				}
				else
				{
					$innercode .= "\$p$i =& php_{$prefix}_encode(\$p$i);\n";
				}
			}
			$innercode .= "\$msg->addparam(\$p$i);\n";
			$mdesc .= '* @param '.xmlrpc_2_php_type($ptype)." \$p$i\n";
		}
		if ($client_copy_mode < 2)
		{
			$plist[] = '$debug=0';
			$mdesc .= "* @param int \$debug when 1 (or 2) will enable debugging of the underlying {$prefix} call (defaults to 0)\n";
		}
		$plist = implode(', ', $plist);
		$mdesc .= '* @return '.xmlrpc_2_php_type($msig[0])." (or an {$prefix}resp obj instance if call fails)\n*/\n";

		$innercode .= "\$res =& \${$this_}client->send(\$msg, $timeout, '$protocol');\n";
		if ($decode_fault)
		{
			if (is_string($fault_response) && ((strpos($fault_response, '%faultCode%') !== false) || (strpos($fault_response, '%faultString%') !== false)))
			{
				$respcode = "str_replace(array('%faultCode%', '%faultString%'), array(\$res->faultCode(), \$res->faultString()), '".str_replace("'", "''", $fault_response)."')";
			}
			else
			{
				$respcode = var_export($fault_response, true);
			}
		}
		else
		{
			$respcode = '$res';
		}
		if ($decode_php_objects)
		{
			$innercode .= "if (\$res->faultcode()) return $respcode; else return php_{$prefix}_decode(\$res->value(), array('decode_php_objs'));";
		}
		else
		{
			$innercode .= "if (\$res->faultcode()) return $respcode; else return php_{$prefix}_decode(\$res->value());";
		}

		$code = $code . $plist. ") {\n" . $innercode . "\n}\n";

		return array('source' => $code, 'docstring' => $mdesc);
	}

	/**
	* Given necessary info, generate php code that will rebuild a client object
	* Take care that no full checking of input parameters is done to ensure that
	* valid php code is emitted.
	* @access private
	*/
	function build_client_wrapper_code($client, $verbatim_client_copy, $prefix='xmlrpc')
	{
		$code = "\$client =& new {$prefix}_client('".str_replace("'", "\'", $client->path).
			"', '" . str_replace("'", "\'", $client->server) . "', $client->port);\n";

		// copy all client fields to the client that will be generated runtime
		// (this provides for future expansion or subclassing of client obj)
		if ($verbatim_client_copy)
		{
			foreach($client as $fld => $val)
			{
				if($fld != 'debug' && $fld != 'return_type')
				{
					$val = var_export($val, true);
					$code .= "\$client->$fld = $val;\n";
				}
			}
		}
		// only make sure that client always returns the correct data type
		$code .= "\$client->return_type = '{$prefix}vals';\n";
		//$code .= "\$client->setDebug(\$debug);\n";
		return $code;
	}
?>