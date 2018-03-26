<?php

echo "start csv_interpol_aida0_run24.php<br>";
set_time_limit(10000);

$aida_url0 = "the_folder_and_file_name_addressing_the_file(csv_interpol_aida0.php)";//ie. 119.80.92.11/project_files/csv_interpol_aida0.php

for ($i0=1;$i0<98;$i0++){
	for ($i1=0;$i1<15;$i1++){
		$s0 = $aida_url0."?iid0=".$i0."&ix1=".($i1 * 60);
		echo $s0."<br>";
		get_remote_data($s0);
		//header('Location: '.$aida_url0."iid0=".$i0."&ix0=".$i1 * 60);
	}
}

echo "end csv_interpol_aida0_run24.php<br>";
//echo get_remote_data("localhost/iot0/csv_interpol_aida0.php?itn0=0000&iid0=2&ix1=600");

//See Updates and explanation at: https://github.com/tazotodua/useful-php-scripts/
function get_remote_data($url, $post_paramtrs=false)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    if($post_paramtrs)
    {
        curl_setopt($c, CURLOPT_POST,TRUE);
        curl_setopt($c, CURLOPT_POSTFIELDS, "var1=bla&".$post_paramtrs );
    }
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
    curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
    curl_setopt($c, CURLOPT_MAXREDIRS, 10);
    $follow_allowed= ( ini_get('open_basedir') || ini_get('safe_mode')) ? false:true;
    if ($follow_allowed)
    {
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
    curl_setopt($c, CURLOPT_REFERER, $url);
    curl_setopt($c, CURLOPT_TIMEOUT, 60);
    curl_setopt($c, CURLOPT_AUTOREFERER, true);
    curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
    $data=curl_exec($c);
    $status=curl_getinfo($c);
    curl_close($c);
    preg_match('/(http(|s)):\/\/(.*?)\/(.*\/|)/si',  $status['url'],$link); $data=preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/|\/)).*?)(\'|\")/si','$1=$2'.$link[0].'$3$4$5', $data);   $data=preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/)).*?)(\'|\")/si','$1=$2'.$link[1].'://'.$link[3].'$3$4$5', $data);
    if($status['http_code']==200)
    {
        return $data;
    }
    elseif($status['http_code']==301 || $status['http_code']==302)
    {
        if (!$follow_allowed)
        {
            if (!empty($status['redirect_url']))
            {
                $redirURL=$status['redirect_url'];
            }
            else
            {
                preg_match('/href\=\"(.*?)\"/si',$data,$m);
                if (!empty($m[1]))
                {
                    $redirURL=$m[1];
                }
            }
            if(!empty($redirURL))
            {
                return  call_user_func( __FUNCTION__, $redirURL, $post_paramtrs);
            }
        }
    }
    return "ERRORCODE22 with $url!!<br/>Last status codes<b/>:".json_encode($status)."<br/><br/>Last data got<br/>:$data";
}

?>





















































