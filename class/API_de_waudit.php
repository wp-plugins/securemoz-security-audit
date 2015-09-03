<?php

error_reporting(0);
set_time_limit(0);

include("__functions.php");

$domain				=	trim(_POST("url", "", true));

if($_POST){
	
	
if(substr($domain, 0, 7) == "http://")	$domain	=	substr_replace($domain, "", 0, 7);
			$url	=	"http://" . $domain;
	
			$domain	=	get_domain_name($url);
			$domain = str_replace("www.","",$domain);
			$url	=	"http://" . $domain;
			$en_url = urlencode($url);	
			

		$content = get_web_page($url);
		
		
		$malware_check = get_web_page_s("https://sb-ssl.google.com/safebrowsing/api/lookup?client=api&apikey=ABQIAAAA-TupMdiEURbDKQxShJgRTBSr3g6UydUrHrn7ItxvjeiZIKCU2A&appver=1.0&pver=3.0&url=$en_url");		
		
		if($malware_check == "malware"){
			
			$malware_status = "<b><i>Yes</i><b>";
			$malware_img = "error";
			
		}else{
			
			$malware_status = "No";
			$malware_img = "check";
		}
		
		
		if($malware_check == "malware"){
			
			$google_class = "ui-state-error widg-error ui-corner-all";
			$google_span = "ui-icon ui-icon-alert";
			$google_html = 'Domain blacklisted by Google Safe Browsing: '.$domain.' - <a target="_blank" href="http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site='.$domain.'">reference</a><br />';
			
			
		}else{
			
			$google_class = "ui-state-highlight  widg-info ui-corner-all";
			$google_span = "ui-icon ui-icon-info";
			$google_html = 'Domain clean by Google Safe Browsing: '.$domain.' - <a target="_blank" href="http://safebrowsing.clients.google.com/safebrowsing/diagnostic?site='.$domain.'">reference</a><br />';
		}		

		
		$yandex_content = get_web_page_g("http://www.yandex.com/infected?url=$domain&l10n=en");

		preg_match('#Visiting this site may harm your computer#is',$yandex_content,$yandex_check);
		if ($yandex_check[0]) $yandex = "1";
		else $yandex = "0";
				
		if($yandex == "1"){
			
			$yandex_class = "ui-state-error widg-error ui-corner-all";
			$yandex_span = "ui-icon ui-icon-alert";
			$yandex_html = 'Domain blacklisted by Yandex (via Sophos): '.$domain.' - <a target="_blank" href="http://www.yandex.com/infected?url='.$domain.'&amp;l10n=en">reference</a><br />';
			
		}else{
			
			$yandex_class = "ui-state-highlight  widg-info ui-corner-all";
			$yandex_span = "ui-icon ui-icon-info";
			$yandex_html = 'Domain clean by Yandex (via Sophos): '.$domain.' - <a target="_blank" href="http://www.yandex.com/infected?url='.$domain.'&amp;l10n=en">reference</a><br />';
		}		
		
		
		
		$mcafee_content = get_web_page_g("http://www.siteadvisor.com/sites/$domain");

		preg_match('#siteRed#is',$mcafee_content,$mcafee_check);
		if ($mcafee_check[0]) $mcafee = "1";
		else $mcafee = "0";
				
		if($mcafee == "1"){
			
			$mcafee_class = "ui-state-error widg-error ui-corner-all";
			$mcafee_span = "ui-icon ui-icon-alert";
			$mcafee_html = 'Domain blacklisted by SiteAdvisor (McAfee): '.domain.' - <a target="_blank" href="http://www.siteadvisor.com/sites/'.$domain.'">reference</a><br />';
			
		}else{
			
			$mcafee_class = "ui-state-highlight  widg-info ui-corner-all";
			$mcafee_span = "ui-icon ui-icon-info";
			$mcafee_html = 'Domain clean by SiteAdvisor (McAfee): '.domain.' - <a target="_blank" href="http://www.siteadvisor.com/sites/'.$domain.'">reference</a><br />';
		}		
		


		$norton_content = get_web_page_g("http://safeweb.norton.com/report/show?url=$domain");
		
		
		preg_match('#Total threats found: <strong>(.*?)</strong>#is',$norton_content,$norton);
		$norton = $norton[1];		
		if($norton == "") $norton = "0";
		
		if($norton > "0"){
			
			$norton_class = "ui-state-error widg-error ui-corner-all";
			$norton_span = "ui-icon ui-icon-alert";
			$norton_html = 'Domain blacklisted by Norton Safe Web: '.$domain.' - <a target="_blank" href="http://safeweb.norton.com/report/show?url='.$domain.'">reference</a><br />';
			
			
		}else{
			
			$norton_class = "ui-state-highlight  widg-info ui-corner-all";
			$norton_span = "ui-icon ui-icon-info";
			$norton_html = 'Domain clean by Norton Safe Web: '.$domain.' - <a target="_blank" href="http://safeweb.norton.com/report/show?url='.$domain.'">reference</a><br />';
		}		
		
		if($norton > "0" || $malware_check == "malware" || $mcafee == "1" || $yandex == "1"){
			
			$bl_status = "<b><i>Yes</i><b>";
			$bl_img = "error";
			
			$report_img = "warn2";
			$report_status = "Site blacklisted";
			$report_warning = "Warnings found";
			$report_class = "red";
			
		}else{
			
			$bl_status = "No";
			$bl_img = "check";
			
			
			$report_img = "green";
			$report_status = "Site clean";
			$report_warning = "No threats found";
			$report_class = "blue";			
			
		}		

		
		preg_match('#Viruses</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$v);
		$v = $v[2];
		if($v == "") $v = "0";
		
		if($v > "0"){
			
			$v_status = "<b><i>Yes</i><b>";
			$v_img = "error";
			
		}else{
			
			$v_status = "No";
			$v_img = "check";
		}		
		
		
		
		
		
		preg_match('#Drive-By Downloads</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$dbd);
		$dbd = $dbd[2];
		if($dbd == "") $dbd = "0";
		
		if($dbd > "0"){
			
			$dbd_status = "<b><i>Yes</i><b>";
			$dbd_img = "error";
			
		}else{
			
			$dbd_status = "No";
			$dbd_img = "check";
		}	
		
		
		
		preg_match('#Malicious Downloads</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$md);
		$md = $md[2];
		if($md == "") $md = "0";
		
		if($md > "0"){
			
			$md_status = "<b><i>Yes</i><b>";
			$md_img = "error";
			
		}else{
			
			$md_status = "No";
			$md_img = "check";
		}

		
		
		preg_match('#Worms</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$w);
		$w = $w[2];
		if($w == "") $w = "0";
		
		if($w > "0"){
			
			$w_status = "<b><i>Yes</i><b>";
			$w_img = "error";
			
		}else{
			
			$w_status = "No";
			$w_img = "check";
		}

		
		preg_match('#Suspicious Applications</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$sa);
		$sa = $sa[2];
		if($sa == "") $sa = "0";
		
		if($sa > "0"){
			
			$sa_status = "<b><i>Yes</i><b>";
			$sa_img = "error";
			
		}else{
			
			$sa_status = "No";
			$sa_img = "check";
		}

		
		
		preg_match('#Suspicious Browser Changes</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$sbc);
		$sbc = $sbc[2];
		if($sbc == "") $sbc = "0";
		
		if($sbc > "0"){
			
			$sbc_status = "<b><i>Yes</i><b>";
			$sbc_img = "error";
			
		}else{
			
			$sbc_status = "No";
			$sbc_img = "check";
		}

		
		preg_match('#Security Risks</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$sr);
		$sr = $sr[2];
		if($sr == "") $sr = "0";
		
		if($sr > "0"){
			
			$sr_status = "<b><i>Yes</i><b>";
			$sr_img = "error";
			
		}else{
			
			$sr_status = "No";
			$sr_img = "check";
		}

		
		preg_match('#Heuristic Viruses</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$hv);
		$hv = $hv[2];
		if($hv == "") $hv = "0";
		
		if($hv > "0"){
			
			$hv_status = "<b><i>Yes</i><b>";
			$hv_img = "error";
			
		}else{
			
			$hv_status = "No";
			$hv_img = "check";
		}

		
		preg_match('#Adware</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$a);
		$a = $a[2];
		if($a == "") $a = "0";
		
		if($a > "0"){
			
			$a_status = "<b><i>Yes</i><b>";
			$a_img = "error";
			
		}else{
			
			$a_status = "No";
			$a_img = "check";
		}

		
		
		preg_match('#Trojans</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$t);
		$t = $t[2];
		if($t == "") $t = "0";
		
		if($t > "0"){
			
			$t_status = "<b><i>Yes</i><b>";
			$t_img = "error";
			
		}else{
			
			$t_status = "No";
			$t_img = "check";
		}

		
		preg_match('#Phishing Attacks</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$pa);
		$pa = $pa[2];
		if($pa == "") $pa = "0";
		
		if($pa > "0"){
			
			$pa_status = "<b><i>Yes</i><b>";
			$pa_img = "error";
			
		}else{
			
			$pa_status = "No";
			$pa_img = "check";
		}

		
		preg_match('#Spyware</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$s);
		$s = $s[2];
		if($s == "") $s = "0";
		
		if($s > "0"){
			
			$s_status = "<b><i>Yes</i><b>";
			$s_img = "error";
			
		}else{
			
			$s_status = "No";
			$s_img = "check";
		}

		
		preg_match('#Backdoors</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$b);
		$b = $b[2];
		if($b == "") $b = "0";
		
		if($b > "0"){
			
			$b_status = "<b><i>Yes</i><b>";
			$b_img = "error";
			
		}else{
			
			$b_status = "No";
			$b_img = "check";
		}

		
		preg_match('#Remote Access Software</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$ras);
		$ras = $ras[2];
		if($ras == "") $ras = "0";
		
		if($ras > "0"){
			
			$ras_status = "<b><i>Yes</i><b>";
			$ras_img = "error";
			
		}else{
			
			$ras_status = "No";
			$ras_img = "check";
		}

		
		preg_match('#Information Stealers</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$is);
		$is = $is[2];
		if($is == "") $is = "0";
		
		if($is > "0"){
			
			$is_status = "<b><i>Yes</i><b>";
			$is_img = "error";
			
		}else{
			
			$is_status = "No";
			$is_img = "check";
		}

		
		preg_match('#Dialers</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$d);
		$d = $d[2];
		if($d == "") $d = "0";
		
		if($d > "0"){
			
			$d_status = "<b><i>Yes</i><b>";
			$d_img = "error";
			
		}else{
			
			$d_status = "No";
			$d_img = "check";
		}

		
		preg_match('#Downloaders</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$dl);
		$dl = $dl[2];
		if($dl == "") $dl = "0";
		
		if($dl > "0"){
			
			$dl_status = "<b><i>Yes</i><b>";
			$dl_img = "error";
			
		}else{
			
			$dl_status = "No";
			$dl_img = "check";
		}		


		
		preg_match('#Embedded Link To Malicious Site</strong>(.*?)Threats found: <strong>(.*?)</strong>#is',$norton_content,$el);
		$el = $el[2];
		if($el == "") $el = "0";
		
		if($el > "0"){
			
			$el_status = "<b><i>Yes</i><b>";
			$el_img = "error";
			
		}else{
			
			$el_status = "No";
			$el_img = "check";
		}		
		
		

		
				
		$adsense = "";
		$analytics = "";

 		preg_match('/("|\')UA-([0-9]+)-([0-9]{1,3})("|\')/is',$content,$analytics);
		$analytics = str_replace(array('"',"'"),"",$analytics[0]);
		
 		preg_match('/pub-([0-9]+)/is',$content,$adsense);
		$adsense = $adsense[0];

		if($adsense) $adsense = '<div class="ui-widget"><div class="ui-state-highlight  widg-info ui-corner-all"><span class="ui-icon ui-icon-info"></span>Google Adsense installed:  '.$adsense.'<br /></div></div>';
		if($analytics) $analytics = '<div class="ui-widget"><div class="ui-state-highlight  widg-info ui-corner-all"><span class="ui-icon ui-icon-info"></span>Google Analytics installed:  '.$analytics.'<br /></div></div>';
		$ip = gethostbyname("www.$domain");
		
		$powered_by = phpversion();
		
		$runing_on = $_SERVER['SERVER_SOFTWARE'];
		

		
		preg_match_all('#<script(.*?)>#is',$content,$js_links);
		$js_count = 0;
		$js_html = '';

		foreach ($js_links[1] as $js_link){
			preg_match('#(\.js)#is',$js_link,$js_check);
			if($js_check[1]){
				preg_match('#src=("|\')(.*?)("|\')#is',$js_link,$js_file);			
				$js_file = $js_file[2];
				if(substr($js_file, 0, 4) != "http")	$js_file	=	$url.'/'.trim($js_file,"/");
				$js_html .= $js_file.'  <br />';
				$js_count++;
			}
		}

		
		
			$internal_links = 0;
			$internal_follow = 0;
			$internal_html = "";
			$external_links = 0;
			$external_follow = 0;
			$external_html = "";
			$link_text = "";
			$internal_pages = array();
			preg_match_all('/<a(.*?)<\/a>/is',$content,$page_links);
			foreach ($page_links[0] as $key => $link){
				
				preg_match('/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/is',$link,$anchor_link);
				preg_match('/<img/is',$link,$check_link_image);
				preg_match('/nofollow/is',$link,$check_nofollow);
				if($check_link_image[0]) $anchor_text = "[image]";
				else $anchor_text = strip_tags($anchor_link[4]);
				$link_text .= $anchor_link[4];
				if(trim(strip_tags($anchor_link[2])) != ""){
				if(substr($anchor_link[2], 0, 4) != "http"){
					$internal_pages[] = $url.$anchor_link[2];
					$internal_html .= $url.$anchor_link[2]."  <br />";
					if($check_nofollow[0] == "") $internal_follow++;
					$internal_links++;
					
				}else{
				preg_match("/$domain/is",$anchor_link[2],$check_link);
					
					if ($check_link[0]) {
						$internal_pages[] = $anchor_link[2];
						$internal_html .= $anchor_link[2]."  <br />";
						if($check_nofollow[0] == "") $internal_follow++;
						$internal_links++;
					}else{
						$external_html .= "<tr><td>".$anchor_text."</td><td>".$anchor_link[2]."</td></tr>";
						if($check_nofollow[0] == "") $external_follow++;						
						$external_links++;
						
					}

				
				}
				}

			}

			
		

$generated_content = <<<EOF
<div id="tabs">
	
		<ul>
			<li id="litab2"><a href="#tab2"><span id="mtab2">Sitecheck Results</span></a></li>
			<li id="litab0"><a href="#tab0"><span id="mtab0">Website details</span></a></li>
			<li id="litab1"><a href="#tab1"><span id="mtab1">Blacklisting status</span></a></li>
		</ul>
	
		<div id="tab0">
			<div id="accordion0">

				<h3><a href="#">Web server details</a></h3>
				<div style="line-height: 150%;">
				   Scan for: <strong><a href="$url  ">$url  </a></strong><br />
				   Hostname: <strong>$domain  </strong><br />
				   IP address: <strong>$ip  </strong><br />
					<br />
<br /><b>Web application details:</b><br />
				$adsense
				$analytics
   
				</div><!-- End Empty Div -->
	
				<h3><a href="#">List of links found</a></h3><div>
				$internal_html
</div><h3><a href="#">List of javascripts included</a></h3><div>
$js_html
</div>				
				<br />
			</div><!-- End Accordian0 -->
		</div><!-- End Tab0 -->
	
	
	
		<div id="tab1">
			<div id="accordion1">
	
				<h3><a href="#">Blacklist status</a></h3><div>
                    <div class="ui-widget">
                    <div class="$google_class">
                    <span class="$google_span"></span>
                    $google_html
</div></div>
                    <div class="ui-widget">
                    <div class="$norton_class">
                    <span class="$norton_span"></span>
                    $norton_html
</div></div>

                    <div class="ui-widget">
                    <div class="$mcafee_class">
                    <span class="$mcafee_span"></span>
                    $mcafee_html
</div></div>

                    <div class="ui-widget">
                    <div class="$yandex_class">
                    <span class="$yandex_span"></span>
                    $yandex_html
</div></div>			
				</div><!-- REQUIRED ENDING DIV -->
			</div><!-- End Accordian1 -->
		</div><!-- End Tab1 -->
	
	
		<div id="tab2">
			<div id="accordion2">
	
				
<div class="col_10b left">

	<div class="website-trust">
	 
		<div class="col_1b left">
    		<img width="82" height="82" alt="" src="img/$report_img.png" />
		</div>

		<div style="font-size:14px;" class="resltstxt col_7 left">
                <table style="position:relative; left:10px; top:10px;">
                <tr>
                <td>web site: &nbsp;</td><td><span class="$report_class">$domain</span></td>
                </tr>
                <tr>
                <td>status: </b><td><span class="$report_class">$report_status</span></td>
                </tr>
                
                <tr><td>&nbsp;</td><td>


                </td></tr>
                </table>
		</div>

	</div>

</div>
    
<br class="clearer" />
 

			    <span style="position:relative;left:10px">
			    			    <b>Security report (<i>$report_warning</i>):</b>
			    			    <table style="position:relative;left:10px">
			        
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$bl_img" src="img/$bl_img.png" /></td><td><b>Blacklisted: </b></td><td>$bl_status</td></tr>		    	
					
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$malware_img" src="img/$malware_img.png" /></td><td><b>Malware: </b></td><td>$malware_status</td></tr>

				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$v_img" src="img/$v_img.png" /></td><td><b>Viruses: </b></td><td>$v_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$dbd_img" src="img/$dbd_img.png" /></td><td><b>Drive-By Downloads: </b></td><td>$dbd_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$md_img" src="img/$md_img.png" /></td><td><b>Malicious Downloads: </b></td><td>$md_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$w_img" src="img/$w_img.png" /></td><td><b>Worms: </b></td><td>$w_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$sa_img" src="img/$sa_img.png" /></td><td><b>Suspicious Applications: </b></td><td>$sa_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$$sbc_img" src="img/$sbc_img.png" /></td><td><b>Suspicious Browser Changes: </b></td><td>$sbc_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$sr_img" src="img/$sr_img.png" /></td><td><b>Security Risks: </b></td><td>$sr_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$hv_img" src="img/$hv_img.png" /></td><td><b>Heuristic Viruses: </b></td><td>$hv_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$a_img" src="img/$a_img.png" /></td><td><b>Adware: </b></td><td>$a_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$t_img" src="img/$t_img.png" /></td><td><b>Trojans: </b></td><td>$t_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$pa_img" src="img/$pa_img.png" /></td><td><b>Phishing Attacks: </b></td><td>$pa_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$s_img" src="img/$s_img.png" /></td><td><b>Spyware: </b></td><td>$s_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$b_img" src="img/$b_img.png" /></td><td><b>Backdoors: </b></td><td>$b_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$ras_img" src="img/$ras_img.png" /></td><td><b>Remote Access Software: </b></td><td>$ras_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$is_img" src="img/$is_img.png" /></td><td><b>Information Stealers: </b></td><td>$is_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$d_img" src="img/$d_img.png" /></td><td><b>Dialers: </b></td><td>$d_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$dl_img" src="img/$dl_img.png" /></td><td><b>Downloaders: </b></td><td>$dl_status</td></tr>
				
				<tr><td><img style="position:relative;top:5px;" height="30" width="30" alt="$el_img" src="img/$el_img.png" /></td><td><b>Embedded Link To Malicious Site: </b></td><td>$el_status</td></tr>
				
				
				
			</table>
	
				<br />
				</span>
	
			
								
			</div>
		</div><!-- End Accordion2 -->
		</div><!-- End Tab2 -->


EOF;

echo $generated_content;
}

?>
