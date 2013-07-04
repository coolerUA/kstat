<?php
include "conf/config.php";
require_once 'Cache/Lite.php';
$id=mktime(date("H"), (floor(date("i")/10))*10, 0, date("n"), date("j"), date("Y"));
if (isset($_GET['id'])){ $id=time();}

$opts = array(
    'cacheDir' => './cache/',
    'automaticSerialization' => true,
    'lifeTime' => 600 );

$globcount = microtime(true);

function dbconnect(){
    include('conf/config.php');
    $link = mysqli_connect($config['MYSQL']['HOST'], $config['MYSQL']['USER'], $config['MYSQL']['PWD']) or die("Could not connect : " . mysqli_error($link));
    mysqli_select_db($link, $config['MYSQL']['DBNAME']) or die("Could not select database");
    $GLOBALS['link']=$link;
}

function dbas(){
    include "conf/config.php";
    $linkas = mysqli_connect($config['MYSQLas']['HOST'], $config['MYSQLas']['USER'], $config['MYSQLas']['PWD'], $config['MYSQLas']['DBNAME']) or die("Could not connect : " . mysqli_error($linkas). "<br> mysql connect error: ".mysqli_connect_error($linkas));
    mysqli_select_db($linkas, $config['MYSQLas']['DBNAME']) or die("Could not select database");
    $GLOBALS['linkas']=$linkas;
}

function get_user_info($userid) {
    $query="SELECT `firstname`,`lastname`,`username` FROM `swstaff` where `staffid` = '".$userid."';";
    dbconnect();
    global $link;
//    $starttime = microtime(true);
    $result = mysqli_query($link, $query, MYSQLI_USE_RESULT) or die("Query failed (info)[$link] : " . mysqli_error($link));
//    $endtime = microtime(true); $duration = $endtime - $starttime;
    $row=mysqli_fetch_row($result);
    return "".$row['0']." ".$row['1']." (".$row['2'].")<!-- ".$userid." -->";
}

function get_user_chat($userid) {
    $query="select count(`chatobjectid`) from `swchatobjects` where `chatstatus` = '3' and `staffpostactivity` > UNIX_TIMESTAMP(CURDATE()) and `staffid` = '".$userid."';";
    dbconnect();
    global $link;
//    $starttime = microtime(true);
    $result = mysqli_query($link, $query, MYSQLI_USE_RESULT) or die("Query failed (chat) : " . mysqli_error($link));
//    $endtime = microtime(true); $duration = $endtime - $starttime;
    $row=mysqli_fetch_row($result);
    return $row['0'];
}

function get_user_ticket($userid) {
    $query="select count(*) from `swticketauditlogs` where `dateline` > unix_timestamp(curdate()) and `creatorid` = '".$userid."' and `actionmsg` = \"Ticket status changed from: Open to: In Progress\";";
    dbconnect();
    global $link;
//    $starttime = microtime(true);
    $result = mysqli_query($link,$query, MYSQLI_USE_RESULT) or die("Query failed (ticket): " . mysqli_error($link));
//    $endtime = microtime(true); $duration = $endtime - $starttime;
    $row=mysqli_fetch_row($result);
    return $row['0'];
}

function get_user_phone($userid) {
    include "conf/config.php";
    $query="SELECT count(*)  FROM `cdr` WHERE `calldate` > curdate() and `disposition` = 'ANSWERED' and `dstchannel` like 'SIP/".$sip[$userid]."%' AND  `lastapp` !=  'Hangup'";
    dbas();
    global $linkas;
//    $starttime = microtime(true);
    $result = mysqli_query($linkas, $query, MYSQLI_USE_RESULT) or die("Query failed (chat) : " . mysqli_error($linkas)."|||".mysqli_connect_error($linkas));
//    $endtime = microtime(true); $duration = $endtime - $starttime;
    $row = mysqli_fetch_row($result);
    return $row[0];
}

$Cache_Lite = new Cache_Lite($opts);

if ($design = $Cache_Lite->get($id)) {
echo($design);
}
else {

$header= <<< EOH
<html>
<head>
<script type="text/javascript" src="./js/jquery.min.js"></script>
<script type="text/javascript" src="./js/jquery.calculation.js"></script>
<script type="text/javascript">
 function start()
 {
$("td[value^='" + $("td[name=chat]").max() + "'];td[name=chat]").css("background-color","#C2FFCE");
$("td[value^='" + $("td[name=ticket]").max() + "'];td[name=ticket]").css("background-color","#C2FFCE");
$("td[value^='" + $("td[name=phone]").max() + "'];td[name=phone]").css("background-color","#C2FFCE");
$("td[value^='0'];td[name=chat]").css("background-color","#FFD9D9");
$("td[value^='0'];td[name=ticket]").css("background-color","#FFD9D9");
$("td[value^='0'];td[name=phone]").css("background-color","#FFD9D9");
$("td[name=chat]").sum("keyup", "#Echat");
$("td[name=ticket]").sum("keyup", "#Eticket");
$("td[name=phone]").sum("keyup", "#Ephone");


EOH;
# <?php

$header2= <<< EOH
}
</script>

</head>
<body onload="start()">
<table border="2" cellpadding="2">
<tr> <td>user</td> <td>chat</td> <td>ticket</td> <td>phone</td><td>Σ</td></tr>

EOH;
# <?php

foreach (explode(",", $config['enabledID']) as $user) {
$chat=get_user_chat($user);
$ticket=get_user_ticket($user);
$phone=get_user_phone($user);

$js.="$(\"td[id=".$user."]\").sum(\"keyup\", \"#totalSum".$user."\");\r\n";

$design.="<tr><td>".get_user_info($user)."</td>";
$design.="<td align=center id=\"".$user."\" name=\"chat\" value=\"".$chat."\" >".$chat."</td>";
$design.="<td align=center  id=\"".$user."\" name=\"ticket\" value=\"".$ticket."\" >".$ticket."</td>";
$design.="<td align=center  id=\"".$user."\" name=\"phone\" value=\"".$phone."\" >".$phone."</td>";
$design.="<td align=center  name=\"summ\" id=\"totalSum".$user."\"></td>";
$design.=" </tr>\r\n";
}

$design.="<tr><td>Σ</td><td  align=center id=\"Echat\"></td><td  align=center id=\"Eticket\"></td><td align=center  id=\"Ephone\"></td><td></td></table>";
$endglobcount = microtime(true); $globduration = $endglobcount - $globcount;
$design.= "generated $globduration<br>last update: ".date("Y-m-d H:i:s",$id);

$designfull=$header.$js.$header2.$design;
echo $designfull;

$Cache_Lite->save($designfull);
}
$previd=$id-600;
$Cache_Lite->remove($previd);
