<?php
/* 06/06/2010 10.11
Project Honey Pot Http BlackList
http://www.projecthoneypot.org/httpbl_configure.php
version 0.1

- 2008-01-18 version 0.1 by Francois Dechery, www.440net.net

This php class is distribured under the GNU Public License ("GPL") version 2.
http://www.gnu.org/licenses/gpl.txt

--------------
Usage Example:

$h=new http_bl('pqtxmwvztziq'); // put your access key here
$ip='89.149.254.13'; // replace with the ip to query
$r=$h->query($ip);

echo $ip.": ";
if($r==2){
echo "Found a " . $h->type_txt ." (".$h->type_num .") with a score of ". $h->score . ", last seen since ". $h->days . " days";
}
elseif($r==1){
echo "Found a Search engine (". $h->engine_num . ")";
}
else{
echo "Not Found";
}
*/
class http_bl
{
	var $access_key		= '';
	var $domain			= 'dnsbl.httpbl.org';
	var $answer_codes	= array();
	//var $engine_codes=array();

	var $ip 			= '';
	var $type_txt		= '';
	var $type_num		= 0;
	var $engine_txt		= '';
	var $engine_num		= 0;
	var $days			= 0;
	var $score			= 0;

	// ***********************************************
	function http_bl( $key = '' ) {
		$key && $this->access_key = $key;

		$this->answer_codes = array(
			0 => JText::_( 'Search Engine' ),
			1 => JText::_( 'Suspicious' ),
			2 => JText::_( 'Harvester' ),
			3 => JText::_( 'Suspicious' ) . ' & ' . JText::_( 'Harvester' ),
			4 => JText::_( 'Comment Spammer' ),
			5 => JText::_( 'Suspicious' ) . ' & ' . JText::_( 'Comment Spammer' ),
			6 => JText::_( 'Harvester' ) . ' & ' . JText::_( 'Comment Spammer' ),
			7 => JText::_( 'Suspicious' ) . ' & ' . JText::_( 'Harvester' ) . ' & ' . JText::_( 'Comment Spammer' )
		);
	}

	// return 1 (Search engine) or 2 (Generic) if host is found, else return 0
	function query($ip){
		
		if( !$ip ) {
			return false;
		}
		$this->ip = $ip;
		list( $a, $b, $c, $d ) = explode( '.', $ip );
		$query	= $this->access_key . ".$d.$c.$b.$a." . $this->domain;
		$host	= gethostbyname( $query );
		list( $first, $days, $score, $type ) = explode( '.', $host );

		if( $first == 127 ) {
			//spammer
			$this->days		= $days;
			$this->score	= $score;
			$this->type_num = $type;
			$this->type_txt	= $this->answer_codes[$type];

			// search engine
			if( $type == 0 ) {
				$this->days			= 0;
				$this->score		= 0;
				$this->engine_num	= $score;
				//$this->engine_txt =$this->engine_codes[$score];
				return 1;
			}else{
				return 2;
			}
		}
		return 0;
	}
}
?>