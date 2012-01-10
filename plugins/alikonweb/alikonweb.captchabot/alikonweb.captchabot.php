<?php
/**
* @version 1.7.0
* @package AJAX Captcha 4 Joomla 1.6
* @copyright Copyright (C) 2005 - 2011 Alikonweb. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
//$lang	 =& JFactory::getLanguage();
/** ensure this file is being included by a parent file */

defined( '_JEXEC' ) or die( 'Restricted access' );
//require_once 'blow.php';
error_reporting(0);

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' );			
$app = JFactory::getApplication();
//Security code events        onView, onVerify
$app->registerEvent( 'onVerify', 'plgVerify' );
$app->registerEvent( 'onView2', 'plgView_Captcha' );
$app->registerEvent( 'onRefresh', 'plgAjaxCaptcha_refresh'); 

 //--


/**
* Check secure code form event
* Method is called when a user want validate the form
* @param 	code
* @return	boolean	true, false
*/
function plgVerify($p1,$buttonid){
		global $mainframe;
  $plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.captchabot');$botParams = new JParameter( $plugin->params );
  $crypt  = $botParams->def('crypt', '0');
  $mode   = $botParams->def('smode', 5);
  $max    = $botParams->def('overtime', 5);
  $temp_session = $_SESSION; // backup all session data
    session_write_close();
    ini_set('session.save_handler','files'); // set session saved hadler on file
    session_start();
    if ($buttonid=='validate2'){
    	$istanza=9;
    }else	{
    	$istanza=4;
    }		
    /*
switch($buttonid){
	case 'submitlogin':
	$istanza=1;
	break;
	case 'submittest':
	$istanza=2;
	break;
	case 'submitcontact':
	$istanza=3;
	break;
    case 'submitregister':
	$istanza=4;
	break;
	case 'submitremind':
	$istanza=5;
	break;
	case 'submitreset':
	$istanza=6;
	break;
	case 'submitexpire':
	$istanza=7;
	break;
	case 'form-test':
	$istanza=8;
	break;
	case 'submitmodlogin':
	$istanza=9;
	break;
}
*/
//var_dump($_SESSION['digit']);

    $char =$_SESSION['digit'][$istanza];
    $res  =$_SESSION['result'][$istanza];
    $times  =$_SESSION['times'][$istanza];
    $times = isset($_SESSION['times'][$istanza]) ?  $_SESSION['times'][$istanza] : 1;
    $_SESSION['times'][$istanza]=$times+1;
    $mode=$_SESSION['mode'][$istanza];
/*
    echo 'char:'.$char.'<br/>';
    echo 'res:'.$res.'<br/>';
    echo 'times:'.$times.'<br/>';
    echo 'mode:'.$mode.'<br/>';
    echo 'ISTANZA:'.$istanza.'<br/>';
    echo 'buttonid:'.$buttonid.'<br/>';
    exit();
*/
    session_write_close();

    ini_set('session.save_handler','user'); // put back session saved handler on database
    $jd = new JSessionStorageDatabase();
    $jd->register('digit'); // set required parameters
    $jd->register('result'); // set required parameters
    $jd->register('times'); // set required parameters
    $jd->register('mode'); // set required parameters
    session_start(); // restart //
    $_SESSION = $temp_session; // restore last session data


//$char =$_SESSION['digit'];
if ($times > $max ){
	RETURN 0;
}
   if(($mode=='5')|| ($mode=='6')||($mode=='7')) {
   	 // $session =& JFactory::getSession();
     // $p2  = $session->get('digit');
   	 // $res = $session->get('result');
   		$p2=$res;
   }else{




 //
 switch ($crypt) {
				case '0' :
					// no
					$p2=$char;

					break;
				case '1' :
					// blow
					require_once 'blow.php';
					$obj = new MyBlowfish("ALIKON_FIGHT_SPAM") ;
          $p2 = $obj->decryptString( $char ) ;
					break;
				case '2' :
					// aes
				//	if (version_compare( phpversion(), '5.0' ) < 0) {
					/*
					include_once('AES.class.php');
          $key256 = '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4';
          $Cipher = new AES(AES::AES256);
          $content = $Cipher->decrypt($char, $key256);
          $p2 = $Cipher->hexToString($content);
					break;
					*/
					$z = "abcdefgh01234567"; // 128-bit key
					include_once('AES4.class.php');
					$aes = new AES($z);
					$p2=$aes->decrypt($char);
					break;

      }
//

   }

unset($_SESSION['digit']);
 	 unset($_SESSION['digit'][$istanza]);
 	 unset($_SESSION['result'][$istanza]);
 	 unset($_SESSION['times'][$istanza]);
 	 
/* 	 
    echo 'char:'.$char.'<br/>';
    echo 'res:'.$res.'<br/>';
    echo 'times:'.$times.'<br/>';
    echo 'mode:'.$mode.'<br/>';
    echo 'ISTANZA:'.$istanza.'<br/>';
    echo 'buttonid:'.$buttonid.'<br/>';
     echo 'p1:'.$p1.'<br/>';
      echo 'p2:'.$p2.'<br/>';
    exit();
*/    
 if  ( $p2 != $p1){
  // RETURN false;
  	RETURN 1;
 }else{


  // return true;
  RETURN 2;

 }

}

function convert_number($number) {
	/*
    if (($number < 0) || ($number > 999999999))
    {
    throw new Exception("Number is out of range");
    }
*/
    $Gn = floor($number / 1000000);  /* Millions (giga) */
    $number -= $Gn * 1000000;
    $kn = floor($number / 1000);     /* Thousands (kilo) */
    $number -= $kn * 1000;
    $Hn = floor($number / 100);      /* Hundreds (hecto) */
    $number -= $Hn * 100;
    $Dn = floor($number / 10);       /* Tens (deca) */
    $n = $number % 10;               /* Ones */

    $res = "";

    if ($Gn)
    {
        $res .= convert_number($Gn) . ' ' . JText::_( 'Million' );
    }

    if ($kn)
    {
        $res .= (empty($res) ? "" : " ") .
            convert_number($kn) . ' ' . JText::_( 'Thousand' );
    }

    if ($Hn)
    {
        $res .= (empty($res) ? "" : " ") .
            convert_number($Hn) . ' ' . JText::_( 'Hundred' );
    }

    $ones = array(
		'',
		JText::_( 'One' ),
		JText::_( 'Two' ),
		JText::_( 'Three' ),
		JText::_( 'Four' ),
		JText::_( 'Five' ),
		JText::_( 'Six' ),
        JText::_( 'Seven' ),
		JText::_( 'Eight' ),
		JText::_( 'Nine' ),
		JText::_( 'Ten' ),
		JText::_( 'Eleven' ),
		JText::_( 'Twelve' ),
		JText::_( 'Thirteen' ),
        JText::_( 'Fourteen' ),
		JText::_( 'Fifteen' ),
		JText::_( 'Sixteen' ),
		JText::_( 'Seventeen' ),
		JText::_( 'Eighteen' ),
        JText::_( 'Nineteen' )
	);
    $tens = array(
		'',
		'',
		JText::_( 'Twenty' ),
		JText::_( 'Thirty' ),
		JText::_( 'Fourty' ),
		JText::_( 'Fifty' ),
		JText::_( 'Sixty' ),
        JText::_( 'Seventy' ),
		JText::_( 'Eighty' ),
		JText::_( 'Ninety' )
	);

    if ($Dn || $n)
    {
        if (!empty($res))
        {
            $res .= ' ' . JText::_( 'and' ) . ' ';
        }

        if ($Dn < 2)
        {
            $res .= $ones[$Dn * 10 + $n];
        }
        else
        {
            $res .= $tens[$Dn];

            if ($n)
            {
                $res .= "-" . $ones[$n];
            }
        }
    }

    if (empty($res) )
   // if ($number==0)
    {
        $res = JText::_( 'zero' );
    }

    return $res;
}

class ConvertRoman
{
	var $number;
	var $numrom;
	var $romovr;

	function ConvertRoman($number) {
		$this->number = $number;

		$this->numrom = array("I"=>1,"A"=>4,
			"V"=>5,"B"=>9,
			"X"=>10,"E"=>40,
			"L"=>50,"F"=>90,
			"C"=>100,"G"=>400,
			"D"=>500,"H"=>900,
			"M"=>1000,"J"=>4000,
			"P"=>5000,"K"=>9000,
			"Q"=>10000,"N"=>40000,
			"R"=>50000,"W"=>90000,
			"S"=>100000,"Y"=>400000,
			"T"=>500000,"Z"=>900000,
			"U"=>1000000);
		$this->romovr = array("/_V/"=>"/P/",
			"/_X/"=>"/Q/",
			"/_L/"=>"/R/",
			"/_C/"=>"/S/",
			"/_D/"=>"/T/",
			"/_M/"=>"/U/",
			"/IV/"=>"/A/","/IX/"=>"/B/","/XL/"=>"/E/","/XC/"=>"/F/",
			"/CD/"=>"/G/","/CM/"=>"/H/","/M_V/"=>"/J/","/MQ/"=>"/K/",
			"/QR/"=>"/N/","/QS/"=>"/W/","/ST/"=>"/Y/","/SU/"=>"/Z/");

		if(is_numeric($number)) {
			$this->convert2rom();
		}else{
			$this->convert2num();
		}
	}

	function convert2num() {
		$this->result = $this->convert_num();
		//need roman numeral input validation
	}

	function result() {
		return $this->result;
	}


	function convert2rom() {
		if($this->number > 0) {
			$this->result = $this->convert_rom();
		}else{
			return $this->raiseerror(1);
		}
	}

	function convert_num() {
		$number = $this->number;

		$numrom = $this->numrom;

		$romovr = $this->romovr;

		$number = preg_replace(array_keys($romovr),array_values($romovr), $number);
		print $number;
		$split_rom = preg_split('//', strrev($number), -1, PREG_SPLIT_NO_EMPTY);

		for($i=0; $i < sizeof($split_rom); ++$i){
			$num = $numrom[$split_rom[$i]];

			if( $i > 0 && ($num < $numrom[$split_rom[$i-1]])) {
				$num = -$num;
			}

			$arr_num += $num;
		}
		return str_replace("/","",$arr_num);
	}

	function convert_rom() {
		$str_roman = '';
		$number = $this->number;
		$numrom = array_reverse($this->numrom);
		$arabic = array_values($numrom);
		$roman  = array_keys($numrom);

		//algorithm from oguds
		$i = 0;
		while($number != 0) {
			while ($number >= $arabic[$i]) {
				$number-=  $arabic[$i];
				$str_roman.=  $roman[$i];
			}
			++$i;
		}

		$romovr =$this->romovr;

		$str_roman = str_replace("/","",preg_replace(array_values($romovr),array_keys($romovr), $str_roman));

		return $str_roman;
	}

	function raiseerror($num){
		if($num==1) {
			echo JText::_( 'unsupported number' );
		}
	}
}

//-----///
function rnd_ajaxdiv(){
$aa='';
  for ($i=0; $i<6; $i++) {
    $d=rand(1,30)%2;
	$aa.=chr(rand(65,90)) ;   
  } 
  return  $aa ;
}
function plgAjaxCaptcha_refresh( $tip,$buttonid ) {
	jimport('joomla.plugin.plugin');
     global $mainframe;
	 // define language
	 // let's do it

     $lang = JFactory :: getLanguage();
		 $lang->load('plg_alikonweb_ajaxcaptcha', JPATH_ADMINISTRATOR);
 		 $sound_dir=$lang->getTag();
	// JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.captchabot',JPATH_ADMINISTRATOR );
	 // Get plugin info
	 $plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.captchabot');
	 $botParams = new JParameter( $plugin->params );
	 $mode		= $botParams->def('smode', 5);
	 $align		= $botParams->def('align', 'center');
	 $crypt		= $botParams->def('crypt', '0');
	 $length	= $botParams->def('lcode', 4);
	 $par1		= $form;
	 $char		= '';
	 //$lang		= 'english'; // mic: needed for captcha audio output
	 $code		= '';
	 $res		= '';
	 $mcap		= '';

	 if (($tip == 'example') || ($mode == 8)){
	 	$mode = rand(0,6);
	// $mode=0;
	 }
//	 jexit('mode:'.$mode.' tip:'.$tip);
//switch mode begin
	//
	switch ($mode) {
		case '0' :
		case '1' :
		case '2' :
		case '3' :
		case '4' :
		    /* no 1, 0 */
		    $src  = '';
		    $src .= '23456789';
		    $src .= 'abcdefghijkmnpqrstuvwxyz';        /* no l, o */

		    $srclen = strlen($src)-1;
		    for($i=0; $i<$length; ++$i) {
		    	$char .= substr($src, mt_rand(0,$srclen), 1);
		    }
		    $classe="validate['required','alphanum','length[3,-1]']";

		    switch ($crypt) {
		    	case '0' :
					// include_once('md5.class.php');
					$code=$char;

					break;
				case '1' :
					// blow
					require_once 'blow.php';
					$obj = new MyBlowfish("ALIKON_FIGHT_SPAM") ;
					$code = $obj->encryptString( $char ) ;
					break;
				case '2' :
					// aes
					/*
					include_once('AES.class.php');
					$key256 = '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4';
					$Cipher = new AES(AES::AES256);
					$code = $Cipher->encrypt($Cipher->stringToHex($char), $key256);
					break;
					*/
					$z = "abcdefgh01234567"; // 128-bit key
					include_once('AES4.class.php');
					$aes = new AES($z);
					$code=$aes->encrypt($char);
					break;

		      }

		break;

		case '5' :
	
		    $classe="validate['required','num','length[1,-1]']";
	    	//math
			$num1=rand(0,9);
			$num2=rand(0,9);
			$num3=rand(0,9);
			$op1=rand(0,2);
			$op2=rand(0,2);

			if($op1=="0") $op3="+";
			if($op1=="1") $op3="-";
			if($op1=="2") $op3="*";
			if($op2=="0") $op4="+";
			if($op2=="1") $op4="-";
			if($op2=="2") $op4="*";
			$op=$op3.$op4;
    
			switch($op){			
				case '++':
					$num1	= rand(0,99);
					$res	= ($num1+$num2)+$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'plus' ) . '</strong> '
					.convert_number($num3);
				break;
				case '--':
					$num2	= rand(0,99);
					$res	= ($num1-$num2)-$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'minus' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num3);
				break;
				case '**':
					$res	=($num1*$num2)*$num3;
					$mcap	="(".convert_number($num1)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num3);
				break;
				case '+-':
					$num2	= rand(0,99);
					$res	= ($num1+$num2)-$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num3);
				break;
				case '-+':
					$num2	= rand(0,99);
					$res	= ($num1-$num2)+$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num3);
				break;
				case '*+':
					$res	= ($num1*$num2)+$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num3);
				break;
				case '+*':
					$res	= $num1+($num2*$num3);
					$mcap	= convert_number($num1)
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					."(".convert_number($num2)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num3).")";
				break;
				case '*-':
					$res	= ($num1*$num2)-$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num3);
				break;
				case '-*':
					$res	= $num1-($num2*$num3);
					$mcap	= convert_number($num1)
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					."(".convert_number($num2)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num3).")";
				break;
			}

		break;
		case '6' :
			$classe	= "validate['required','alphanum','length[1,-1]']";
			$res	= rand(1,100);
			$converter = new ConvertRoman($res);
			$roman	= $converter->result();
		break;
		case '7' :
		 $classe="validate['required','num','length[1,-1]']";
		      $num1=rand(0,9);
			$num2=rand(0,9);
		 $op1=rand(0,2);
			
		      
		      if($op1==0){
		      	$num1	= rand(0,99);
					  $res	= ($num1+$num2);
					  $mcap	= "(".convert_number($num1)
					  .' <strong>' . JText::_( 'add' ) . '</strong> '
					  .convert_number($num2).")";
					}
					if($op1==1){
		      	$num1	= rand(0,99);
					  $res	= ($num1-$num2);
					  $mcap	= "(".convert_number($num1)
					  .' <strong>' . JText::_( 'subtract' ) . '</strong> '
					  .convert_number($num2).")";
					}
					if($op1==2){
		      	$num1	= rand(0,13);
					  $res	= ($num1*$num2);
					  $mcap	= "(".convert_number($num1)
					  .' <strong>' . JText::_( 'multiply' ) . '</strong> '
					  .convert_number($num2).")";
					}
					
			//		jexit($mcap);
		break;
	}

	$temp_session = $_SESSION; // backup all session data
	session_write_close();
	ini_set('session.save_handler','files'); // set session saved hadler on file
	session_start();
	 //echo 'buttonid:'.$buttonid.'<br/>';
	    if ($buttonid=='validate2'){
    	$istanza=9;
    }else	{
    	$istanza=4;
    }		
    /*
	switch($buttonid){
		case 'submitlogin':
		$istanza=1;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submittest':
		$istanza=2;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitcontact':
		$istanza=3;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitregister':
		$istanza=4;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitremind':
		$istanza=5;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitreset':
		$istanza=6;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitexpire':
		$istanza=7;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'form-test':
		$istanza=8;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitmodlogin':
		$istanza=9;
		$_SESSION['istanza'] = $istanza;
		break;
	}
	*/
	/*
	$_SESSION['times']=array();
	$_SESSION['digit']=array();
	$_SESSION['result']=array();
	$_SESSION['mode']=array();
	*/
	$_SESSION['times'][$istanza] = 0;
	$_SESSION['digit'][$istanza] = $code;
	$_SESSION['result'][$istanza] = $res;
	$_SESSION['mode'][$istanza] =$mode;
	session_write_close();
	/*
	    echo 'istanza'.$istanza.'<br/>';
	    echo 'digit:'.$_SESSION['digit'][$istanza].'<br/>';
	    echo 'res:'.$_SESSION['result'][$istanza].'<br/>';
	    echo 'times:'.$_SESSION['times'][$istanza].'<br/>';
	    echo 'mode:'.$_SESSION['mode'][$istanza].'<br/>';
	    echo 'istanza:'.$_SESSION['istanza'].'<br/>';
	*/
	ini_set('session.save_handler','user'); // put back session saved handler on database
	$jd = new JSessionStorageDatabase();
	$jd->register('digit'); // set required parameters
	$jd->register('times'); // set required parameters
	$jd->register('result'); // set required parameters
	$jd->register('mode'); // set required parameters

	session_start();

	$_SESSION	= $temp_session; // restore last session data
	$e			= session_id();
	setcookie('jsid', $e, time()+3600,'/');
	$link_url	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/media/';
	//$link_url    =$pippo.'plugins/alikonweb/media/';
	$link_sound	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/playcode.php?l='.$sound_dir.'&c='.$e.'&x='.$crypt.'&i='.$istanza;
	//$link_sound=$pippo.'plugins/alikonweb/playcode.php?l='.$lang.'&c='.$e.'&x='.$crypt;
	$url_sound	= JRoute::_($link_sound);
	$link_image	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/showcode.php?'.time().'&c='.$e.'&x='.$crypt.'&i='.$istanza;
	//$link_image=$pippo.'plugins/alikonweb/showcode.php?'.time().'&c='.$e.'&x='.$crypt;
	$url_image	= JRoute::_($link_image);
	$atooltip	= JText::_('SECUREFROM')."::".JText::_('ENTERTHECODE');
	$link_font	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/media/banner4.flf';
	$link_font	= JRoute::_($link_font);
	//$classe="validate['required','alphanum','length[3,-1]']";
    JHTML::stylesheet('secureform.css', $link_url, null);
	$document =& JFactory::getDocument();
	 
//----------------------------------------//
//----------22/05/2011 9.40----------------//
	//	JHTML::stylesheet('secureform.css', $link_url, null);
	    
	    $html='<!-- alikonweb ajax captcha -->';
	   
	  // capctha list	
	  //	$html.='<dl><dt>&nbsp;</dt>';  
      $html.=capmode($mode,$roman,$mcap,$link_font,$char,$url_image,$url_sound,$btn_refresh);
		//	$html.='</dl>';

			$html.='<!-- alikonweb ajax captcha -->';		

//-----------22/05/2011 9.40--------------//

	return $html;
}
//-----22/05/2011 9.32 ---///
function plgView_Captcha( $tip,$form,$buttonid,$pswid,$msgid ) {
	/*
	 echo 'tip:'.$tip.'<br/>';
	 echo 'form:'.$form.'<br/>';
	 echo 'buttonid:'.$buttonid.'<br/>';
	  echo 'pswid:'.$pswid.'<br/>';
	   echo 'msgid:'.$msgid.'<br/>';
	   */
     $lang = JFactory :: getLanguage();
		 $lang->load('plg_alikonweb_ajaxcaptcha', JPATH_ADMINISTRATOR);
      $sound_dir=$lang->getTag();	
       $btn_refresh='btn'.substr($buttonid,1);
     $chekF=substr($buttonid,1);
     global $mainframe;
	 // define language
//	 JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.captchabot',JPATH_ADMINISTRATOR );
	 // Get plugin info
	 $plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.captchabot');
	 $botParams = new JParameter( $plugin->params );
	 $mode		= $botParams->def('smode', 5);
	 $align		= $botParams->def('align', 'center');
	 $crypt		= $botParams->def('crypt', '0');
	 $length	= $botParams->def('lcode', 4);
	 $par1		= $form;
	 $char		= '';
	 $lang		= 'english'; // mic: needed for captcha audio output
	 $code		= '';
	 $res		= '';
	 $mcap		= '';

	  if (($tip == 'example') || ($mode == 8)){
	 	$mode = rand(0,6);
	// $mode=0;
	 }
	 
//switch mode begin
	//
	switch ($mode) {
		case '0' :
		case '1' :
		case '2' :
		case '3' :
		case '4' :
		    /* no 1, 0 */
		    $src  = '';
		    $src .= '23456789';
		    $src .= 'abcdefghijkmnpqrstuvwxyz';        /* no l, o */

		    $srclen = strlen($src)-1;
		    for($i=0; $i<$length; ++$i) {
		    	$char .= substr($src, mt_rand(0,$srclen), 1);
		    }
		    $classe="validate['required','alphanum','length[3,-1]']";

		    switch ($crypt) {
		    	case '0' :
					// include_once('md5.class.php');
					$code=$char;

					break;
				case '1' :
					// blow
					require_once 'blow.php';
					$obj = new MyBlowfish("ALIKON_FIGHT_SPAM") ;
					$code = $obj->encryptString( $char ) ;
					break;
				case '2' :
					// aes
					/*
					include_once('AES.class.php');
					$key256 = '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4';
					$Cipher = new AES(AES::AES256);
					$code = $Cipher->encrypt($Cipher->stringToHex($char), $key256);
					break;
					*/
					$z = "abcdefgh01234567"; // 128-bit key
					include_once('AES4.class.php');
					$aes = new AES($z);
					$code=$aes->encrypt($char);
					break;

		      }

		break;

		case '5' :
	
		    $classe="validate['required','num','length[1,-1]']";
	    	//math
			$num1=rand(0,9);
			$num2=rand(0,9);
			$num3=rand(0,9);
			$op1=rand(0,2);
			$op2=rand(0,2);

			if($op1=="0") $op3="+";
			if($op1=="1") $op3="-";
			if($op1=="2") $op3="*";
			if($op2=="0") $op4="+";
			if($op2=="1") $op4="-";
			if($op2=="2") $op4="*";
			$op=$op3.$op4;
    
			switch($op){			
				case '++':
					$num1	= rand(0,99);
					$res	= ($num1+$num2)+$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'plus' ) . '</strong> '
					.convert_number($num3);
				break;
				case '--':
					$num2	= rand(0,99);
					$res	= ($num1-$num2)-$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'minus' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num3);
				break;
				case '**':
					$res	=($num1*$num2)*$num3;
					$mcap	="(".convert_number($num1)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num3);
				break;
				case '+-':
					$num2	= rand(0,99);
					$res	= ($num1+$num2)-$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num3);
				break;
				case '-+':
					$num2	= rand(0,99);
					$res	= ($num1-$num2)+$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num3);
				break;
				case '*+':
					$res	= ($num1*$num2)+$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					.convert_number($num3);
				break;
				case '+*':
					$res	= $num1+($num2*$num3);
					$mcap	= convert_number($num1)
					.' <strong>' . JText::_( 'add' ) . '</strong> '
					."(".convert_number($num2)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num3).")";
				break;
				case '*-':
					$res	= ($num1*$num2)-$num3;
					$mcap	= "(".convert_number($num1)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num2).")"
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					.convert_number($num3);
				break;
				case '-*':
					$res	= $num1-($num2*$num3);
					$mcap	= convert_number($num1)
					.' <strong>' . JText::_( 'subtract' ) . '</strong> '
					."(".convert_number($num2)
					.' <strong>' . JText::_( 'multiply' ) . '</strong> '
					.convert_number($num3).")";
				break;
			}

		break;
		case '6' :
			$classe	= "validate['required','alphanum','length[1,-1]']";
			$res	= rand(1,100);
			$converter = new ConvertRoman($res);
			$roman	= $converter->result();
		break;
		case '7' :
		 $classe="validate['required','num','length[1,-1]']";
		      $num1=rand(0,9);
			$num2=rand(0,9);
		 $op1=rand(0,2);
			
		      
		      if($op1==0){
		      	$num1	= rand(0,99);
					  $res	= ($num1+$num2);
					  $mcap	= "(".convert_number($num1)
					  .' <strong>' . JText::_( 'add' ) . '</strong> '
					  .convert_number($num2).")";
					}
					if($op1==1){
		      	$num1	= rand(0,99);
					  $res	= ($num1-$num2);
					  $mcap	= "(".convert_number($num1)
					  .' <strong>' . JText::_( 'subtract' ) . '</strong> '
					  .convert_number($num2).")";
					}
					if($op1==2){
		      	$num1	= rand(0,13);
					  $res	= ($num1*$num2);
					  $mcap	= "(".convert_number($num1)
					  .' <strong>' . JText::_( 'multiply' ) . '</strong> '
					  .convert_number($num2).")";
					}
					
			//		jexit($mcap);
		break;
	}

	$temp_session = $_SESSION; // backup all session data
	session_write_close();
	ini_set('session.save_handler','files'); // set session saved hadler on file
	session_start();
	// echo 'buttonid'.$buttonid.'<br/>';
	   if ($buttonid=='validate2'){
    	$istanza=9;
    }else	{
    	$istanza=4;
    }		
    /*
	switch($buttonid){
		case 'submitlogin':
		$istanza=1;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submittest':
		$istanza=2;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitcontact':
		$istanza=3;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitregister':
		$istanza=4;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitremind':
		$istanza=5;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitreset':
		$istanza=6;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitexpire':
		$istanza=7;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'form-test':
		$istanza=8;
		$_SESSION['istanza'] = $istanza;
		break;
		case 'submitmodlogin':
		$istanza=9;
		$_SESSION['istanza'] = $istanza;
		break;
	}
	*/
	/*
	$_SESSION['times']=array();
	$_SESSION['digit']=array();
	$_SESSION['result']=array();
	$_SESSION['mode']=array();
	*/
	$_SESSION['times'][$istanza] = 0;
	$_SESSION['digit'][$istanza] = $code;
	$_SESSION['result'][$istanza] = $res;
	$_SESSION['mode'][$istanza] =$mode;
	session_write_close();
	/*
	    echo 'istanza'.$istanza.'<br/>';
	    echo 'digit:'.$_SESSION['digit'][$istanza].'<br/>';
	    echo 'res:'.$_SESSION['result'][$istanza].'<br/>';
	    echo 'times:'.$_SESSION['times'][$istanza].'<br/>';
	    echo 'mode:'.$_SESSION['mode'][$istanza].'<br/>';
	    echo 'istanza:'.$_SESSION['istanza'].'<br/>';
	*/
	ini_set('session.save_handler','user'); // put back session saved handler on database
	$jd = new JSessionStorageDatabase();
	$jd->register('digit'); // set required parameters
	$jd->register('times'); // set required parameters
	$jd->register('result'); // set required parameters
	$jd->register('mode'); // set required parameters

	session_start();

	$_SESSION	= $temp_session; // restore last session data
	$e			= session_id();
	setcookie('jsid', $e, time()+3600,'/');
	$link_url	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/media/';
	//$link_url    =$pippo.'plugins/alikonweb/media/';
	$link_sound	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/playcode.php?l='.$sound_dir.'&c='.$e.'&x='.$crypt.'&i='.$istanza;
	//$link_sound=$pippo.'plugins/alikonweb/playcode.php?l='.$lang.'&c='.$e.'&x='.$crypt;
	$url_sound	= JRoute::_($link_sound);
	$link_image	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/showcode.php?'.time().'&c='.$e.'&x='.$crypt.'&i='.$istanza;
	//$link_image=$pippo.'plugins/alikonweb/showcode.php?'.time().'&c='.$e.'&x='.$crypt;
	$url_image	= JRoute::_($link_image);
	$atooltip	= JText::_('AJAX_CAPTCHA')."::".JText::_('ENTER_THE_CODE');
	$link_font	= JURI::base().'plugins/alikonweb/alikonweb.captchabot/media/banner4.flf';
	//$classe="validate['required','alphanum','length[3,-1]']";
    JHTML::stylesheet('secureform.css', $link_url, null);
	$document =& JFactory::getDocument();
	 //  $js = "window.addEvent('domready', function(){checkCaptcha('".$form."','".$buttonid."','".$pswid."','".$msgid."'); })";
	 /// $js = "window.addEvent('domready', function(){checkCaptcha(); })";
	  $btn_refresh='btn'.substr($buttonid,1);
	  $refreshbtn='btn'.$pswid;
	  $juri_root=JURI::root().'index.php';
	  //$juri_root=JURI::base();
	  $script =<<<EOL
//function checkCaptcha(form, buttonid, pswid, msgid, msg ) {
window.addEvent('domready', function(){{$chekF}checkCaptcha(); })
function {$chekF}checkCaptcha() {
//	
var url2="{$juri_root}?option=com_{$form}&task=refreshCaptacha&format=raw&amp;fid={$buttonid}"
		var req = new Request.JSON({
		                    url: url2, 
	               onComplete: function(response) {
			                //  var resp=JSON	.evaluate(response);
                        if (response.msg==='true'){
                        {$btn_refresh}refresh(response.html);	
                        }                                                                 
		                  }
		                  
	                   });
	
	               // azioniamo la richiesta
	               req.get();
            	
//	
//	$('{$buttonid}').setProperty('disabled', 'true');
	$$('{$buttonid}').setProperty('disabled', 'true');
	//$$(document.getElementsByTagName('button')).setProperty('disabled', 'true');
	//$$(document.getElementsByTagName('{$buttonid}')).setProperty('disabled', 'true');
	var box = $('{$msgid}');
	//var fx = box.effects({duration: 1000, transition: Fx.Transitions.Quart.easeOut});
  var Pswid = document.id('{$pswid}')
    //$('{$pswid}').addEvent("blur",function(){
    $(Pswid).addEvent("change",function(){
    $$('{$buttonid}').setProperty('disabled', 'true');
    if ( $('{$pswid}').value.length > 0 ){

        var url="{$juri_root}?option=com_{$form}&amp;task=chkCaptcha&amp;format=raw&amp;{$pswid}="+document.getElementById("{$pswid}").value+"&amp;campo={$pswid}&amp;fid={$buttonid}"; 
        //var url="index.php?option=com_"+form+"&amp;task=chkCaptcha&amp;format=raw&amp;"+pswid+"="+this.getValue()+"&amp;campo="+pswid+"&amp;fid="+buttonid;
        box.style.display="block";
    	box.set('html','Check in progress...');
        var a=new Request.JSON({
            url:url,
            onComplete: function(response){
               // var resp=Json.evaluate(response);

                if (response.msg==='false'){
                 $('{$pswid}').value='';
                 $('{$pswid}').focus();
                   $$('{$buttonid}').setProperty('disabled', 'true');
                }else{
                	$$('{$buttonid}').removeProperty('disabled');
          var el = $('{$btn_refresh}cazzo');
(function(){
        el.fade('out').get('tween');
        el.destroy();
}).delay(1500);   								
                }
                box.set('html',response.html);             
            }
        });
        a.get();
      }
    });
		
			 
$("{$btn_refresh}").addEvent("click",function(){
		var url2="{$juri_root}?option=com_{$form}&task=refreshCaptacha&format=raw&amp;fid={$buttonid}"
		var req = new Request.JSON({
		                    url: url2, 
	               onComplete: function(response) {
			                //  var resp=JSON	.evaluate(response);
                        if (response.msg==='true'){
                        {$btn_refresh}refresh(response.html);	
                        }                                                                 
		                  }
		                  
	                   });
	
	               // azioniamo la richiesta
	               req.get();
	});	             

};

function {$btn_refresh}refresh(html){
 var d = document.getElementById('{$btn_refresh}'+'ajaxwrapper');
 var olddiv = document.getElementById('{$btn_refresh}'+'ajaxcaptchadiv');
 d.removeChild(olddiv);				    	  
 var newdiv = document.createElement('div');
 var divIdName = '{$btn_refresh}'+'ajaxcaptchadiv';
 newdiv.setAttribute('id',divIdName);                  
 newdiv.innerHTML =html;
 d.appendChild(newdiv);
 
}
EOL;
    
//----------------------------------------------------------------------------------------------------------------------------//

//$document->addScriptDeclaration($js);
//$js = "window.addEvent('domready', function(){refreshCaptcha(); })";
//$document->addScriptDeclaration($js);
$rdiv=rnd_ajaxdiv();


JHTML::_('behavior.mootools');

$document->addScriptDeclaration($script);
//----------------------------------------//
$button_link=JURI::base();
$html='<!-- alikonweb ajax captcha -->';
	  	$html.='<div id="'.$btn_refresh.'cazzo">';
	  // capctha list	
	   // quiz  
      $html.='<dl><dt>&nbsp;</dt>';
      $html.=capmode($mode,$roman,$mcap,$link_font,$char,$url_image,$url_sound,$btn_refresh);
	  	$html.='<dt>&nbsp;</dt>';
// refersh	
      $html.='<dd>';      
      $html.='<input type="text" name="'.$pswid.'" id="'.$pswid.'" maxlength="5" size="5" value="" />	';
      $html.='<img width="24px" height=24px" id="'.
              $btn_refresh.
              '" src="'.
              JURI::root().'plugins/alikonweb/alikonweb.captchabot/refresh.png" title="'.JText::_( 'REFRESH' ).'" alt="'.JText::_( 'REFRESH' ).'"/>';
      //$html.='<img width="24px" height=24px" id="'.$btn_refresh.'" src="'.JPATH_PLUGINS.DS.'alikonweb/alikonweb.captchabot/refresh.png" title="'.JText::_( 'REFRESH' ).'" alt="'.JText::_( 'REFRESH' ).'"/>';
      //$html.='<div id="'.$msgid.'"></div>';	    
      $html.='</dd>';  
      
     // input text 
     $html.='<dt>&nbsp;</dt><dd>';   
     $html.='<div id="'.$msgid.'"></div>';	    
      $html.='</dd>';  
			$html.='</dl>';
			$html.='</div>';
		
$html.='<!-- alikonweb ajax captcha -->';
	return $html;
}
function capmode($mode,$roman,$mcap,$link_font,$char,$url_image,$url_sound,$btn_refresh){
 	$html='<div id="'.$btn_refresh.'ajaxwrapper"><div id="'.$btn_refresh.'ajaxcaptchadiv">';
 	    if( $mode == 6 ) { 			    
				$html.='<dt></dt><dd><div>'.JText::_( 'What number is' ).' :&nbsp;<strong>'.$roman.'</strong></div></dd>';			
			}
      if (( $mode == 5 )|| ($mode==7)){ 			
				$html.='<dt></dt><dd><div>'.JText::_( 'HOW_MUCH_IS' ).':&nbsp;'.$mcap.'</div></dd>';				
		 	}    
      if (($mode==0) || ($mode==4)) {
      	$html.='<dt></dt><dd><div>';
				include_once('phpfiglet_class.php');
				$phpFiglet = new phpFiglet();
				if ($phpFiglet->loadFont($link_font)) {
					$html.=$phpFiglet->display(strtoupper($char));
				}
				$html.='</div></dd>';
		 	}
		 	if (($mode==2) || ($mode==3)) { 	
			    $html.='<dt></dt><dd><div>
			            <img src="'.$url_image.'" title="Alikonweb Joomla Ajax Captcha plugin" alt="Alikonweb Joomla Ajax Captcha plugin" />
			    	      </div></dd>';
			}
			if (($mode==1) || ($mode==3) || ($mode==4)) {			
				$html.='<dt></dt><dd><div>
					      <a href="'.$url_sound.'" title="'.JText::_( 'Click to listen the Alikonweb security code' ).'">'.JText::_( 'Click to listen the code' ).'</a>
				        </div></dd>';				
			}
			$html.='</div></div>';
			return $html;
}			    	 