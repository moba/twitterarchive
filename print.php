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

	$page = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html>
 <head>
  <title>gamambel's Newsticker - Politics, Journalism &amp; Media, Censorship, Software Development</title>
  <link rel="stylesheet" type="text/css" href="style.css">
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
  <link rel="alternate" href="http://twitter.com/statuses/user_timeline/147903457.rss" title="gamambel's Tweets" type="application/rss+xml">
 </head>
<body>
<h1>gamambel's Newsticker</h1>
<ul>
<li>Disclaimer: I use Twitter to collect and distribute information. Neither the tweets nor the linked pages necessarily reflect the truth or my own opinion. Like any media: Use with care.</li>
</ul>
EOF;
	
	$query  = "SELECT id, tweet, url, timestamp, language FROM tweets WHERE hide=0 ORDER BY timestamp DESC";
	$result = mysql_query($query);

	$lastdate = "";
	$firstdayover = 0; // used to insert banner after first day
	while($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		$id = $row['id'];
		$tweet = $row['tweet'];
		$url = $row['url'];
		$timestamp = $row['timestamp'];
		$language = $row['language'];
		$timestamp = strtotime($timestamp);
		$date = date('d.m.Y', $timestamp);
		
		if ( $date != $lastdate ) {
			if ($lastdate!="") {
				$page .= "</ul>";
				if ($firstdayover == false) {
					$page .= <<<EOF
<div class="box">
 If you want to help getting uncensored information to people behind oppressive governmental filters, consider donating to <a href="http://www.torservers.net/">torservers.net</a>.
</div>
EOF;
					$firstdayover = true;
				}
			}
			$lastdate = $date;
			$page .= "\n<h1 id=\"$date\"><a href=\"#$date\" class=\"nostyle\">$date</a></h1>\n<ul>\n";
		}
		
		$class = "";
		if ($language) { $class = " class=\"$language\""; }
		$page .= " <li id=\"$id\"$class><a href=\"$url\" class=\"nostyle\">$tweet</a></li>\n";
	}
	mysql_close($db);

	$page .= <<<EOF
</ul>
<h1>
 <a href="http://twitter.com/gamambel/">http://twitter.com/gamambel/</a>
</h1>
</body></html>
EOF;
	echo $page;

	file_put_contents("index.html", $page);
	
?>