<?php
/**
 * -----------------------------------------------------------------------------
 *
 * based on TwitterZoid PHP Script
 * Copyright (c) 2008 Philip Newborough <mail@philipnewborough.co.uk>
 *
 * http://crunchbang.org/archives/2008/02/20/twitterzoid-php-script/
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * http://www.gnu.org/licenses/
 *
 * -----------------------------------------------------------------------------
 */

 function converttweet(&$text, $target='_blank', $nofollow=false){
	$text = str_replace("http://http://", "http://", $text);	

    $urls  =  _autolink_find_URLS( $text );
    if(!empty($urls)){
      array_walk( $urls, '_autolink_create_html_tags', array('target'=>$target, 'nofollow'=>$nofollow) );
      $text  =  strtr( $text, $urls);
    }
    $text = preg_replace("/(\s@|^@)([a-zA-Z0-9_]{1,25})/","",$text);
	$text = str_replace("RT: ", "", $text);
	$text = str_replace("(via)", "", $text);
	$text = substr( $text, strpos($text,':')+2);
	
//	$text .= "<br/>" . implode ( $urls, " ");
}
function _autolink_find_URLS($text){
    $scheme = '(http:\/\/|https:\/\/)';
    $www = 'www\.';
    $ip = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
    $subdomain = '[-a-z0-9_]+\.';
    $name = '[a-z][-a-z0-9]*\.';
    $tld = '[a-z]+(\.[a-z]{2,2})?';
    $the_rest = '\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1}';            
    $pattern = "$scheme?(?(1)($ip|($subdomain)?$name$tld)|($www$name$tld))$the_rest";    
    $pattern = '/'.$pattern.'/is';
    $c = preg_match_all($pattern, $text, $m);
    unset($text, $scheme, $www, $ip, $subdomain, $name, $tld, $the_rest, $pattern);
    if($c){
        return(array_flip($m[0]));
    }
    return(array());
}
function _autolink_create_html_tags(&$value, $key, $other=null){
	resolve_url($key);

    $target = $nofollow = null;
    if(is_array($other)){
        $target = ($other['target'] ? " target=\"$other[target]\"":null);
        $nofollow = ($other['nofollow'] ? ' rel="nofollow"':null);     
    }
    //We'll not bother with nofollow and target for twitter
    //$value = "<a href=\"$key\"$target$nofollow>$key</a>";
	
	preg_match('@^(?:http:\/\/|https:\/\/)?(www\.)?([^/]+)@i', $key, $matches);
	$host = $matches[2];	
	
    $value = "<a href=\"$key\">$host</a>";
}

function resolve_url(&$url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	$result = curl_exec($ch); 

	if (preg_match("/Location\:/","$result")) {
		$url = explode("Location: ",$result);
		$reversed_url = explode("\r",$url[1]);
		$url = $reversed_url[0];
	}
}
?>
