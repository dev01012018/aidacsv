<?php
//================================================================================================
// In the name of The Creator
//======================================= PHASE0 =================================================
echo "<br>#### PHASE0: META & TESTING ####<br>";
echo "By AEJ EST 2010(c). implemented by: YAS<br>";
echo "csv_read5.php<br>";
echo "1-reads \"input.2aida\" file to an array.<br>";
echo "2-splits the array into a time array & a glucose_level array.<br>";
echo "3-saves the array into MySQL.<br>";

date_default_timezone_set('Asia/Baghdad');
$GLOBALS['aida_csv'][] = array();
$GLOBALS['aida_glucose_level'][] = array();
$GLOBALS['aida_ts'][] = array();

//=====================================================================================================
//edit here to change the database input and output information
$GLOBALS["db_url"]="ip_number:port_number";//the output mysql database's url
$GLOBALS["db_un"] = "username";//the output mysql database's user name
$GLOBALS["db_pw"] = "password";//the output mysql database's password
$GLOBALS["output_db_name"] = "output_dbn";//output database name
$GLOBALS['table_name'] = "output_tn";//output table name

//$GLOBALS['fixed_year'] = 2017;
//$GLOBALS['fixed_month'] = 11;
//$GLOBALS['fixed_day'] = 02;
//======================================= PHASE1: READ =================================================
echo "<br>#### PHASE1: FILE LOADING AND PREPARATION OF DATA INTO AN ARRAY ####<br>";
// passed on 31OCT2017 YAS

$fn0 = fopen("input.2aida", "r") or die("Unable to open file!");
$data0 = fread($fn0,filesize("input.2aida"));
fclose($fn0);

$fn1 = fopen("undecoded_space.txt", "r") or die("Unable to open file!");
$us0 = fread($fn1,filesize("undecoded_space.txt"));
fclose($fn1);

$data1 = explode("\r\n", $data0);
$data1 = str_replace($us0,",",$data1);
$data2 = "";

for ($i=0;$i<count($data1);$i++){
	$data2 = str_replace(/*$us0*/" ",",",$data2.$data1[$i].",");
	//echo "<br>\$data2@i=".$i."=".$data2."<br>";//test line
}

//echo "\$data2=".$data2;//input as read from file//test line
$GLOBALS['aida_csv'] = explode(",",$data2);
$data4 = "";

$toggler = 1;
for ($i=0;$i<count($GLOBALS['aida_csv'])-2;$i++){
	if ($i==0){$GLOBALS['aida_csv'][$i]="00:00:00";$toggler=!$toggler;}else{
	if ($toggler){$GLOBALS['aida_csv'][$i] = decimal2time_converter($GLOBALS['aida_csv'][$i]);}$toggler = !$toggler;}
	//echo "<br>\$data3[".$i."]=".$data3[$i]."<br>";//test line
	$data4 = $data4.$GLOBALS['aida_csv'][$i].",";
}
$data4 = rtrim($data4,",");

//$data4 = array_dblidx_to_line($data3);
//echo "<br>\$data4=".$data4."<br>";//test line

//echo date("d-m-Y h:i:s",1509608056);//test line
echo "<br>>>>> PHASE1 END >>>><br>";/*die();*/
//=========================== PHASE2: SEPARATE ==================================================================
echo "<br>#### PHASE2: SEPARATE AIDA ARRAY ####<br>";

//into two arrays, one for time the other for data
function separate_aida(){
	$toggler = 0;
	$idx2 = 0;
	for ($i0=0;$i0<count($GLOBALS['aida_csv']);$i0++){
		if ($toggler == 0){
		$GLOBALS['aida_ts'][$idx2] = $GLOBALS['aida_csv'][$i0];
		$toggler = 1;
		}else{
		$GLOBALS['aida_glucose_level'][$idx2] = $GLOBALS['aida_csv'][$i0];
		$toggler = 0;
		$idx2++;
		}
	}
}

separate_aida();

echo "<br>>>>> PHASE2 END >>>><br>";//die();
//==============================================================================================================
//=========================== PHASE3: SAVE INTO MySQL ==========================================================
echo "<br>#### PHASE3: OUTPUT ARRAYS TO MYSQL ####<br>";

//connection for login/authentication
$sql_link = mysqli_connect($GLOBALS["db_url"], $GLOBALS["db_un"], $GLOBALS["db_pw"]) or die(mysqli_error($sql_link));
//$sql_link = mysqli_connect("localhost:3306", "root", "root") or die(mysqli_error($sql_link));
mysqli_set_charset($sql_link,"utf8");

function get_id($sqll, $tbl, $fld, $val){
    $q = "SELECT id FROM $tbl WHERE $fld='$val'";
    $r = mysqli_query($sqll, $q) or die(mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
    $s = mysqli_fetch_array($r,MYSQLI_NUM);
    return $s[0];
}

function get_val($sqll, $tbl, $id, $fld){
	$q = "SELECT $fld FROM $tbl WHERE id='$id'";
	$r = mysqli_query($sqll, $q) or die(mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
    $s = mysqli_fetch_array($r,MYSQLI_NUM);
    return $s[0];
}

//aida
function create_aida_table($sqll, $aida_code){
$q = "CREATE TABLE IF NOT EXISTS `$aida_code`(`id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `timestamp0` VARCHAR(50) UNIQUE KEY, `glucose_level` VARCHAR(50)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;";	
$r = mysqli_query($sqll, $q) or die(mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
	//(tbc)table creation checking must be coded for better error tracking
}

//return 1: table might exist, else: table does not exist or unknown error
function chk_table_exist($sqll, $tn){
	error_reporting(0);
	$q = "SELECT 1 FROM $tn LIMIT 1";
	$r = mysqli_query($sqll, $q);// or die(mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
	$s = mysqli_fetch_array($r,MYSQLI_NUM);
	error_reporting(E_ALL);
	if ($s[0] == "1"){return 1;}else{return 0;}
	//return $s[0];
}

function new_fill_aida($sqll, $aida_code){
	$return_array = array();
	$return_failures = 0;
	$while_increments = 0;
	mysqli_select_db($sqll, $GLOBALS["output_db_name"])or die(mysqli_error($sqll));
	create_aida_table($sqll, $aida_code);
	for ($i0=0;$i0<96/*count($GLOBALS['aida_ts'])-1*/;$i0++){
		//echo "<br>ts test i=".$i0." -> ".$GLOBALS['aida_ts'][$i0]."<br>";
		$q = "INSERT INTO `$aida_code` (`id`,`timestamp0`,`gl`) VALUES (NULL, '".$GLOBALS['aida_ts'][$i0]."', '".$GLOBALS['aida_glucose_level'][$i0]."')";
		$r = mysqli_query($sqll, $q) or die("new_fill_interpolated, i=".$i0." ->error: ".mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
		while(mysqli_affected_rows($sqll) < 1){if ($while_incrementals > 999){return $while_incrementals * -1;}$while_increments++;}
		
		//if (mysqli_affected_rows($sqll) < 1){$return_array[] = $i0;$return_failures++;}
	}
	return $return_failures;
}

echo "<br>new_fill failures= ".new_fill_aida($sql_link, $GLOBALS['table_name'])."<br>";

echo "<br>>>>> PHASE3 END >>>><br>";//die();
//=========================== END OF RUNNING CODE ====================================================
//=========================== AUXILIARY FUNCTIONS START===============================================

//parts of a day
function time_to_int($t0){
	//echo "<br>NEW TIME_TO_INT : ".$t0."<br>";
	$a0 = explode(":",$t0);
	//echo "<br>time_to_int(".$t0.")-->".$a0[0]."|".$a0[1]."|".$a0[2]."<br>";
	//$v0 = ($a0[0] / 24.00)+($a0[1] / (60 * 24.00)) + ($a0[2] / (60 * 60 * 24.00));
	$v0 = (int)$a0[0]*60*60 + (int)$a0[1]*60 + (int)$a0[2];
	//echo "<br>".$v0."<br>";
	return $v0;
}

//entering negative integer values will subtract, parts of seconds are rounded up
//an example of 23:59:00 + 60 seconds will be converted to 00:00:00
//an example of 23:59:00 + 120 seconds will be converted to 00:01:00
function time_string_add_seconds($time_string, $seconds){
	$seconds2 = $seconds;
	while ($seconds2 >= 24*60*60){
		if ($seconds2 == 24*60*60){return $time_string;}
		$seconds2 = $seconds2 - 24*60*60;
		//echo "<br>seconds2=".$senods2."<br>";//test line
		}
		
	$time1 = explode(":",$time_string);
	if (count($time1)<3){return $time_string;}//bad length, just return the same input
	$h0 = (int)$time1[0];$m0=(int)$time1[1];$s0=(int)$time1[2];
	//echo "<br>add_seconds to (hh:mm:ss): ".$h0.":".$m0.":".$s0."<br>";//test line
	//echo "<br>seconds to be added= ".$seconds."<br>";
	$h1 = floor($seconds / (24 * 60)) + $h0;
	$hm1 = $seconds - ($h1 % (24 * 60));//modulus in seconds
	if ($h1 >= 24){$h1 = $h1 - 24;}
	$m1 = floor($hm1 / 60) + $m0;
	if ($m1 >= 60){$m1 = $m1 - 60; $h1 = $h1 + 1;}
	if ($h1 >= 24){$h1 = $h1 - 24;}
	$mm1 = ($hm1 + $m1 * 60) % 60;//modulus in seconds
	$s1 = round($mm1) + $s0;
	if ($s1 >= 60){$s1 = $s1 - 60; $m1 = $m1 + 1;}
	if ($m1 >= 60){$m1 = $m1 - 60; $h1 = $h1 + 1;}
	if ($h1 >= 24){$h1 = $h1 - 24;}
	
	//echo "<br>mod hm1=".$hm1.", mod mm1=".$mm1."<br>";//test line
	
	if (strlen($h1) < 2){ $h1 = "0".$h1;}
	if (strlen($m1) < 2){ $m1 = "0".$m1;}
	if (strlen($s1) < 2){ $s1 = "0".$s1;}
	return $h1.":".$m1.":".$s1;
}

//AIDA formatted decimal time converter to hh:mm:ss format
function decimal2time_converter($ip){
	if ($ip == "24"){return "00:00:00";}
	if ($ip == "0"){return "00:00:00";}
	$datax = explode(".",$ip);
	if (strlen($datax[0])<2){ $datax[0]="0".$datax[0];}
	if (count($datax)<2){return $datax[0].":00:00";}
	while ((int)$datax[0] >= 24){$datax[0]=(string)((int)$datax[0]-24);}//should be transferred into the next day calender addition
	switch($datax[1]){
		case "00": return $datax[0].":00:00";
		case "25": return $datax[0].":15:00";
		case "5": return $datax[0].":30:00";
		case "75": return $datax[0].":45:00";
	}
}

//value of time in seconds converted to hh:mm:ss format
//time over 23:59:59 will be cut and rewinded fit the range of 00:00:00->23:59:59
//the excess to be carried out as days/months/years can be taked from the $hm1 modulus
function seconds2hhmmss($ip){
	$h1 = floor($ip / (60 * 60));
	$hm1 = $ip - ($h1 * 60);//modulus in seconds
	if ($h1 >= 24){$h1 = $h1 - 24;}//cut days can be taken from hear to days/months/years
	$m1 = floor($hm1 / 60);$mm1 = $hm1 - $m1 * 60;//modulus in seconds
	$s1 = round($mm1);
	return $h1.":".$m1.":".$s1;
}

//a double index array a0,b0,a1,b1,a2,b2... is to be written in a single line separated by commas (CSV)
//the first value is time, the next is glucose level or any other generic decimal/float data value
function array_dblidx_to_line($ip){
	$op = "";
	$toggler = 1;
	for ($i=0;$i<count($ip)-2;$i++){
		if ($i==0){$ip[$i]="00:00:00";$toggler=!$toggler;}else{
		if ($toggler){$ip[$i] = decimal2time_converter($ip[$i]);}$toggler = !$toggler;}
		//echo "<br>\$ip[".$i."]=".$ip[$i]."<br>";//test line
		$op = $op.$ip[$i].",";
	}
	$op = rtrim($op,",");
	return $op;
}

?>

