<?php
 require_once("db.php");
	
$twitter_username = "gamambel";
$twitter_feed_remote = "http://twitter.com/statuses/user_timeline/147903457.rss";
$twitter_feed = "147903457.rss";

/** simple pie doesn't work for remote feeds on my hosters config .... **/
$ch = curl_init();
$timeout = 50; // set to zero for no timeout
curl_setopt ($ch, CURLOPT_URL, $twitter_feed_remote);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$file_contents = curl_exec($ch);
curl_close($ch);
file_put_contents($twitter_feed,$file_contents);



require_once('simplepie.php');
$feed = new SimplePie();
$_GET['feed'] = $twitter_feed;
$feed->set_feed_url($twitter_feed);
$feed->init();
$feed->handle_content_type();
if ($feed->data):

	$db = mysql_connect($dburl, $dbuser, $dbpwd);
	if (!$db) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_query("SET NAMES 'utf8'");
	if (!mysql_select_db($dbdb, $db)) {
		die('Could not select database. '. mysql_error());
	}

	$page = "<html><body><ul>";
	$changed = false;
	$items = $feed->get_items();
	foreach($items as $item):		
	        $tweet = mysql_real_escape_string($item->get_description());
			$url = mysql_real_escape_string($item->get_permalink());
			$id = mysql_real_escape_string(substr(strrchr($url, "/"), 1));
			$timestamp = mysql_real_escape_string($item->get_date("Y-m-d H:i:s"));
			
			$query = "INSERT INTO $dbarchive (id, tweet, url, timestamp) VALUES (". $id. ", '". $tweet. "','" . $url . "', '" . $timestamp . "');";
			$result = mysql_query($query);
			
			if ($result) {
				$page .= "<li>$id: $tweet ($url)</li>";		
				$changed = true;
			}
	endforeach;
	mysql_close($db);

	$page.="</ul></body></html>";	
	echo $page;

	if ($changed == true) {
		require_once("parse.php");
	}
endif;

?>
