<?php
	require_once("db.php");
	require_once("functions.php");
	
	$db = mysql_connect($dburl, $dbuser, $dbpwd);
	if (!$db) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_query("SET NAMES 'utf8'");
	if (!mysql_select_db($dbdb, $db)) {
		die('Could not select database. '. mysql_error());
	}
	
	$query  = "SELECT DISTINCT archive.id, tweet, url, timestamp FROM $dbarchive WHERE archive.id NOT IN (SELECT tweets.id FROM tweets) ORDER BY timestamp DESC";
	$result = mysql_query($query);
	
	$lastdate = "";
	$changed = false;
	$page = "<html>\n<head><title>Twitter Archive</title><link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\"/></head>\n<body>\n";
	while($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		$id = $row['id'];
		$tweet = $row['tweet'];
		$username = substr($tweet, 0, strpos($tweet,':'));
		$url = $row['url'];
		$timestamp = $row['timestamp'];
		$timestamp = strtotime($timestamp);
		$date = date('d.m.Y', $timestamp);
				
		if ( $date != $lastdate ) {
			if ($lastdate="") {
				$page .= " </ul>\n</p>\n";
			}
			$lastdate = $date;
			$page .= "<p>\n$date\n <ul>\n";
		}
		
		converttweet($tweet);
		$tweet = mysql_real_escape_string($tweet);
		$writequery = "INSERT INTO tweets (id, username, tweet, url, timestamp) VALUES (". $id. ", '". $username. "', '". $tweet. "','" . $url . "', '" . $row['timestamp'] . "');";
		$writeresult = mysql_query($writequery);
			
		if ($writeresult) {
			$page .= "<li>$id: $tweet ($url)</li>";
			$changed = true;
		}		
	}
	mysql_close($db);

	$page .= " </ul>\n</p></body></html>";
	echo $page;
	
	if ($changed == true) {
		require_once("print.php");
	}

?>