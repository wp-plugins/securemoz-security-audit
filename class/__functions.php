<?php

function _GET($QS , $defaultValue, $addslashes = false)
{
	if(isset($_GET[$QS])){
		if($addslashes)
			return	mysql_escape_string($_GET[$QS]);
		else
			return	$_GET[$QS];
	}else
		return	$defaultValue;
}

function _POST($QS , $defaultValue, $addslashes = false)
{
	if(isset($_POST[$QS])){
		if($addslashes)
			return	mysql_escape_string($_POST[$QS]);
		else
			return	$_POST[$QS];
	}else
		return	$defaultValue;
}
function _REQUEST($QS , $defaultValue, $addslashes = false)
{
	if(isset($_REQUEST[$QS])){
		if($addslashes)
			return	mysql_escape_string($_REQUEST[$QS]);
		else
			return	$_REQUEST[$QS];
	}else
		return	$defaultValue;
}
function redirect($url){
	header("location: $url");
	exit();
}
function error($str, $done = false){
	if($done)
		echo "<div id='done'>".stripslashes($str)."</div>";
	else
		echo "<div id='error'>".stripslashes($str)."</div>";
}
function bad_error($str){
	global $SOURCE, $_LANG;
	
	echo '<div class="bader">' . $str . '</div>';

	if($SOURCE == "EMBED"){
		echo '<a href="Javascript:;" onclick="history.go(-1);" class="tryagain">' . $_LANG["Try Again"] . '</a>';
	}

}
function good_error($str){
	echo '<div class="gooder">' . $str . '</div>';
}

function _s($str, $strip_tags	=	false){
	if($strip_tags)
		return htmlentities(strip_tags(stripslashes($str)));
	else
		return htmlentities(stripslashes($str));
}



function fetch_curl($url, $op = false){

	$ch	=	curl_init();
	
	// cookie file
	if($op['COOKIE'])
		curl_setopt($ch, CURLOPT_COOKIEJAR, $op['COOKIE']);
	
	
	curl_setopt($ch, CURLOPT_URL, $url);
	
	
	// return header
	if(!$op['HEADER'])
		curl_setopt($ch, CURLOPT_HEADER, 0);
	else
		curl_setopt($ch, CURLOPT_HEADER, $op['HEADER']);
		

	if($op['NOBODY'])
		curl_setopt($ch, CURLOPT_NOBODY, 0);
	else
		curl_setopt($ch, CURLOPT_NOBODY, $op['NOBODY']);

	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($ch, CURLOPT_USERAGENT, getRandomAgent());
	

	$html	=	curl_exec($ch);
	curl_close($ch);


	$html	=	get_safe_utf8($html);
	return $html;
}



function fetch($url){

	$content	=	file_get_contents($url);
	$content	=	get_safe_utf8($content);
	
	return $content;
}


function get_safe_utf8($content){
	if (function_exists('iconv')) {
		if(preg_match("#<meta.*?charset=(.*?)(\"|'| |>)#",$content,$charset))
		{
			if(@$charset[1] && strtolower($charset[1])!="utf-8")
			{
				$content = iconv($charset[1],"UTF-8",$content);
			}
		}
	}
	return $content;
}




function get_domain_name($url){
	$domain	=	parse_url($url);
	return $domain['host'];
}

function filter_url($url){
	$url_arr	=	parse_url($url);
	$scheme		=	$url_arr['scheme'];
	
	if(!stristr($scheme, 'http'))
		$url	=	'http://' . $url;
	
	return $url;
}


function get_complete_url($url){
	$url_arr	=	parse_url($url);
	$scheme		=	$url_arr['scheme'];
	
	if(!stristr($scheme, 'http'))
		$url	=	'http://' . $url;
	else
		return false;
	
	return $url;
}

function get_domain_only($url){
	$url_arr	=	parse_url($url);
	return $url_arr['host'];
}


function check_permission($CID){
	
	global $FREE_LIMIT, $ROOT_URL;

	$ip	=	getIP();
	$agent	=	$_SERVER['HTTP_USER_AGENT'];
	$pre_day_timestamp	=	mktime() - 60*60*24;
	$today	=	mktime();
	
	$sql	=	"SELECT COUNT(`id`) FROM `stats` WHERE 
												`t` > '$pre_day_timestamp' AND  
												`t` < '$today' AND 
												`ip` = '$ip' AND 
												`tool` = '$CID' AND 
												`agent` = '$agent'
												";
	
	list($count)	=	mysql_fetch_row(mysql_query($sql));

	if($count > $FREE_LIMIT){
		bad_error('You have exceeded your daily limit for using this tool! Please wait for <strong>24</strong> hours to use this tool again. <br /><br /><b>If you want to remove this limit and get extra features and benefit, become our <a href="'.$ROOT_URL.'premium/" target="_blank">premium member</a></b>.');
		exit();
	}
	
}

function store_stats($CID, $q){
	global $SOURCE;
	
	$ip		=	getIP();
	$refer	=	$_SERVER['HTTP_REFERER'];
	$agent	=	$_SERVER['HTTP_USER_AGENT'];
	$today	=	mktime();
	
	if($SOURCE == "EMBED")
		$source	=	'E';
	else
		$source	=	'D';
	
	$sql	=	"INSERT INTO `stats` SET 
										`tool`= '$CID', 
										`ip` = '$ip', 
										`refer` = '$refer', 
										`agent` = '$agent', 
										`source` = '$source', 
										`q` = '$q', 
										`t` = '$today'
										";
	mysql_query($sql);
}

function getIP(){
	return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
}


function getAlexa($url){
	$url	=	"http://data.alexa.com/data?cli=10&dat=snbamz&url=".$url;
	if(!($xml	=	simplexml_load_file($url))) return "-";
	
	return $xml->SD->POPULARITY['TEXT'];
}
function getAlexaRank($url) {
$url = @parse_url($url);
$url = $url['host'];
$url = "http://data.alexa.com/data?cli=10&dat=s&url=$url";
$data = getPage($url);
preg_match('#<POPULARITY URL="(.*?)" TEXT="([0-9]+){1,}"/>#si', $data, $p);
$value = ($p[2]) ? number_format($p[2]) : 0;
return $value;
}

function prImage($pr){

	global $ROOT_URL;
	$pr	=	trim($pr);
	
	if($pr == "")
		return '<img src="' . $ROOT_URL . 'images/pr/0.png" alt="Google Pagerank 0 / 10" width="80" height="15" />';
	else
		return '<img src="' . $ROOT_URL . 'images/pr/' . $pr . '.png" alt="Google Pagerank ' . $pr . ' / 10" width="80" height="15" />';

}

function getRandomAgent(){

	list($agent)	=	mysql_fetch_row(mysql_query("SELECT `agent` FROM `agents` ORDER BY rand() LIMIT 0, 1"));
	return trim($agent);

}

function getRandomProxy(){

	list($proxy)	=	mysql_fetch_row(mysql_query("SELECT `proxy` FROM `proxy` ORDER BY rand() LIMIT 0, 1"));
	return trim($proxy);

}


function strip_white_spaces($str)
{
	preg_match_all("!<script[^>]+>.*?</script>!is", $str, $match);
	$_script_blocks	=	$match[0];
	$str			=	preg_replace("!<script[^>]+>.*?</script>!is",'', $str);

	preg_match_all("!<pre>.*?</pre>!is", $str, $match);
	$_pre_blocks	=	$match[0];
	$str			=	preg_replace("!<pre>.*?</pre>!is",'', $str);

	preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $str, $match);
	$_textarea_blocks	=	$match[0];
	$str				=	preg_replace("!<textarea[^>]+>.*?</textarea>!is",'', $str);

	$str				=	trim(preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $str));	
	
	return $str;
}


function relative2absolute($absolute, $relative) {
	$p = @parse_url($relative);
	if(!$p) {
		return false;
	}
	if(isset($p["scheme"])) return $relative;
	 
	$parts=(parse_url($absolute));
	 
	if(substr($relative,0,1)=='/') {
		$cparts = (explode("/", $relative));
		array_shift($cparts);
	} else {
		if(isset($parts['path'])){
			$aparts=explode('/',$parts['path']);
			array_pop($aparts);
			$aparts=array_filter($aparts);
		} else {
			$aparts=array();
	}
	$rparts = (explode("/", $relative));
	$cparts = array_merge($aparts, $rparts);
	foreach($cparts as $i => $part) {
		if($part == '.') {
			unset($cparts[$i]);
		} else if($part == '..') {
			unset($cparts[$i]);
			unset($cparts[$i-1]);
		}
	}
	}
	$path = implode("/", $cparts);
	 
	$url = '';
	if($parts['scheme']) {
		$url = "$parts[scheme]://";
	}
	if(isset($parts['user'])) {
		$url .= $parts['user'];
	if(isset($parts['pass'])) {
		$url .= ":".$parts['pass'];
	}
	$url .= "@";
	}
	if(isset($parts['host'])) {
		$url .= $parts['host']."/";
	}
	$url .= $path;
	 
	return $url;
}

function get_meta_data($CID){
	return mysql_fetch_row(mysql_query("SELECT `meta_title`, `meta_keywords`, `meta_description` FROM `tools` WHERE `id` = '$CID'"));
}

function get_country_code($userIP)
{
	list($countryCode) = mysql_fetch_row(mysql_query("SELECT `country` FROM `ip2nation`  WHERE `ip` < INET_ATON('$userIP') ORDER BY `ip` DESC LIMIT 0,1"));
	return $countryCode;
}

function get_country_name($code)
{
	$sql = "SELECT `country` FROM ip2nationcountries  WHERE `code` = '$code' LIMIT 0,1";
	list($country) = mysql_fetch_row(mysql_query($sql));
	return $country;
}

function get_headers_x($url, $format=0)
{
       $url_info=parse_url($url);
        $port = isset($url_info['port']) ? $url_info['port'] : 80;
        $fp=fsockopen($url_info['host'], $port, $errno, $errstr, 30);
        if($fp) {
            $head = "HEAD ".@$url_info['path']."?".@$url_info['query'];
            $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n\r\n";
            fputs($fp, $head);
            while(!feof($fp)) {
                if($header=trim(fgets($fp, 1024))) {
                    if($format == 1) {
                        $h2 = explode(':',$header);
                        // the first element is the http header type, such as HTTP/1.1 200 OK,
                        // it doesn't have a separate name, so we have to check for it.
                        if($h2[0] == $header) {
                            $headers['status'] = $header;
                        }
                        else {
                            $headers[strtolower($h2[0])] = trim($h2[1]);
                        }
                    }
                    else {
                        $headers[] = $header;
                    }
                }
            }
            return $headers;
        }
        else {
            return false;
        }
}


function _getFileSize($url){
	$hdata = get_headers($url, 1);
	$size = (is_array($hdata['Content-Length']))?$hdata['Content-Length'][0]:$hdata['Content-Length'];
	if($size == "") return "-";
	else return $size;
}

function _isReadable($return)
{
	if($return)
	{
				
      $size = round($return/1024, 2);
      $sz = "KB"; // Size In KB
	        if ($fsize > 1024) 
	        {
	            $size = round($return / 1024, 2);
	            $sz = "MB"; // Size in MB
	        }
			
				$return = "$size $sz";
	}
   return $return;
}

function _getMetaTitle($content){
	$pattern = "|<[\s]*title[\s]*>([^<]+)<[\s]*/[\s]*title[\s]*>|Ui";
	$resTitle = preg_match_all($pattern, $content, $match);
	$title=$match[1][0];
	return $title;
}

function _getMetaDescription($content) {
$urldata = getUrlData($content);
return $urldata['metaTags']['description']['value'];
}


function _getW3Validation($url)
{
		$newUrl =  "http://validator.w3.org/check?uri="	.	$url;
		$newStr	=	fetch($newUrl);
		$image_regex_src_url2	=	'/Passed/';
		preg_match_all($image_regex_src_url2, $newStr, $out1, PREG_PATTERN_ORDER);  
	
		if($out1[0][0]=='Passed'){
			$w3validation	=	'<font color="green">Passed</font>';
		}else{
			preg_match('/<td colspan="2" class="invalid">([^<]+)/i', $newStr, $er);  
			if(count($er) > 0)
				$w3validation	=	'<a href="http://validator.w3.org/check?uri=' . htmlentities($url) . '" target="_blank"><font color="red">' . _s($er[1], true) . '</font></a>';
			else
				$w3validation	=	"-";
		}
	
	return $w3validation;
}


function _getCodeRatio($contents)
{
	$text	=	strip_tags($contents);
	$ratio	=	sprintf("%01.2f", strlen($contents) / strlen($text));
	return $ratio;
}

function _getImageUrl($contents)
{
	$image_regex_src_url = '/<img[^>]*'.'src=["|\'](.*)["|\']/Ui';
	preg_match_all($image_regex_src_url, $contents, $out, PREG_PATTERN_ORDER); 
	$images_url_array = $out[1];
	return  $images_url_array;
}

function _extractCssUrls($content, $url)
{
	preg_match_all('/<link(.*)>/Ui',$content,$match);
			$valid_css	=	array();
			foreach($match[1] as $m)
			{
				if(stristr($m, "text/css"))
				{
					$valid_css[]	=	$m;	
				}
			}
			
				$image_regex_src_url = '/href=["|\'](.*)["|\']/Ui';
			
			$valid_css_link	=	array();
			foreach($valid_css as $vc)
			{
				preg_match($image_regex_src_url, $vc, $css_link);
				if(count($css_link) > 0)
				{
					$css_url = explode('=',$css_link[0]);
					$newLink = substr($css_url[1],1,-1);
					$valid_css_link[]	=	relative2absolute($url , $newLink);
				}
			} 
	return $valid_css_link;
}



function extract_css_urls( $text )
{
	$urls = array( );

	$url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
	$urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
	$pattern         = '/(' .
		 '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
		'|(@import\s*'      . $urlfunc_pattern . ')'      .
		'|('                . $urlfunc_pattern . ')'      .  ')/iu';
	if ( !preg_match_all( $pattern, $text, $matches ) )
		return $urls;
	
	// @import '...'
	// @import "..."
	foreach ( $matches[3] as $match )
		if ( !empty($match) )
			$urls['import'][] = preg_replace( '/\\\\(.)/u', '\\1', $match );

	// @import url(...)
	// @import url('...')
	// @import url("...")
	foreach ( $matches[7] as $match )
		if ( !empty($match) )
			$urls['import'][] = preg_replace( '/\\\\(.)/u', '\\1', $match );

	// url(...)
	// url('...')
	// url("...")
	foreach ( $matches[11] as $match )
		if ( !empty($match) )
			$urls['property'][] = preg_replace( '/\\\\(.)/u', '\\1', $match );

	return $urls;
	
}


function extractAllLinks($contents, $url){
	
	$urls	=	array();
	
	$html	=	str_get_html($contents);
	foreach($html->find('a') as $e) {
	    $u	=	relative2absolute($url, $e->href);
		
		// Filtering URL
		if($u != $url){		// Is it not main url?
			if(!stristr($u, "javascript:"))
				if(!stristr($u, "mailto:"))
					if(!stristr($u, "skype:"))
						if(!stristr($u, "yahoo:"))
							if(!stristr($u, "data:"))
								$urls[]	=	$u;
		}

	}
	$urls	=	array_unique($urls);
	
	$result	=	array();
	
	// getting internal and external links
	foreach($urls as $u){
		if(stristr(str_replace("www.", "", $u), str_replace("www.", "", $url)))
			$result['INTERNAL'][]	=	$u;
		else
			$result['EXTERNAL'][]	=	$u;
	}
	
	
	return $result;
}

function extractTotalImages($contents){
	$html	=	str_get_html($contents);
	return count($html->find('img'));
}

function extractAllImgUrls($contents, $url){

	$urls	=	array();
	$html	=	str_get_html($contents);
	foreach($html->find('img') as $e) {
	    $u	=	relative2absolute($url, $e->src);
		
		if($u != $url)
			$urls[]	=	$u;
	}
	$urls	=	array_unique($urls);
	
	return $urls;
}

function change_number_percent($n, $p){
	
	$direction_up	=	(rand(1, 2) == 1)?true:false;
	$change_percentage	=	rand(1, $p);
	$n_percentage	=	number_format((($n*$change_percentage)/100), 0, '', '');
	
	
	if($direction_up){
		$n	=	$n+$n_percentage;
	}else{
		$n	=	$n-$n_percentage;
	}
	return $n;
}

function get_backlinks($what, $domain){
	
	global $YAHOO_KEY;
	
	if($what	==	"GOOGLE"){
		$google	=	get_web_page("http://www.google.com/search?q=link:" . urlencode(htmlentities($domain)) . "&hl=en");
		preg_match('#<div id="resultStats">(.*?) results#is',$google,$google);
		$google		=	$google[1];
		return $google;
		
	}elseif($what	==	"MSN"){

		$msn	=	fetch("http://www.bing.com/search?q=links:" . $domain . "");
		preg_match("#([\\d,]+) results</span>#",$msn,$msn);
		$msn		=	preg_replace("#\\D#","",@$msn[1]);
		return $msn;

	}elseif($what	==	"YAHOO"){
		$yahoo	=	fetch("http://siteexplorer.search.yahoo.com/search?p=" . htmlentities($domain) . "&bwm=i&bwmo=d&bwmf=u");
		preg_match('/<span class="btn">Inlinks \((.*?)\)/is',$yahoo,$yahoo);
		$yahoo		=	str_replace(",","",$yahoo[1]);
		return $yahoo;
	}
	
	return "0";
}

function total_searches($what, $keyword){
	
	
	if($what	==	"GOOGLE"){
		
		$google	=	fetch("http://www.google.com/search?q=" . urlencode($keyword) . "&hl=en");
		preg_match("#About (.*?) results#is",$google,$google);
		$google		=	$google[1];
		return $google;
		
	}elseif($what	==	"MSN"){

		$msn	=	fetch("http://www.bing.com/search?q=:" . urlencode($keyword) . "");
		preg_match("#([\\d,]+) results</span>#",$msn,$msn);
		$msn		=	$msn[1];
		return $msn;

	}elseif($what	==	"YAHOO"){
		$yahoo	=	fetch("http://siteexplorer.search.yahoo.com/search?p=" . htmlentities($domain) . "&bwm=i&bwmo=d&bwmf=u");
		preg_match('/<span class="btn">Inlinks \((.*?)\)/is',$yahoo,$yahoo);
		$yahoo		=	str_replace(",","",$yahoo[1]);
		return $yahoo;
	}
	
	return "0";
}

function get_pages_index($what, $domain){
	
	global $YAHOO_KEY;
	
	if($what	==	"GOOGLE"){
		
		$google	=	fetch("http://www.google.com/search?q=site:" . htmlentities($domain) . "&hl=en");
		preg_match("/About (.*?) results/",$google,$google);
		return $google[1];
		
	}elseif($what	==	"MSN"){

		$msn	=	file_get_contents("http://www.bing.com/search?q=site:" . htmlentities($domain) . "");
		preg_match("#1-([0-9]+) of ([\\d,]+) results</span>#",$msn,$msn);
		$msn		=	preg_replace("#\\D#","",@$msn[2] ? $msn[2] : 0);
		return $msn;

	}elseif($what	==	"YAHOO"){

		$yahoo	=	fetch_curl("http://search.yahooapis.com/WebSearchService/V1/webSearch?appid=bIpqc43V34G.6UQFQGZVHGYdwJEKr10H9DcBvfFbrB1au6uVZA1jVHkV9wJubMSTjZG9Ma4wpoA-&query=" . htmlentities($domain) . "&results=1");
		preg_match("#<ResultSet .*?totalResultsAvailable=\"(\\d+)\"#",$yahoo,$yahoo);
		$yahoo		=	preg_replace("#\\D#","",@$yahoo[1] ? $yahoo[1] : 0);
		return $yahoo;
	}
	
	return "0";
}

function get_visitors_and_worth($domain){

	$Rss = new Rss; 
	
	$page_views	=	get_cache_data($domain, 'page_views');
	$worth		=	get_cache_data($domain, 'worth');
	$daily_earning		=	get_cache_data($domain, 'daily_earning');
	$daily_unique		=	get_cache_data($domain, 'daily_unique');
	

	if($page_views	==	false || $worth == false || $daily_earning	==	false || $daily_unique	==	false){

		$newurl	=	'http://www.cubestat.com/'.$domain.'.rss';
		$content= '';
	
		$feed = $Rss->getFeed($newurl);
	
		foreach($feed as $item)
		{
			$content = "$item[description]";
		}
	
		$worth				=	number_format(_getTotalWorth($content), 0, '', '');
		$page_views			=	number_format(_getDailyPageViews($content), 0, '', '');
		$daily_earning		=	number_format(_getDailyAdsRev($content), 0, '', '');
		
		$uni_content		=	fetch_curl('http://www.mywebsiteworth.com/site/'.$domain);
		$pages_per_visit	=	_getUniVisits($uni_content);
		
		if($page_views != 0 and $pages_per_visit != 0){
			$daily_unique 	= 	ceil($page_views/$pages_per_visit);
		}else{
			$daily_unique	=	$page_views;	//Daily Unique Visitors
		}
		

		set_cache_data($domain, 'worth', $worth);
		set_cache_data($domain, 'page_views', $page_views);
		set_cache_data($domain, 'daily_earning', $daily_earning);
		set_cache_data($domain, 'daily_unique', $daily_unique);

	}
	
	$return	=	array(
					  'WORTH'		=>	$worth, 
					  'PAGE_VIEWS'	=>	$page_views, 
					  'DAILY_EARNING'	=>	$daily_earning, 
					  'DAILY_UNIQUE'	=>	$daily_unique
					);

	return $return;
}

function _getTotalWorth($content)
{
	$image_regex_src_url = '/Website Worth: (.*)</Ui';
	preg_match($image_regex_src_url, $content, $worth);

	$newWorth	=	substr($worth[1],1);
	$w_com		=	str_replace(",","",$newWorth);
	$w_dec		=	number_format((floor($w_com)), 0, '', '');;
	
	
	// Randomize It
	if($w_dec != "" &&  $w_dec != 0){
		$w_dec	=	change_number_percent($w_dec, 7);
	}
	
	
	return $w_dec;
}

function _getDailyPageViews($content)
{
	$image_regex_src_url = '/Daily Pageviews: (.*)</Ui';
	preg_match($image_regex_src_url, $content, $worth);

	$d_view = $worth[1];
	$daily_view = str_replace(",","",$d_view);

	// Randomize It
	if($daily_view != "" &&  $daily_view != 0){
		$daily_view	=	change_number_percent($daily_view, 7);
	}

	return $daily_view;
}

function _getDailyAdsRev($content)
{
	$image_regex_src_url = '/Daily Ads Revenue: (.*)</Ui';
	preg_match($image_regex_src_url, $content, $worth);

	$newWorth = substr($worth[1],1);
	$w_com = str_replace(",","",$newWorth);
	$w_dec = number_format((floor($w_com)), 0, '', '');

	// Randomize It
	if($w_dec != "" &&  $w_dec != 0){
		$w_dec	=	change_number_percent($w_dec, 10);
	}
	
	return $w_dec;
}

function _getUniVisits($content)
{
	$image_regex_src_url = '/Visit:<\/B><\/td>(.*)People/si';
	preg_match($image_regex_src_url, $content, $worth1);

	$w_dec	=	trim(strip_tags($worth1[1]));
	
	// Randomize It
	if($w_dec != "" &&  $w_dec != 0){
		$w_dec	=	change_number_percent($w_dec, 10);
	}

	return $w_dec;
}


function _getMothlyEarning($amount)
{
	
	$m_earning  = number_format((str_replace(",","",$amount) * 30), 0, '', '');
	return $m_earning;
	
}

function _getGoogleAdCount($js_tags)
{
	$count	=	0;
	foreach($js_tags as $js){
		if(stristr($js, 'googlesyndication.com'))	$count++;
	}
	return $count;
}


function _getYahooAdCount($js_tags)
{
	$count	=	0;
	foreach($js_tags as $js){
		if(stristr($js, 'ypn-js'))	$count++;
	}
	return $count;
}
function _getValueClickAdCount($js_tags)
{
	$count	=	0;
	foreach($js_tags as $js){
		if(stristr($js, 'fastclick.net'))	$count++;
	}
	return $count;
}

function _getMSNAdCount($js_tags)
{
	$count	=	0;
	foreach($js_tags as $js){
		if(stristr($js, 'msn'))	$count++;
	}
	return $count;
}

function _getMonthlyUniVisitors($res)
{
	$result = (intval($res) * 30);
	return $result;
}

function _getJavascriptTags($src){
	return search("<script","</script>",$src,false);
}

function random_str($len){
	$ch	=	"ABCDEFGHKMNPRSTX23456789";	// removed confusing characters
	$l	=	strlen ($ch) - 1;
	$str	=	"";
	for($i=0; $i < $len; $i++){
		$x	=	rand (0, $l);
		$str	.=	$ch[$x];
	}
	return strtolower ($str);
}

function ln2ln($str){
	$str	=	str_replace('\\n', "\n", $str);
	$str	=	str_replace('\\r', "", $str);
	return $str;
}


function search($start,$end,$string, $borders=true){
	$reg="!".preg_quote($start)."(.*?)".preg_quote($end)."!is";
	preg_match_all($reg,$string,$matches);

	if($borders) return $matches[0];
	else return $matches[1];

}

function getMainCategoryURL($tool){
	list($category)	=	mysql_fetch_row(mysql_query("SELECT `category` FROM `tools` WHERE `id` = '" . strtoupper($tool) . "'"));
	if($category == "SEO")
		return "seo-tools";
	elseif($category == "WEBMASTER")
		return "webmaster-tools";
	elseif($category == "MISC")
		return "misc";
	elseif($category == "DOMAIN")
		return "domain-tools";
	else
		return false;
}

function _parseEmbedCSS($text){
	global $_SETTINGS;
	foreach($_SETTINGS as $set => $key){
		$text	=	str_replace("__".strtoupper($set), $key, $text);
	}
	
	return $text;
}

function get_member_id_from_api($api){
	list($id)	=	mysql_fetch_row(mysql_query("SELECT `id` FROM `members` WHERE `api_key` = '$api' LIMIT 0, 1"));	
	return $id;
}

function get_member_settings($member_id){
	return	mysql_fetch_array(mysql_query("SELECT * FROM `member_settings` WHERE `member_id` = '$member_id' LIMIT 0, 1"));
}

function get_embed_code($tool){
	global $ROOT_URL;
	return htmlentities($ROOT_URL)	.	"service/"	.	strtolower($tool)	.	".js";
}

function get_data_table_name($domain){
	if(substr($domain, 0, 4) == "www.")
		$domain	=	substr_replace($domain, "", 0, 4);

	return "data_1";			// IIIIIIIIIIIIIIIIIIIIIIIII WILLLLLLLLLLLLLLLLLLLLLLLLLLLLLL DOOOOOOOOOOOOOOOOOOO ITTTTTTTTTTTTTTTTTTTTTTTTTTTTTT
}

function get_cache_data($url, $data_name){
	global $CACHE_EXPIRE_DAY;

	// Validating Domain
	$url	=	trim(strip_tags($url));
	if($url == "" || $url == "http://")	return false;
	if(substr($url, 0, 7) != "http://")	$url	=	"http://" . $url;
	
	
	$domain	=	trim(htmlentities(get_domain_name($url)));	
	if(substr($domain, 0, 4) == "www.")
		$domain	=	substr_replace($domain, "", 0, 4);


	if($domain	==	"")	return false;
	
	$data_table_name	=	get_data_table_name($domain);

	// Quering Database
	list($last_update, $data)	=	mysql_fetch_row(mysql_query("SELECT `last_update`, `$data_name` FROM `$data_table_name` WHERE `domain` = '$domain' LIMIT 0, 1"));	
	if($last_update	==	""	||	trim($data)	==	"")	return false;
	
	// Getting Cache Expire Time
	$cache_time_in_seconds	=	mktime(0,0,0,date('m'),date('d')-$CACHE_EXPIRE_DAY,date('Y'));
	
	if($last_update	<	$cache_time_in_seconds){
		return false;		// Redo same procedure and store in database
	}else{
		return $data;
	}
}

function set_cache_data($url, $data_name, $data){

	// Validating Domain
	$url	=	trim(strip_tags($url));
	if($url == "" || $url == "http://")	return false;
	if(substr($url, 0, 7) != "http://")	$url	=	"http://" . $url;
	
	
	$domain	=	trim(htmlentities(get_domain_name($url)));	
	if(substr($domain, 0, 4) == "www.")
		$domain	=	substr_replace($domain, "", 0, 4);


	if($domain	==	"")	return false;
	
	$data_table_name	=	get_data_table_name($domain);


	// Checking if record already exists
	$t	=	mktime();
	list($yes)	=	mysql_fetch_row(mysql_query("SELECT count(`domain`) FROM `$data_table_name` WHERE `domain` = '$domain'"));	
	if($yes)
		mysql_query("UPDATE `$data_table_name` SET `$data_name` = '$data', `last_update` = '$t' WHERE `domain` = '$domain'");
	else
		mysql_query("INSERT INTO `$data_table_name` SET `domain` = '$domain', `$data_name` = '$data', `last_update` = '$t'");
}



function shortenText($longString, $len = 50) {
	
	if(strlen($longString) <= $len) return $longString;
	
	$separator = '...';
	$separatorlength = strlen($separator) ;
	$maxlength = $len - $separatorlength;
	$start = $maxlength / 2 ;
	$trunc =  strlen($longString) - $maxlength;
	return substr_replace($longString, $separator, $start, $trunc);
}

function validate_ip($ip_addr){
	
	if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$ip_addr)){
		$parts	=	explode(".",$ip_addr);
		foreach($parts as $ip_parts){
			if(intval($ip_parts)>255 || intval($ip_parts)<0)
				return false;
		}
		return true;
	}else
		return false;
}

function is_tool_exists($tool){
	list($c)	=	mysql_fetch_row(mysql_query("SELECT COUNT(id) FROM `tools` WHERE `id` = '$tool'"));
	if($c > 0)	return true;
	else	return false;
}

function error_404_image(){
	global	$ROOT_URL;
	header('Content-Type: image/jpeg');
	readfile($ROOT_URL . "service/image/image_404.jpg");
}

function error404(){

	header("HTTP/1.0 404 Not Found"); 
	include("404.php");
	exit();
	
}

function get_html_page_title($title, $title2){
	global $_complete_domain, $_complete_url;
	if($_complete_domain != ""){
		return $title2 . '<a href="' . $_complete_url . '" target="_blank">' . $_complete_domain . '</a>';	
	}else{
		return $title;	
	}
}

function get_html_page_submit($submit){
	global $_complete_domain;
	if($_complete_domain != ""){
		echo '<script language="javascript">
' . $submit . '
</script>
';
	}
}

function get_html_page_input($default = "http://"){
	global $_complete_domain, $_complete_url;
	if($_complete_domain != ""){
		return htmlentities($_complete_url);
	}else{
		return $default;
	}
}

function get_tool_snapshot($dir, $tool, $domain){
	global $IMAGES_OUTPUT_PATH, $ROOT_URL;
	
	$tool	=	strtolower($tool);
	
	$image_file_name	=	$IMAGES_OUTPUT_PATH . $tool . "\\" . $domain . ".jpg";
	
	if(file_exists($image_file_name)) {
		$alt	=	str_replace("_", " ", $tool) . " of " . $domain;
		$path	=	$ROOT_URL . $dir . "/" . str_replace("_", "-", $tool) . "/" . $domain . ".jpg";
		
		echo '<p align="center">';
		echo '<img src="' . $path . '" alt="' . $alt . '" title="' . $alt . '" />';
		echo '</p><br />';
	}
}

function get_google_related_websites($domain, $limit = 10){

		$content	=	get_web_page("http://www.similarsites.com/site/".$domain);
		preg_match_all('#title="Visit (.*?)"#is' , $content , $urls);
		
		$u	=	array_unique($urls[1]);
		return $u;
}

function get_share_add_buttons($alt, $op = false){
	global $ROOT_URL;
	
	echo '<div id="toolbuttons">';
	
	if($op == false || in_array('SHARE', $op))
		echo '<a href="#share"><img src="' . $ROOT_URL . 'images/share-button.gif" alt="Share ' . $alt . '" width="73" height="21" hspace="3" border="0" align="middle" /></a>';

	if($op == false || in_array('ADD', $op))
		echo '<a href="#add"><img src="' . $ROOT_URL . 'images/add-button.gif" alt="Add ' . $alt . ' on your website" width="73" height="21" hspace="3" border="0" align="middle" /></a>';

	echo '</div>';
}

// This function will explode array from text 
function get_array_from_text($text, $check_unique = true){
	$result	=	array();
	
	$text	=	trim($text, "*-_-*");
	if($check_unique){
		$result	=	explode("*-_-*", $text);
		if(count($result) > 0)		$result	=	array_unique($result);
	}else{
		explode("*-_-*", $text);	
	}
	return $result;	
}
// This function will implode array of text
function get_text_from_array($array, $check_unique = true){
	$result	=	"";
	if($check_unique){
		if(count($array) > 0)		$array	=	array_unique($array);
		$result	=	implode("*-_-*", $array);
	}else{
		implode("*-_-*", $array);	
	}
	return $result;
}

function add_array_element_in_text($text, $e, $add_first = true){
	$result	=	array();
	
	$text	=	trim($text, "*-_-*");
	
	if($add_first)		$result[]	=	$e;
	$result	=	array_merge($result, get_array_from_text($text));
	if(!$add_first)		$result[]	=	$e;
	
	return get_text_from_array($result);
}


function store_latest_searches($CID, $url){
	list($search)	=	mysql_fetch_row(mysql_query("SELECT `latest_searches` FROM `tools` WHERE `id` = '$CID' LIMIT 0, 1"));
	mysql_query("UPDATE `tools` SET `latest_searches` = '" . add_array_element_in_text($search, $url) . "' WHERE `id` = '$CID'");
}


function get_latest_searches($CID){
	$result	=	array();
	list($search)	=	mysql_fetch_row(mysql_query("SELECT `latest_searches` FROM `tools` WHERE `id` = '$CID' LIMIT 0, 1"));
	
	if(trim($search) != "")		$result	=	get_array_from_text($search);
	if(count($result) > 0)	return $result;
	else	return false;
}






function set_html_cache($tool, $domain){
	global $SOURCE, $ROOT_URL, $_current_dir, $CACHE_SECURITY_STR, $CACHE_OUTPUT_PATH, $CID;
	
	if($SOURCE == "" || $SOURCE == "DIRECT")
		$ajax_url	=	$ROOT_URL . $_current_dir . "/__ajax.php?" . http_build_query($_REQUEST) . "&cache_mode=true";
	elseif($SOURCE == "EMBED" || $SOURCE == "IMAGE")
		$ajax_url	=	$ROOT_URL . $_current_dir . "/__ajax.php?" . http_build_query($_REQUEST) . "&cache_mode=true&CID=$CID&SOURCE=DIRECT";
		
	
	// Creating Cache Data
	$ch	=	curl_init();
	curl_setopt($ch, CURLOPT_URL, $ajax_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ajax_html	=	curl_exec($ch);
	curl_close($ch);


	if($ajax_html == "" || !stristr($ajax_html, $CACHE_SECURITY_STR)){
		
		if($SOURCE != "IMAGE")	// Show any error, but not on image
			echo $ajax_html;
			
		include("../__wash.php");
		exit();	
	}else{
		// Removing Security Str
		$ajax_html	=	str_replace($CACHE_SECURITY_STR, "", $ajax_html);
	
		// Writing Output in File
		$cache_file_name	=	$CACHE_OUTPUT_PATH . strtolower($tool) . "\\__" . $domain . ".html";
		$fh			=	fopen($cache_file_name, 'w');
		fwrite($fh, $ajax_html);
		fclose($fh);
		
	}
	
}



function get_cache_days($tool){
	list($d)	=	mysql_fetch_row(mysql_query("SELECT `cache_in_days` from `tools` WHERE `id` = '$tool' LIMIT 0, 1"));
	return $d;			
}
function get_url_name($tool){
	list($d)	=	mysql_fetch_row(mysql_query("SELECT `urlname` from `tools` WHERE `id` = '$tool' LIMIT 0, 1"));
	return $d;			
}




function get_explore_cache($tool, $domain){
	
	global $ROOT_URL, $EXPLORE_CACHE_PATH;

	$html_file_name	=	$EXPLORE_CACHE_PATH . strtolower($domain) . "\\" . strtolower($tool) . ".html";

	// Checking if cache exists
	if(file_exists($html_file_name)) {
		echo "true";
	}else{
		set_explore_cache($tool, $domain);
	}
}


function set_explore_cache($tool, $domain){
	global $ROOT_URL, $EXPLORE_CACHE_PATH, $CACHE_SECURITY_STR;

	$ajax_url	=	$ROOT_URL . "__ajax.php?TID=" . $tool . "&domain=" . $domain . "&cache_mode=true";
	
	// Creating Cache Data
	$ch	=	curl_init();
	curl_setopt($ch, CURLOPT_URL, $ajax_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ajax_html	=	curl_exec($ch);
	curl_close($ch);
	
	if($ajax_html == "" || !stristr($ajax_html, $CACHE_SECURITY_STR)){

		$cache_file_name	=	$EXPLORE_CACHE_PATH . strtolower($domain) . "\\" . strtolower($tool) . ".html";
		$fh			=	fopen($cache_file_name, 'w');
		fwrite($fh, "false");
		fclose($fh);

		echo "false";
		
	}else{
		// Removing Security Str
		$ajax_html	=	str_replace($CACHE_SECURITY_STR, "", $ajax_html);

		// Writing Output in File
		$cache_file_name	=	$EXPLORE_CACHE_PATH . strtolower($domain) . "\\" . strtolower($tool) . ".html";
		
		$fh			=	fopen($cache_file_name, 'w');
		fwrite($fh, $ajax_html);
		fclose($fh);
		
		echo "true";
	}
	
}
// This function will get record from AWIS Amazon
/*
function update_awis($domain){
	
	include("__awis.php");


	// Validating Domain
	$domain	=	trim(strip_tags($domain));
	if($domain == "" || $domain == "http://")	return false;
	if(substr($domain, 0, 4) != "http://")	$domain	=	"http://" . $domain;
	
	$domain	=	trim(htmlentities(get_domain_name($domain)));
	if(substr($domain, 0, 4) == "www.")
		$domain	=	substr_replace($domain, "", 0, 4);


	if($domain	==	"")	return false;
	

	$awis_url	=	awis_generate_url($domain);
	$result		=	awis_make_http_request($awis_url);

	$current_tag = "";
	
	$xml_parser  =  xml_parser_create("");
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
	xml_set_element_handler($xml_parser, "awis_start_tag", "awis_end_tag");
	xml_set_character_data_handler($xml_parser, "awis_contents");
	xml_parse($xml_parser, $result, true);
	xml_parser_free($xml_parser);
	
	print_r($result);
	exit();


	$data_table_name	=	get_data_table_name($domain);
	$set_query			=	"SET `$data_name` = '$data', `last_update` = '$t'";

	// Checking if record already exists
	$t	=	mktime();
	list($yes)	=	mysql_fetch_row(mysql_query("SELECT count(`domain`) FROM `$data_table_name` WHERE `domain` = '$domain'"));	
	if($yes)
		mysql_query("UPDATE `$data_table_name` $set_query WHERE `domain` = '$domain'");
	else
		mysql_query("INSERT INTO `$data_table_name` $set_query, `domain` = '$domain'");

		
	echo ("Results:\n");
	echo ("Phone Number: ".$results['phonenumber']."\n");
	echo ("Owner Name: ".$results['ownername']."\n");
	echo ("Address: ".$results['street']."; ".$results['city'].",".$results['state']." ".$results['postalcode']." ".$results['country']."\n");
	echo ("Other sites that link to this site: ".$results['linksincount']."\n");
	echo ("Rank: ".$results['rank']."\n");

}
*/

function simple_search($action){
	echo '<form name="formSearch" method="get" action="'.$action.'">
	<p align="center">
		<input type="hidden" name="type" value="search">
	      <input name="q" type="text" id="q" size="40" value="'._REQUEST("q", "").'">
          <input type="submit" name="Submit" value="Search">
	<br>
	</p>
	</form>';
}
function _qs_remove($key, $url = false){

	if(!$url)	$url	=	$_SERVER['REQUEST_URI'];
	
	$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
	$url = substr($url, 0, -1);
	return ($url);
}
function make_url_name($str){
	$result	=	"";
	
	$str	=	strtolower(stripslashes($str));
	$str	=	preg_replace("/ /i", "_", $str);
	
	for($n = 0; $n < strlen($str); $n++){
		if(eregi("^[a-z0-9_-]+$", $str[$n]))
			$result	.=	$str[$n];
	}
	
	// Removing multiple ____
	$result	=	preg_replace("/_____/i", "_", $result);
	$result	=	preg_replace("/____/i", "_", $result);
	$result	=	preg_replace("/___/i", "_", $result);
	$result	=	preg_replace("/__/i", "_", $result);
	return $result;
}

function checkEmail($email){
	if(eregi ("^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,6}$", stripslashes(trim($email))))	{
		return true;
	}else{
		return false;
	}
}





function StrToNum($Str, $Check, $Magic)
{
    $Int32Unit = 4294967296;  // 2^32
 
    $length = strlen($Str);
    for ($i = 0; $i < $length; $i++) {
        $Check *= $Magic;
        //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
        //  the result of converting to integer is undefined
        //  refer to http://www.php.net/manual/en/language.types.integer.php
        if ($Check >= $Int32Unit) {
            $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
            //if the check less than -2^31
            $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
        }
        $Check += ord($Str{$i});
    }
    return $Check;
}
 
//--> for google pagerank
/*
* Genearate a hash for a url
*/
function HashURL($String)
{
    $Check1 = StrToNum($String, 0x1505, 0x21);
    $Check2 = StrToNum($String, 0, 0x1003F);
 
    $Check1 >>= 2;
    $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
    $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
    $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);
 
    $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
    $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );
 
    return ($T1 | $T2);
}
   //--> for google pagerank
/*
* genearate a checksum for the hash string
*/
function CheckHash($Hashnum)
{
    $CheckByte = 0;
    $Flag = 0;
 
    $HashStr = sprintf('%u', $Hashnum) ;
    $length = strlen($HashStr);
 
    for ($i = $length - 1;  $i >= 0;  $i --) {
        $Re = $HashStr{$i};
        if (1 === ($Flag % 2)) {
            $Re += $Re;
            $Re = (int)($Re / 10) + ($Re % 10);
        }
        $CheckByte += $Re;
        $Flag ++;
    }
 
    $CheckByte %= 10;
    if (0 !== $CheckByte) {
        $CheckByte = 10 - $CheckByte;
        if (1 === ($Flag % 2) ) {
            if (1 === ($CheckByte % 2)) {
                $CheckByte += 9;
            }
            $CheckByte >>= 1;
        }
    }
 
    return '7'.$CheckByte.$HashStr;
}
        //get google pagerank
function getpagerank($url) {
    $query="http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=".CheckHash(HashURL($url)). "&features=Rank&q=info:".$url."&num=100&filter=0";
    $data=file_get_contents_curl_ex($query);
    //print_r($data);
    $pos = strpos($data, "Rank_");
    if($pos === false){} else{
        $pagerank = substr($data, $pos + 9);
        return $pagerank;
    }
}
function file_get_contents_curl_ex($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);
 
    return $data;
}


class whois
{
    const timeout = 50;
    const whoishost = 'whois.internic.net';
    
    public static function lookup($domain){

       $result = "";
       $errno = 0;
       $errstr='';
    
       $fd = fsockopen(whois::whoishost,43, $errno, $errstr, whois::timeout);

       if ($fd){
             fputs($fd, $domain."\015\012");
           while (!feof($fd))    {
            $result .= fgets($fd,128) . "<br />";
           }
           fclose($fd);
        }
         return $result;
     }
}


function alexaRank($domain){
    $remote_url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url='.trim($domain);
    $search_for = '<POPULARITY URL';
    if ($handle = @fopen($remote_url, "r")) {
        while (!feof($handle)) {
            $part .= fread($handle, 100);
            $pos = strpos($part, $search_for);
            if ($pos === false)
            continue;
            else
            break;
        }
        $part .= fread($handle, 100);
        fclose($handle);
    }
    $str = explode($search_for, $part);
    $str = array_shift(explode('"/>', $str[1]));
    $str = explode('TEXT="', $str);
	$str[1] = str_replace('" SOURCE="panel',"",$str[1]);
    return $str[1];
}


function robots_allowed($url, $useragent=false)
  {
    # parse url to retrieve host and path
    $parsed = parse_url($url);

    $agents = array(preg_quote('*'));
    if($useragent) $agents[] = preg_quote($useragent);
    $agents = implode('|', $agents);

    # location of robots.txt file
    $robotstxt = @file("http://{$parsed['host']}/robots.txt&quot");
    if(!$robotstxt) return true;

    $rules = array();
    $ruleapplies = false;
    foreach($robotstxt as $line) {
      # skip blank lines
      if(!$line = trim($line)) continue;

      # following rules only apply if User-agent matches $useragent or '*'
      if(preg_match('/User-agent: (.*)/i', $line, $match)) {
        $ruleapplies = preg_match("/($agents)/i", $match[1]);
      }
      if($ruleapplies && preg_match('/Disallow:(.*)/i', $line, $regs)) {
        # an empty rule implies full access - no further tests required
        if(!$regs[1]) return true;
        # add rules that apply to array for testing
        $rules[] = preg_quote(trim($regs[1]), '/');
      }
    }

    foreach($rules as $rule) {
      # check if page is disallowed to us
      if(preg_match("/^$rule/", $parsed['path'])) return false;
    }

    # page is not disallowed
    return true;
  }
  
  function get_page_errors($url){
  	
		$w3	=	fetch("http://validator.w3.org/check?uri=" . $url);
		preg_match("/<td colspan=\"2\" class=\"invalid\">\n(.*?)Errors/",$w3,$w3);
		return trim($w3[1]);
  	
  }
function dateDiff($dformat, $endDate, $beginDate)
{
$date_parts1=explode($dformat, $beginDate);
$date_parts2=explode($dformat, $endDate);
$start_date=gregoriantojd($date_parts1[0], $date_parts1[1], $date_parts1[2]);
$end_date=gregoriantojd($date_parts2[0], $date_parts2[1], $date_parts2[2]);
return $end_date - $start_date;
}

function time_elapsed_string($ptime,$val) {
    if($val == "age"){
	$etime = time() - $ptime;
    }else{
    $etime = $ptime - time();		
    }
    if ($etime < 1) {
        return '0 seconds';
    }
    
    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
                );
    
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '');
        }
    }
}

function get_whois($domain){
	
	
	$whois_content = file_get_contents("http://reports.internic.net/cgi/whois?whois_nic=$domain&type=domain");
	
	return $whois_content;
	
}

function getUrlData($url)
{
	$result = false;
	$contents = getUrlContents($url);

	if (isset($contents) && is_string($contents))
	{
		$title = null;
		$metaTags = null;

		preg_match('/<title>([^>]*)<\/title>/si', $contents, $match );

		if (isset($match) && is_array($match) && count($match) > 0)
		{
			$title = strip_tags($match[1]);
		}

		preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' .'[lang="]*[^>"]*["]*'.'[\s]*content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
		if (isset($match) && is_array($match) && count($match) == 3)
		{
			$originals = $match[0];
			$names = $match[1];
			$values = $match[2];

			if (count($originals) == count($names) && count($names) == count($values))
			{
				$metaTags = array();

				for ($i=0, $limiti=count($names); $i < $limiti; $i++)
				{
					$metaname=trim(strtolower($names[$i]));
					$metaname=str_replace("'",'',$metaname);
					$metaname=str_replace("/",'',$metaname);
					$metaTags[$metaname] = array (
					'html' => htmlentities($originals[$i]),
					'value' => $values[$i]
					);
				}
			}
		}
		if(sizeof($metaTags)==0) {
			preg_match_all('/<[\s]*meta[\s]*content="?' . '([^>"]*)"?[\s]*' .'[lang="]*[^>"]*["]*'.'[\s]*name="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);

			if (isset($match) && is_array($match) && count($match) == 3)
			{
				$originals = $match[0];
				$names = $match[2];
				$values = $match[1];

				if (count($originals) == count($names) && count($names) == count($values))
				{
					$metaTags = array();

					for ($i=0, $limiti=count($names); $i < $limiti; $i++)
					{
						$metaname=trim(strtolower($names[$i]));
						$metaname=str_replace("'",'',$metaname);
						$metaname=str_replace("/",'',$metaname);
						$metaTags[$metaname] = array (
							'html' => htmlentities($originals[$i]),
							'value' => $values[$i]
						);
					}
				}
			}
		}

		$result = array (
			'title' => $title,
			'metaTags' => $metaTags
		);
	}

	return $result;
}

function getUrlContents($url, $maximumRedirections = null, $currentRedirection = 0)
{
	$result = false;
	$contents = $url;

	if (isset($contents) && is_string($contents))
	{
		preg_match_all('/<[\s]*meta[\s]*http-equiv="?REFRESH"?' . '[\s]*content="?[0-9]*;[\s]*URL[\s]*=[\s]*([^>"]*)"?' . '[\s]*[\/]?[\s]*>/si', $contents, $match);

		if (isset($match) && is_array($match) && count($match) == 2 && count($match[1]) == 1)
		{
			if (!isset($maximumRedirections) || $currentRedirection < $maximumRedirections)
			{
				return getUrlContents($match[1][0], $maximumRedirections, ++$currentRedirection);
			}

			$result = false;
		}
		else
		{
			$result = $contents;
		}
	}

	return $contents;
}

function get_web_page( $url )
	{
		
		$proxies_array	=	array(
								'173.208.13.98:17338',
								'173.208.14.100:17338',
								'173.208.24.58:17338',
								'173.208.67.92:17338',
								'173.234.27.103:17338',
								'173.234.28.164:17338',
								'173.234.29.155:17338',
								'173.234.30.138:17338',
								'173.234.31.129:17338',
								'173.234.54.253:17338'
							);
							
		$random_key 		= array_rand($proxies_array);
		$random_proxy 		= $proxies_array[$random_key];					
							
		$useragents_array	=	array(
								'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.11) Gecko Kazehakase/0.5.4 Debian/0.5.4-2.1ubuntu3',
								'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.13) Gecko/20080311 (Debian-1.8.1.13+nobinonly-0ubuntu1) Kazehakase/0.5.2',
								'Mozilla/5.0 (X11; Linux x86_64; U;) Gecko/20060207 Kazehakase/0.3.5 Debian/0.3.5-1',
								'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; KKman2.0)',
								'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8.1.4) Gecko/20070511 K-Meleon/1.1',
								'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9) Gecko/2008052906 K-MeleonCCFME 0.09',
								'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8.0.7) Gecko/20060917 K-Meleon/1.02',
								'Mozilla/5.0 (Windows; U; Win 9x 4.90; en-US; rv:1.7.5) Gecko/20041220 K-Meleon/0.9',
								'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.5) Gecko/20031016 K-Meleon/0.8.2',
								'Mozilla/5.0 (Windows; U; Win98; en-US; rv:1.5) Gecko/20031016 K-Meleon/0.8.2',
								'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.5) Gecko/20031016 K-Meleon/0.8',
								'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.2b) Gecko/20021016 K-Meleon 0.7',
								'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:0.9.5) Gecko/20011011',
								'Mozilla/5.0(Windows;N;Win98;m18)Gecko/20010124',
								'Mozilla/5.0 (compatible; Konqueror/4.0; Linux) KHTML/4.0.5 (like Gecko)',
								'Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)',
								'Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)',
								'Mozilla/5.0 (compatible; Konqueror/3.5; GNU/kFreeBSD) KHTML/3.5.9 (like Gecko) (Debian)',
								'Mozilla/5.0 (compatible; Konqueror/3.5; Darwin) KHTML/3.5.6 (like Gecko)'
							);
							
		$random_key 		= array_rand($useragents_array);
		$random_useragent 	= $useragents_array[$random_key];							

													
		$options = array(
			CURLOPT_RETURNTRANSFER 	=> true,     			// return web page
			CURLOPT_HEADER         	=> false,    			// don't return headers
			//CURLOPT_PROXY 			=> $random_proxy,     		// the HTTP proxy to tunnel request through
			//CURLOPT_HTTPPROXYTUNNEL => 1,    				// tunnel through a given HTTP proxy			
			CURLOPT_FOLLOWLOCATION 	=> true,     			// follow redirects
			CURLOPT_ENCODING       	=> "",       			// handle compressed
			CURLOPT_USERAGENT      	=> $random_useragent, 	// who am i
			CURLOPT_AUTOREFERER    	=> true,     			// set referer on redirect
			CURLOPT_CONNECTTIMEOUT 	=> 120,      			// timeout on connect
			CURLOPT_TIMEOUT        	=> 20,      			// timeout on response
			CURLOPT_MAXREDIRS      	=> 10,       			// stop after 10 redirects
		);
	
		$ch      = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$content = curl_exec( $ch );
		curl_close( $ch );
		
		return $content;
}

function calculate_cost($se_rank,$page_rank,$fixed_price){
	
	if ($fixed_price != "") {
		
		if($page_rank == "0") $page_rank = 1;
		
		if($se_rank < 1 || $se_rank > 50) $se_rank = 50;

		return ceil(($se_rank/$page_rank)*$fixed_price);	
		
		
		
	}else{
		
		return 0;
		
	}	
	
}

function keyword_suggestions($keyword){
	
	$keyword = urlencode($keyword);
	$xml 	= @simplexml_load_file("http://search.yahooapis.com/WebSearchService/V1/relatedSuggestion?query=$keyword&;output=xml&results=20&appid=bIpqc43V34G.6UQFQGZVHGYdwJEKr10H9DcBvfFbrB1au6uVZA1jVHkV9wJubMSTjZG9Ma4wpoA-");	

	$keywords = array();	
	foreach ($xml->Result as $val){
		
		$keywords[] = $val;
		
	}
	
	return $keywords;
	
	
}

function format_bytes($size) {
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}

function compress_html($html){     
    return preg_replace(array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s'), array('>','<','\\1'), $html);
}

function truncate_string ($string, $maxlength, $extension) {
   $cutmarker = "**cut_here**";
   if (strlen($string) > $maxlength) {
       $string = wordwrap($string, $maxlength, $cutmarker);
       $string = explode($cutmarker, $string);
       $string = $string[0] . $extension;
   }

   return $string;
}

function get_dmoz($domain){
	

		$page = get_web_page("http://search.dmoz.org/search/search?q=$domain");
		preg_match_all('/<div class="ref">(.*?)&nbsp;/is',$page,$matches);
		if ($matches[0]) {
			
			foreach($matches[0] as $match){
			
			preg_match("#$domain#is",$match,$found);				
			if($found[0]){
				
				return "Yes";
				
			}else{
				return "No";
			}
			
		}
		}else{
			return "No";
		}
	
}


function get_yahoodir($domain){
	

		$page = get_web_page("http://dir.search.yahoo.com/search?h=c&p=$domain&ei=utf-8&fr=ush-dir");
		preg_match_all('/<em class="article_hosturl">(.*?)<\/em>/is',$page,$matches);
		if ($matches[0]) {
			
			foreach($matches[0] as $match){
			$match = strip_tags($match);
			preg_match("#$domain#is",$match,$found);				
			if($found[0]){
				
				return "Yes";
				
			}else{
				return "No";
			}
			
		}
		}else{
			return "No";
		}
	
}

function tweet_info($url){
	
	
	$tweetmeme = unserialize(file_get_contents("http://api.tweetmeme.com/url_info.php?url=$url"));
	
	return $tweetmeme;
	
}

function StopCounter()
{
      global $StartTime;
      $time = microtime();
	  $time = explode(' ', $time);
	  $time = $time[1] + $time[0];
      $Endtime = $time;
      $Total = round($Endtime - $StartTime,4);
      return $Total;
}

$whoisservers = array(
	"ac" =>"whois.nic.ac",
	"ae" =>"whois.nic.ae",
	"aero"=>"whois.aero",
	"af" =>"whois.nic.af",
	"ag" =>"whois.nic.ag",
	"al" =>"whois.ripe.net",
	"am" =>"whois.amnic.net",
	"arpa" =>"whois.iana.org",
	"as" =>"whois.nic.as",
	"asia" =>"whois.nic.asia",
	"at" =>"whois.nic.at",
	"au" =>"whois.aunic.net",
	"az" =>"whois.ripe.net",
	"ba" =>"whois.ripe.net",
	"be" =>"whois.dns.be",
	"bg" =>"whois.register.bg",
	"bi" =>"whois.nic.bi",
	"biz" =>"whois.biz",
	"bj" =>"whois.nic.bj",
	"br" =>"whois.registro.br",
	"bt" =>"whois.netnames.net",
	"by" =>"whois.ripe.net",
	"bz" =>"whois.belizenic.bz",
	"ca" =>"whois.cira.ca",
	"cat" =>"whois.cat",
	"cc" =>"whois.nic.cc",
	"cd" =>"whois.nic.cd",
	"ch" =>"whois.nic.ch",
	"ci" =>"whois.nic.ci",
	"ck" =>"whois.nic.ck",
	"cl" =>"whois.nic.cl",
	"cn" =>"whois.cnnic.net.cn",
	"com" =>"whois.verisign-grs.com",
	"coop" =>"whois.nic.coop",
	"cx" =>"whois.nic.cx",
	"cy" =>"whois.ripe.net",
	"cz" =>"whois.nic.cz",
	"de" =>"whois.denic.de",
	"dk" =>"whois.dk-hostmaster.dk",
	"dm" =>"whois.nic.cx",
	"dz" =>"whois.ripe.net",
	"edu" =>"whois.educause.edu",
	"ee" =>"whois.eenet.ee",
	"eg" =>"whois.ripe.net",
	"es" =>"whois.ripe.net",
	"eu" =>"whois.eu",
	"fi" =>"whois.ficora.fi",
	"fo" =>"whois.ripe.net",
	"fr" =>"whois.nic.fr",
	"gb" =>"whois.ripe.net",
	"gd" =>"whois.adamsnames.com",
	"ge" =>"whois.ripe.net",
	"gg" =>"whois.channelisles.net",
	"gi" =>"whois2.afilias-grs.net",
	"gl" =>"whois.ripe.net",
	"gm" =>"whois.ripe.net",
	"gov" =>"whois.nic.gov",
	"gr" =>"whois.ripe.net",
	"gs" =>"whois.nic.gs",
	"gw" =>"whois.nic.gw",
	"gy" =>"whois.registry.gy",
	"hk" =>"whois.hkirc.hk",
	"hm" =>"whois.registry.hm",
	"hn" =>"whois2.afilias-grs.net",
	"hr" =>"whois.ripe.net",
	"hu" =>"whois.nic.hu",
	"ie" =>"whois.domainregistry.ie",
	"il" =>"whois.isoc.org.il",
	"in" =>"whois.inregistry.net",
	"info" =>"whois.afilias.net",
	"int" =>"whois.iana.org",
	"io" =>"whois.nic.io",
	"iq" =>"vrx.net",
	"ir" =>"whois.nic.ir",
	"is" =>"whois.isnic.is",
	"it" =>"whois.nic.it",
	"je" =>"whois.channelisles.net",
	"jobs" =>"jobswhois.verisign-grs.com",
	"jp" =>"whois.jprs.jp",
	"ke" =>"whois.kenic.or.ke",
	"kg" =>"www.domain.kg",
	"ki" =>"whois.nic.ki",
	"kr" =>"whois.nic.or.kr",
	"kz" =>"whois.nic.kz",
	"la" =>"whois.nic.la",
	"li" =>"whois.nic.li",
	"lt" =>"whois.domreg.lt",
	"lu" =>"whois.dns.lu",
	"lv" =>"whois.nic.lv",
	"ly" =>"whois.nic.ly",
	"ma" =>"whois.iam.net.ma",
	"mc" =>"whois.ripe.net",
	"md" =>"whois.ripe.net",
	"me" =>"whois.meregistry.net",
	"mg" =>"whois.nic.mg",
	"mil" =>"whois.nic.mil",
	"mn" =>"whois.nic.mn",
	"mobi" =>"whois.dotmobiregistry.net",
	"ms" =>"whois.adamsnames.tc",
	"mt" =>"whois.ripe.net",
	"mu" =>"whois.nic.mu",
	"museum" =>"whois.museum",
	"mx" =>"whois.nic.mx",
	"my" =>"whois.mynic.net.my",
	"na" =>"whois.na-nic.com.na",
	"name" =>"whois.nic.name",
	"net" =>"whois.verisign-grs.net",
	"nf" =>"whois.nic.nf",
	"nl" =>"whois.domain-registry.nl",
	"no" =>"whois.norid.no",
	"nu" =>"whois.nic.nu",
	"nz" =>"whois.srs.net.nz",
	"org" =>"whois.pir.org",
	"pl" =>"whois.dns.pl",
	"pm" =>"whois.nic.pm",
	"pr" =>"whois.uprr.pr",
	"pro" =>"whois.registrypro.pro",
	"pt" =>"whois.dns.pt",
	"re" =>"whois.nic.re",
	"ro" =>"whois.rotld.ro",
	"ru" =>"whois.ripn.net",
	"sa" =>"whois.nic.net.sa",
	"sb" =>"whois.nic.net.sb",
	"sc" =>"whois2.afilias-grs.net",
	"se" =>"whois.iis.se",
	"sg" =>"whois.nic.net.sg",
	"sh" =>"whois.nic.sh",
	"si" =>"whois.arnes.si",
	"sk" =>"whois.ripe.net",
	"sm" =>"whois.ripe.net",
	"st" =>"whois.nic.st",
	"su" =>"whois.ripn.net",
	"tc" =>"whois.adamsnames.tc",
	"tel" =>"whois.nic.tel",
	"tf" =>"whois.nic.tf",
	"th" =>"whois.thnic.net",
	"tj" =>"whois.nic.tj",
	"tk" =>"whois.dot.tk",
	"tl" =>"whois.nic.tl",
	"tm" =>"whois.nic.tm",
	"tn" =>"whois.ripe.net",
	"to" =>"whois.tonic.to",
	"tp" =>"whois.nic.tl",
	"tr" =>"whois.nic.tr",
	"travel" =>"whois.nic.travel",
	"tv" => "tvwhois.verisign-grs.com",
	"tw" =>"whois.twnic.net.tw",
	"ua" =>"whois.net.ua",
	"ug" =>"whois.co.ug",
	"uk" =>"whois.nic.uk",
	"us" =>"whois.nic.us",
	"uy" =>"nic.uy",
	"uz" =>"whois.cctld.uz",
	"va" =>"whois.ripe.net",
	"vc" =>"whois2.afilias-grs.net",
	"ve" =>"whois.nic.ve",
	"vg" =>"whois.adamsnames.tc",
	"wf" =>"whois.nic.wf",
	"ws" =>"whois.website.ws",
	"yt" =>"whois.nic.yt",
	"yu" =>"whois.ripe.net");

function LookupDomain($domain){
	global $whoisservers;
	$domain_parts = explode(".", $domain);
	$tld = strtolower(array_pop($domain_parts));
	$whoisserver = $whoisservers[$tld];
	if(!$whoisserver) {
		return "Error: No appropriate Whois server found for $domain domain!";
	}
	$result = QueryWhoisServer($whoisserver, $domain);
	if(!$result) {
		return "Error: No results retrieved from $whoisserver server for $domain domain!";
	}
	else {
		while(strpos($result, "Whois Server:") !== FALSE){
			preg_match("/Whois Server: (.*)/", $result, $matches);
			$secondary = $matches[1];
			if($secondary) {
				$result = QueryWhoisServer($secondary, $domain);
				$whoisserver = $secondary;
			}
		}
	}
	return "$domain domain lookup results from $whoisserver server:\n\n" . $result;
}

function LookupIP($ip) {
	$whoisservers = array(
		//"whois.afrinic.net", // Africa - returns timeout error :-(
		"whois.lacnic.net", // Latin America and Caribbean - returns data for ALL locations worldwide :-)
		"whois.apnic.net", // Asia/Pacific only
		"whois.arin.net", // North America only
		"whois.ripe.net" // Europe, Middle East and Central Asia only
	);
	$results = array();
	foreach($whoisservers as $whoisserver) {
		$result = QueryWhoisServer($whoisserver, $ip);
		if($result && !in_array($result, $results)) {
			$results[$whoisserver]= $result;
		}
	}
	$res = "RESULTS FOUND: " . count($results);
	foreach($results as $whoisserver=>$result) {
		$res .= "\n\n-------------\nLookup results for $ip from $whoisserver server:\n\n$result";
	}
	return $res;
}

function ValidateIP($ip) {
	$ipnums = explode(".", $ip);
	if(count($ipnums) != 4) {
		return false;
	}
	foreach($ipnums as $ipnum) {
		if(!is_numeric($ipnum) || ($ipnum > 255)) {
			return false;
		}
	}
	return $ip;
}

function ValidateDomain($domain) {
	if(!preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $domain)) {
		return false;
	}
	return $domain;
}

function QueryWhoisServer($whoisserver, $domain) {
	$port = 43;
	$timeout = 10;
	$fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout) or die("Socket Error " . $errno . " - " . $errstr);
	if($whoisserver == "whois.verisign-grs.com") $domain = "=".$domain; // whois.verisign-grs.com requires the equals sign ("=") or it returns any result containing the searched string.
	fputs($fp, $domain . "\r\n");
	$out = "";
	while(!feof($fp)){
		$out .= fgets($fp);
	}
	fclose($fp);

	$res = "";
	if((strpos(strtolower($out), "error") === FALSE) && (strpos(strtolower($out), "not allocated") === FALSE)) {
		$rows = explode("\n", $out);
		foreach($rows as $row) {
			$row = trim($row);
			if(($row != '') && ($row{0} != '#') && ($row{0} != '%')) {
				$res .= $row."\n";
			}
		}
	}
	return $res;
}

function get_web_page_g( $url )
	{
		
		$proxies_array	=	array(
								'173.208.13.98:17338',
								'173.208.14.100:17338',
								'173.208.24.58:17338',
								'173.208.67.92:17338',
								'173.234.27.103:17338',
								'173.234.28.164:17338',
								'173.234.29.155:17338',
								'173.234.30.138:17338',
								'173.234.31.129:17338',
								'173.234.54.253:17338'
							);
							
		$random_key 		= array_rand($proxies_array);
		$random_proxy 		= $proxies_array[$random_key];					
							
		$useragents_array	=	array(
								'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.11) Gecko Kazehakase/0.5.4 Debian/0.5.4-2.1ubuntu3',
								'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.13) Gecko/20080311 (Debian-1.8.1.13+nobinonly-0ubuntu1) Kazehakase/0.5.2',
								'Mozilla/5.0 (X11; Linux x86_64; U;) Gecko/20060207 Kazehakase/0.3.5 Debian/0.3.5-1',
								'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; KKman2.0)',
								'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8.1.4) Gecko/20070511 K-Meleon/1.1',
								'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9) Gecko/2008052906 K-MeleonCCFME 0.09',
								'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.8.0.7) Gecko/20060917 K-Meleon/1.02',
								'Mozilla/5.0 (Windows; U; Win 9x 4.90; en-US; rv:1.7.5) Gecko/20041220 K-Meleon/0.9',
								'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.5) Gecko/20031016 K-Meleon/0.8.2',
								'Mozilla/5.0 (Windows; U; Win98; en-US; rv:1.5) Gecko/20031016 K-Meleon/0.8.2',
								'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.5) Gecko/20031016 K-Meleon/0.8',
								'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.2b) Gecko/20021016 K-Meleon 0.7',
								'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:0.9.5) Gecko/20011011',
								'Mozilla/5.0(Windows;N;Win98;m18)Gecko/20010124',
								'Mozilla/5.0 (compatible; Konqueror/4.0; Linux) KHTML/4.0.5 (like Gecko)',
								'Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)',
								'Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)',
								'Mozilla/5.0 (compatible; Konqueror/3.5; GNU/kFreeBSD) KHTML/3.5.9 (like Gecko) (Debian)',
								'Mozilla/5.0 (compatible; Konqueror/3.5; Darwin) KHTML/3.5.6 (like Gecko)'
							);
							
		$random_key 		= array_rand($useragents_array);
		$random_useragent 	= $useragents_array[$random_key];							

													
		$options = array(
			CURLOPT_RETURNTRANSFER 	=> true,     			// return web page
			CURLOPT_HEADER         	=> false,    			// don't return headers
			//CURLOPT_PROXY 			=> $random_proxy,     		// the HTTP proxy to tunnel request through
			//CURLOPT_HTTPPROXYTUNNEL => 1,    				// tunnel through a given HTTP proxy			
			CURLOPT_FOLLOWLOCATION 	=> true,     			// follow redirects
			CURLOPT_ENCODING       	=> "",       			// handle compressed
			CURLOPT_USERAGENT      	=> "Googlebot/2.1 (+http://www.google.com/bot.html)", 	// who am i
			CURLOPT_AUTOREFERER    	=> true,     			// set referer on redirect
			CURLOPT_CONNECTTIMEOUT 	=> 120,      			// timeout on connect
			CURLOPT_TIMEOUT        	=> 20,      			// timeout on response
			CURLOPT_MAXREDIRS      	=> 10,       			// stop after 10 redirects
		);
	
		$ch      = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$content = curl_exec( $ch );
		curl_close( $ch );
		
		return $content;
	}
	
function grades($level){
	
	if($level == "") $level = 0;
	
	if($level < 6){
		return "Elementary school";
	}elseif($level < 9){
		
		return "Middle school";
	}elseif($level < 13){
		
		return "High school";
	}else{
		
		return "Post-secondary education";
	}
	
	
}


function get_web_page_s( $url )
{
	$options = array(
		CURLOPT_RETURNTRANSFER => true,     // return web page
		CURLOPT_HEADER         => false,    // don't return headers
		CURLOPT_FOLLOWLOCATION => true,     // follow redirects
		CURLOPT_SSL_VERIFYPEER 	=> false,     // follow redirects
		CURLOPT_ENCODING       => "",       // handle compressed
		CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1", // who am i
		CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
		CURLOPT_TIMEOUT        => 120,      // timeout on response
		CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
	);

	$ch      = curl_init( $url );
	curl_setopt_array( $ch, $options );
	$content = curl_exec( $ch );
	curl_close( $ch );
	
	return $content;
}

function strBytes($str){
 // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
 
 // Number of characters in string
 $strlen_var = strlen($str);
 
 // string bytes counter
 $d = 0;
 
 /*
 * Iterate over every character in the string,
 * escaping with a slash or encoding to UTF-8 where necessary
 */
 for($c = 0; $c < $strlen_var; ++$c){
  $ord_var_c = ord($str{$c});
  switch(true){
  case(($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
   // characters U-00000000 - U-0000007F (same as ASCII)
   $d++;
   break;
  case(($ord_var_c & 0xE0) == 0xC0):
   // characters U-00000080 - U-000007FF, mask 110XXXXX
   $d+=2;
   break;
  case(($ord_var_c & 0xF0) == 0xE0):
   // characters U-00000800 - U-0000FFFF, mask 1110XXXX
   $d+=3;
   break;
  case(($ord_var_c & 0xF8) == 0xF0):
   // characters U-00010000 - U-001FFFFF, mask 11110XXX
   $d+=4;
   break;
  case(($ord_var_c & 0xFC) == 0xF8):
   // characters U-00200000 - U-03FFFFFF, mask 111110XX
   $d+=5;
   break;
  case(($ord_var_c & 0xFE) == 0xFC):
   // characters U-04000000 - U-7FFFFFFF, mask 1111110X
   $d+=6;
   break;
   default:
   $d++;
  };
 };
 return $d;
}

function extract_keywords($str, $minWordLen = 3, $minWordOccurrences = 2, $asArray = false)
{
function keyword_count_sort($first, $sec)
{
return $sec[1] - $first[1];
}
$stopwords = array("raquo","style","a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

$str = preg_replace('/[^\p{L}0-9 ]/', ' ', $str);
$str = trim(preg_replace('/\s+/', ' ', $str));
 
$words = explode(' ', $str);
$keywords = array();
while(($c_word = array_shift($words)) !== null)
{
if(strlen($c_word) < $minWordLen) continue;
 
$c_word = strtolower($c_word);
if(array_key_exists($c_word, $keywords)) $keywords[$c_word][1]++;
else $keywords[$c_word] = array($c_word, 1);
}
usort($keywords, 'keyword_count_sort');
 
$final_keywords = array();
foreach($keywords as $keyword_det)
{
if($keyword_det[1] < $minWordOccurrences) break;
if(!in_array($keyword_det[0],$stopwords)) array_push($final_keywords, $keyword_det[0]);
}

return $asArray ? $final_keywords : implode(', ', $final_keywords);
}

function occurance($words_count,$keywords_count){
	
	return ($keywords_count/$words_count)*100;
	
}

?>