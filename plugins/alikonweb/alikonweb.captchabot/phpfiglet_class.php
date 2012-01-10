<?php
error_reporting(1);
/*06/06/2010 10.54*/
/*        _         _____  _       _       _
 *   ___ | |_  ___ |   __||_| ___ | | ___ | |_
 *  | . ||   || . ||   __|| || . || || -_||  _|
 *  |  _||_|_||  _||__|   |_||_  ||_||___||_|
 *  |_|       |_|            |___|
 *
 *	Author	 :		Lucas Baltes (lucas@thebobo.com)
 *					$Author: lhb $
 *
 *	Website	 :		http://www.thebobo.com/
 *
 *	Date	 :		$Date: 2003/03/16 10:08:01 $
 *	Rev      :		$Revision: 1.0 $
 *
 *	Copyright:		2003 - Lucas Baltes
 *  License  :		GPL - http://www.gnu.org/licenses/gpl.html
 *
 *	Purpose	 :		Figlet font class
 *
 *  Comments :		phpFiglet is a php class to somewhat recreate the
 *					functionality provided by the original figlet program
 *					(http://www.figlet.org/). It does not (yet) support the
 *					more advanced features like kerning or smushing. It can
 *					use the same (flf2a) fonts as the original figlet program
 *					(see their website for more fonts).
 *
 *  Usage    :		$phpFiglet = new phpFiglet();
 *
 *					if ($phpFiglet->loadFont("fonts/standard.flf")) {
 *						$phpFiglet->display("Hello World");
 *					} else {
 *						trigger_error("Could not load font file");
 *					}
 *
 */


class phpFiglet
{

	/*
	 *  Internal variables
	 */
  var $righe =array(); 
	var $signature;
	var $hardblank;
	var $height;
	var $baseline;
	var $maxLenght;
	var $oldLayout;
	var $commentLines;
	var $printDirection;
	var $fullLayout;
	var $codeTagCount;
	var $fontFile;


	/*
	 *  Contructor
	 */

	function phpFiglet()
	{

	}


	/*
	 *  Load an flf font file. Return true on success, false on error.
	 */

	function loadfont($fontfile)
	{
		$f = dirname(__FILE__);
		$f.='\media\banner4.flf';
		//$this->fontFile = file($fontfile);
		$this->fontFile= file($f);
		//if (!$this->fontFile) die("Couldnt open fontfile $fontfile\n");
    if (!$this->fontFile) die("Couldnt open fontfile $f\n");
		$hp = explode(" ", $this->fontFile[0]); // get header

		   $this->signature = substr($hp[0], 0, strlen($hp[0]) -1);
		  // echo 'signature:'.$this->signature.'<br>';			
        $this->hardblank = substr($hp[0], strlen($hp[0]) -1, 1);
      //  echo 'hardblank:'.$this->hardblank.'<br>';			
        $this->height = $hp[1];
      //  echo 'height:'.$this->height.'<br>';			
        $this->baseline = $hp[2];
      //  echo 'baseline:'.$this->baseline.'<br>';			
        $this->maxLenght = $hp[3];
      //  echo 'maxLenght:'.$this->maxLenght.'<br>';			
        $this->oldLayout = $hp[4];
      //  echo 'oldLayout:'.$this->oldLayout.'<br>';			
        $this->commentLines = $hp[5] + 1;
      //  echo 'commentLines:'.$this->commentLines.'<br>';			
        $this->printDirection = $hp[6];
      //  echo 'printDirection:'.$this->printDirection.'<br>';			
        $this->fullLayout = $hp[7];
      //  echo 'fullLayout:'.$this->fullLayout.'<br>';			
        $this->codeTagCount = $hp[8];
      //  echo 'codeTagCount:'.$this->codeTagCount.'<br>';			
      //  echo '*-----------*<br>';			
        unset($hp);

        if ($this->signature != "flf2a") {
        	trigger_error("Unknown font version " . $this->signature . "\n");
        	return false;
        } else {
        	return true;
        }
	}


	/*
	 *  Get a character as a string, or an array with one line
	 *  for each font height.
	 */

	function getCharacter($character, $asarray = false)
	{
		$asciValue = ord($character);
		$start = $this->commentLines + ($asciValue - 32) * $this->height;
		$data = ($asarray) ? array() : "";

		for ($a = 0; $a < $this->height; $a++)
		{
			$tmp = $this->fontFile[$start + $a];
			$tmp = str_replace("@", "", $tmp);
			//$tmp = trim($tmp);
			$tmp = str_replace($this->hardblank, " ", $tmp);

			if ($asarray) {
				$data[] = $tmp;
			} else {
				$data .= $tmp;
			}
			
		}

		return $data;
	}


	/*
	 *  Returns a figletized line of characters.
	 */

	function fetch($line)
	{
		
		$ret = "";

		for ($i = 0; $i < (strlen($line)); $i++)
		{
			$data[] = $this->getCharacter($line[$i], true);
		}


	@reset($data);

		for ($i = 0; $i < $this->height; $i++)
		{

			while (list($k, $v) = each($data))
			{
				$ret .= str_replace("\n", "", $v[$i]);	   
			 $this->righe[$i].=$v[$i];
			 
			}
			// $this->righe[$i].=$v[$i];
			reset($data);

		$ret .= "\n";
		

		}

		return 	$ret ;
	}


	/*
	 *  Display (print) a figletized line of characters.
	 *	$phpFiglet->display("abc");
	 */

	function display($line)
	{
		$stringa='';
		$stringa.= '<div style="font-size :0.1em; color : #0000FF;"><pre>';
		$num=count($this->righe);

	$this->fetch($line);
			
	for ($i = 0; $i < $this->height; $i++)
		{
			$this->righe[$i]=str_replace("\r\n", "", $this->righe[$i]);
			$a= strlen($this->righe[$i]);
			for ($x = 0; $x < $a; $x++){
				$stringa.=substr($this->righe[$i],$x,1);
				
			}	
	$stringa.='<br />';		   
		}
$stringa.='</pre></div>';	   
		
 return   $stringa;
	}

}
?>