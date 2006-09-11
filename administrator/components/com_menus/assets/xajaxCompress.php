<?php
// xajaxCompressJavascript() compresses the javascript code for more efficient delivery
// used internally
// $sJS is a string containing the javascript code to compress
function xajaxCompressJavascript($sJS)
{
	//remove windows cariage returns
	$sJS = str_replace("\r","",$sJS);

	//array to store replaced literal strings
	$literal_strings = array();

	//explode the string into lines
	$lines = explode("\n",$sJS);
	//loop through all the lines, building a new string at the same time as removing literal strings
	$clean = "";
	$inComment = false;
	$literal = "";
	$inQuote = false;
	$escaped = false;
	$quoteChar = "";

	for($i=0;$i<count($lines);$i++)
	{
		$line = $lines[$i];
		$inNormalComment = false;

		//loop through line's characters and take out any literal strings, replace them with ___i___ where i is the index of this string
		for($j=0;$j<strlen($line);$j++)
		{
			$c = substr($line,$j,1);
			$d = substr($line,$j,2);

			//look for start of quote
			if(!$inQuote && !$inComment)
			{
				//is this character a quote or a comment
				if(($c=="\"" || $c=="'") && !$inComment && !$inNormalComment)
				{
					$inQuote = true;
					$inComment = false;
					$escaped = false;
					$quoteChar = $c;
					$literal = $c;
				}
				else if($d=="/*" && !$inNormalComment)
				{
					$inQuote = false;
					$inComment = true;
					$escaped = false;
					$quoteChar = $d;
					$literal = $d;
					$j++;
				}
				else if($d=="//") //ignore string markers that are found inside comments
				{
					$inNormalComment = true;
					$clean .= $c;
				}
				else
				{
					$clean .= $c;
				}
			}
			else //allready in a string so find end quote
			{
				if($c == $quoteChar && !$escaped && !$inComment)
				{
					$inQuote = false;
					$literal .= $c;

					//subsitute in a marker for the string
					$clean .= "___" . count($literal_strings) . "___";

					//push the string onto our array
					array_push($literal_strings,$literal);

				}
				else if($inComment && $d=="*/")
				{
					$inComment = false;
					$literal .= $d;

					//subsitute in a marker for the string
					$clean .= "___" . count($literal_strings) . "___";

					//push the string onto our array
					array_push($literal_strings,$literal);

					$j++;
				}
				else if($c == "\\" && !$escaped)
					$escaped = true;
				else
					$escaped = false;

				$literal .= $c;
			}
		}
		if($inComment) $literal .= "\n";
		$clean .= "\n";
	}
	//explode the clean string into lines again
	$lines = explode("\n",$clean);

	//now process each line at a time
	for($i=0;$i<count($lines);$i++)
	{
		$line = $lines[$i];

		//remove comments
		$line = preg_replace("/\/\/(.*)/","",$line);

		//strip leading and trailing whitespace
		$line = trim($line);

		//remove all whitespace with a single space
		$line = preg_replace("/\s+/"," ",$line);

		//remove any whitespace that occurs after/before an operator
		$line = preg_replace("/\s*([!\}\{;,&=\|\-\+\*\/\)\(:])\s*/","\\1",$line);

		$lines[$i] = $line;
	}

	//implode the lines
	$sJS = implode("\n",$lines);

	//make sure there is a max of 1 \n after each line
	$sJS = preg_replace("/[\n]+/","\n",$sJS);

	//strip out line breaks that immediately follow a semi-colon
	$sJS = preg_replace("/;\n/",";",$sJS);

	//curly brackets aren't on their own
	$sJS = preg_replace("/[\n]*\{[\n]*/","{",$sJS);

	//finally loop through and replace all the literal strings:
	for($i=0;$i<count($literal_strings);$i++)
		$sJS = str_replace("___".$i."___",$literal_strings[$i],$sJS);

	return $sJS;
}
?>