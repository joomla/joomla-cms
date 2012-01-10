<?php
/*
06/06/2010 10.32
*/
$sid = $_GET['c'];
session_id($sid);
session_start();
$istanza=$_GET['i'];
$code=$_SESSION['digit'][$istanza];
//$code = $_SESSION['digit'];
$crypt = $_GET['x'];
//$code = $_GET['c'];

 switch ($crypt) {
				case '0' :
					// no
					$rndstring=$code;
					
					break;
				case '1' :
					// blow
					require_once 'blow.php';
					$obj = new MyBlowfish("ALIKON_FIGHT_SPAM") ;          
          $rndstring = $obj->decryptString( $code ) ;
					break;	
				case '2' :
					// aes
					/*
					include_once('AES.class.php');
          $key256 = '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4';	
          $Cipher = new AES(AES::AES256);        
          $content = $Cipher->decrypt($code, $key256);
          $rndstring = $Cipher->hexToString($content);
					break;		
					 for php 4 users configuration issue
					*/				
					$z = "abcdefgh01234567"; // 128-bit key
					include_once('AES4.class.php');
					$aes = new AES($z);
					$rndstring=$aes->decrypt($code);
					break;		
				
      }
//


$file_array = array();
$char = array();


$length=strlen($rndstring);
$lang = $_GET['l'];
for($i=0; $i<$length; $i++) {
        $char[$i] = substr($rndstring, $i, 1);
        if (($char[$i]=='*') ||  ($char[$i]=='+') ||  ($char[$i]=='-') ||  ($char[$i]=='(') ||  ($char[$i]==')')){
           switch ($char[$i]) {
           		case '*' :
           		  $file_array[$i]= 'media/'.$lang.'/'.'multiply.mp3';  
           		   break;
           		case '+' :   
           		  $file_array[$i]= 'media/'.$lang.'/'.'add.mp3';  	
           		  break;
           		case '(' :     
           		  $file_array[$i]= 'media/'.$lang.'/'.'parenthesis.mp3';
	               break;
	            case ')' :     
           		  $file_array[$i]= 'media/'.$lang.'/'.'parenthesis.mp3';
	               break;   
	            case '-' :  
	              $file_array[$i]= 'media/'.$lang.'/'.'subtract.mp3';  
           		   break;		
	         } 
	      } else {
	         $file_array[$i]= 'media/'.$lang.'/'.strtolower($char[$i]).'.mp3';
	      }  
}

$out = '';
$len = 0;
$audioname="Alikonweb.mp3";

foreach ($file_array as $file){
 $fh = fopen($file, 'rb');
 $size = filesize($file);
 $out .= fread($fh, $size);
  
 $len += $size;
 fclose($fh);
}



header("Content-Type: audio/mpeg");
header("Content-Disposition: attachment; filename=$audioname;");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".$len);
echo $out;
?>
