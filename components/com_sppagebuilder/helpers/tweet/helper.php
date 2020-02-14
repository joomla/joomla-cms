<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

require_once __DIR__ . '/twitteroauth/twitteroauth.php';
jimport( 'joomla.filesystem.folder' );

class sppbAddonHelperTweet
{
	public static function getTweets( $username='joomshaper', $consumerkey='', $consumersecret='', $accesstoken='', $accesstokensecret='', $count=5, $ignore_replies=false, $include_rts=false )
	{

		$cache_path 		 = JPATH_CACHE . '/com_sppagebuilder/addons/tweet';
		$cache_file 		 = $cache_path . '/cache.txt';
		$cachetime           = 60*15;

		$tweets = '';

		//Create cache folder if not exists
		if(!JFolder::exists( $cache_path )) JFolder::create( $cache_path );

		$cache_file_created  = ((Jfile::exists($cache_file))) ? filemtime($cache_file) : 0;

		if (time() - $cachetime < $cache_file_created)
		{
			$tweets = json_decode(JFile::read( $cache_file ) );
		}
		else
		{
			$connection = new TwitterOAuth($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

			if($connection)
			{
				$get_tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$username."&count=".$count."&include_rts=".$include_rts."&exclude_replies=".$ignore_replies);

				$tweets = json_encode($get_tweets);

				JFile::write( $cache_file, $tweets );

				$tweets = $get_tweets;

			}
		}

		return $tweets;

	}

	public static function timeago($timestamp) {

		$time_arr 		= explode(" ",$timestamp);
		$year 			= $time_arr[5];
		$day 			= $time_arr[2];
		$time 			= $time_arr[3];
		$time_array 	= explode(":",$time);
		$month_name 	= $time_arr[1];
		$month = array (
			'Jan' => 1,
			'Feb' => 2,
			'Mar' => 3,
			'Apr' => 4,
			'May' => 5,
			'Jun' => 6,
			'Jul' => 7,
			'Aug' => 8,
			'Sep' => 9,
			'Oct' => 10,
			'Nov' => 11,
			'Dec' => 12
			);

		$delta = gmmktime(0, 0, 0, 0, 0) - mktime(0, 0, 0, 0, 0);
		$timestamp = mktime($time_array[0], $time_array[1], $time_array[2], $month[$month_name], $day, $year);
		$etime = time() - ($timestamp + $delta);
		if ($etime < 1) {
			return '0 ' . JText::_('COM_SPPAGEBUILDER_SECONDS');
		}

		$a = array( 12 * 30 * 24 * 60 * 60  =>  'YEAR',
			30 * 24 * 60 * 60       =>  'MONTH',
			24 * 60 * 60            =>  'DAY',
			60 * 60                 =>  'HOUR',
			60                      =>  'MINUTE',
			1                       =>  'SECOND'
			);

		foreach ($a as $secs => $str) {
			$d = $etime / $secs;
			if ($d >= 1) {
				$r = round($d);
				return $r . ' ' . JText::_( 'COM_SPPAGEBUILDER_' . $str . ($r > 1 ? 'S' : '')) . ' ' . JText::_( 'COM_SPPAGEBUILDER_AGO');
			}
		}
	}

}
