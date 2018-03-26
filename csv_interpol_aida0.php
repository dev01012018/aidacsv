<?php
//
// In the name of The Creator
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// TITLE: Linear Interpolator
// V00.01
//
// ** This code is to be gradually developed to reach its written targets.
//
//This page will linearly interpolate a value between 2 points (y0,y1). 
//The value interpolated is at a distance (x0) from (y0).
//The distance between (y0) and (y1) is (x1).
//The data is read from a MySQL.DATABASE.TABLE. The output is stored back to another MySQL.DATABASE.TABLE.
//
// 1- MYSQL -> PHP:
//		The data fetched is from a table which carries the target 2 dimentional array or 2 points.
//
// 2- PHP -> INTERPOLATE
//		The data to be interpolated into smaller intervals is chosen by the user.
//
// 3- INTERPOLATED -> MySQL
//		The output is stored back to the database.
//
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// UPDATES:
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// v0.0.0000:	no updates
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//FIXED TO INTERPOLATE AIDA FORMAT AS FILLED BELOW
//=====================================================================================================
//edit here to change the database input and output information
$GLOBALS["db_url"]="ip_number:port_number";//edit here: the output mysql database's url
$GLOBALS["db_un"] = "unsername";//edit here: the output mysql database's user name
$GLOBALS["db_pw"] = "password";//edit here: the output mysql database's password
$GLOBALS["output_db0"] = "output_dbn";//edit here: output database name
$GLOBALS['output_tn0'] = "output_tn";//edit here: output table name

//GLOBALS
$GLOBALS['dim0'] = array();
$GLOBALS['dim1'] = array();
//$GLOBALS['interval0'] = 0;

$GLOBALS['sql_link'] = "";
$GLOBALS['input_db0'] = "input_dbn";//edit here: input database name
$GLOBALS['input_tn0'] = "input_tn";//edit here: input table name//$_GET['itn0'];
$GLOBALS['input_col0'] = "input_col0";//default input table column0
$GLOBALS['input_col1'] = "input_col1";//default input table column1
$GLOBALS['input_id0'] = $_GET['iid0'];;
$GLOBALS['input_id1'] = $GLOBALS['input_id0'] + 1;//$_GET['iid1'];//usually iid1 = iid0 + 1
//the ids are to be sequentially stacked

$GLOBALS['y0'] = "";//auto fetched
$GLOBALS['y1'] = "";//auto fetched
$GLOBALS['x0'] = "";//auto calculated
$GLOBALS['x1'] = $_GET['ix1'];//to be determined by the user

$GLOBALS['output_db0'] = "output_dbn";//edit here: output database name
$GLOBALS['output_tn0'] = "outpub_tn";//edit here: output table name
$GLOBALS['output_col0'] = "output_col0";//default output table column0
$GLOBALS['output_col1'] = "output_col1";//default output table column1
$GLOBALS['output_id0'] = "";//auto generated

echo date("H:i:s",600)."<br>";
echo seconds2hhmmss($GLOBALS['x1'])."<br>";
echo seconds2hhmmss(1500)."<br>";
echo hhmmss2seconds("00:25:00")."<br>";

//==============================================================================================================
//========================================= PHASE1: PHP/MYSQL SECTION ==========================================

$GLOBALS['sql_link'] = mysqli_connect($GLOBALS["db_url"],$GLOBALS["db_un"],$GLOBALS["db_pw"]) or die("code82: ".mysqli_error($sql_link));
mysqli_set_charset($sql_link,"utf8");
//mysqli_select_db($sql_link, "aida0")or die("code81: ".mysqli_error($sql_link));

function get_id($sqll, $tbl, $fld, $val){
    $q = "SELECT id FROM `$tbl` WHERE `$fld`='$val'";
    $r = mysqli_query($sqll, $q) or die(mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
    $s = mysqli_fetch_array($r,MYSQLI_NUM);
    return $s[0];
}

function get_val($sqll, $tbl, $id, $fld){
	$q = "SELECT $fld FROM `$tbl` WHERE `id`='$id'";
	//echo "<br>\$q=".$q."<br>";//test line
	$r = mysqli_query($sqll, $q) or die("code80: ".mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
    $s = mysqli_fetch_array($r,MYSQLI_NUM);
    return $s[0];
}

function insert_interpol($sqll, $tn, $fld0, $fld1, $val0, $val1){
	$q = "INSERT INTO `$tn` (`id`,`$fld0`,`$fld1`) VALUES (NULL, '".$val0."', '".$val1."')";
	//echo $q."<br>";
	$r = mysqli_query($sqll, $q) or die("cdoe10: insert_interpol, ->error: ".mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
	while(mysqli_affected_rows($sqll) < 1){if ($while_incrementals > 999999){return 0;}$while_increments++;}
	return 1;
}

mysqli_select_db($GLOBALS['sql_link'], $GLOBALS['input_db0'])or die("code81: ".mysqli_error($sql_link));
$GLOBALS['y0'] = get_val($GLOBALS['sql_link'], $GLOBALS['input_tn0'], $GLOBALS['input_id0'], $GLOBALS['input_col1']);
$GLOBALS['y1'] = get_val($GLOBALS['sql_link'], $GLOBALS['input_tn0'], $GLOBALS['input_id1'], $GLOBALS['input_col1']);
$x0_y0 = get_val($GLOBALS['sql_link'], $GLOBALS['input_tn0'], $GLOBALS['input_id0'], $GLOBALS['input_col0']);
$x0_y1 = get_val($GLOBALS['sql_link'], $GLOBALS['input_tn0'], $GLOBALS['input_id1'], $GLOBALS['input_col0']);
$x0_y0 = hhmmss2seconds($x0_y0);$x0_y1 = hhmmss2seconds($x0_y1);
$GLOBALS['x0'] = $x0_y1 - $x0_y0;//aka distance
echo $GLOBALS['x0']."<br>";

echo "<br>".$x0_y0." & ".$x0_y1."<br>";

//==============================================================================================================
//========================================= PHASE2: INTERPOLATION ==============================================

// returns the linear interpolated value (y) between two values (y0,y1). the two values are
// (x0) units of distance from each other.
// (y) is (x1) units of distance from y0 (units of distance can be time, length, speed ... etc)
function find_linear_interpolation($y0,$y1,$x0,$x1){
	$lif0 = find_linear_interpolation_fraction($x0,$x1);
	//echo "<br>\$lif0=".$lif0."<br>";
	return ($y0 + (($y1-$y0)*$lif0));
}

//This is a horizontal (timeline/distance...x-axis) linear fraction. As most interpolation plots are precieved!
function find_linear_interpolation_fraction($x0,$x1){ return (1-(($x0-$x1)/$x0)); }

$op0 = find_linear_interpolation($GLOBALS['y0'],$GLOBALS['y1'],$GLOBALS['x0'],$GLOBALS['x1']);
echo "<br>y0=".$GLOBALS['y0'].",y1=".$GLOBALS['y1'].",x0=".$GLOBALS['x0'].",x1=".$GLOBALS['x1']."<br>";
echo "<br>op0=".$op0."<br>";
$z0= $x0_y0 + $x1;
$GLOBALS['output_val0'] = seconds2hhmmss($z0);
echo "output_val0=seconds2hhmmss("."x0_y0 + x1=".($x0_y0+$x1).")=".$GLOBALS['output_val0']."<br>";
$GLOBALS['output_val1'] = $op0;

//==============================================================================================================
//========================================= PHASE3: SAVE INTERPOLATION TO MySQL ================================

function create_output_table($sqll, $tn){
	$q = "CREATE TABLE IF NOT EXISTS `$tn`(`id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `".$GLOBALS['output_col0']."` VARCHAR(50) UNIQUE KEY, `".$GLOBALS['output_col1']."` VARCHAR(50)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;";
	$r = mysqli_query($sqll, $q) or die(mysqli_error($sqll));//"num=2&v0=err&v1=bad read_one query");
	//(tbc)table creation checking must be coded for better error tracking
}

$new_x = seconds2hhmmss($x0_y0 + $GLOBALS['x1']);
mysqli_select_db($GLOBALS['sql_link'], $GLOBALS['output_db0']) or die("code81: ".mysqli_error($sql_link));
create_pgl_table($GLOBALS['sql_link'], $GLOBALS['output_tn0']);
insert_interpol($GLOBALS['sql_link'], $GLOBALS['output_tn0'], $GLOBALS['output_col0'], $GLOBALS['output_col1'], $GLOBALS['output_val0'], $GLOBALS['output_val1']);
	
//die("EOE: id0=".$GLOBALS['input_id0']."<br>");//for testing
//==============================================================================================================
//========================================= APPENDIX1: AUXILIARIES =============================================

function hhmmss2seconds($ip0){
	$ip1 = explode(":",$ip0);
	$op0 = (int)$ip1[0]*60*60 + (int)$ip1[1]*60 + (int)$ip1[2];
	return $op0;
}

//entering negative integer values will subtract, parts of seconds are rounded up
//an example of 23:59:00 + 60 seconds will be converted to 00:00:00
//an example of 23:59:00 + 120 seconds will be converted to 00:01:00
function time_string_add_seconds($time_string, $seconds){
	$seconds2 = $seconds;
	while ($seconds2 >= 24*60*60){
		if ($seconds2 == 24*60*60){return $time_string;}
		$seconds2 = $seconds2 - 24*60*60;
		echo "<br>seconds2=".$senods2."<br>";//test line
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
	
	echo "<br>mod hm1=".$hm1.", mod mm1=".$mm1."<br>";//test line
	
	if (strlen($h1) < 2){ $h1 = "0".$h1;}
	if (strlen($m1) < 2){ $m1 = "0".$m1;}
	if (strlen($s1) < 2){ $s1 = "0".$s1;}
	return $h1.":".$m1.":".$s1;
}

//value of time in seconds converted to hh:mm:ss format
//time over 23:59:59 will be cut and rewinded fit the range of 00:00:00->23:59:59
//the excess to be carried out as days/months/years can be taked from the $hm1 modulus
function seconds2hhmmss($ip){
	$h1 = floor($ip / (60 * 60.00));
	$hm1 = $ip - ($h1 * 60 * 60.00);//modulus in seconds
	echo "h1=".$h1."&hm1=".$hm1."<br>";
	if ($h1 >= 24){$h1 = $h1 - 24;}//cut days can be taken from hear to days/months/years
	$m1 = floor($hm1 / 60);$mm1 = $hm1 - $m1 * 60;//modulus in seconds
	$s1 = round($mm1);
	
	echo $s1."<br>";
	if (strlen($h1) < 2){$h1 = "0".$h1;}
	if (strlen($m1) < 2){$m1 = "0".$m1;}
	if (strlen($s1) < 2){$s1 = "0".$s1;}
	echo strlen($s1)."-".$s1."<br>";
	
	return $h1.":".$m1.":".$s1;
}

?>
