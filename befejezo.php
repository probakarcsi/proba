<?
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
session_start();

include('lib/config.php');
include('lib/facebook-php-sdk-master/src/facebook.php');


// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => $fbconfig['appId'],
  'secret' => $fbconfig['secret']
));

// Get User ID
$userid = $facebook->getUser();

if ($userid) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($userid) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $params = array('scope' => 'user_friends', 'display'=>'page', 'redirect_uri'=>$fbconfig['appUrl']);
  $loginUrl = $facebook->getLoginUrl($params);
  echo "<script type='text/javascript'>top.location.href = '". $loginUrl. "';</script>";
	exit();
}

$fbme = $facebook->api('/me');
$usernev = $fbme["last_name"]." ".$fbme["first_name"];

$pont = intval($_SESSION['fbpont']);


$fajl_utvonal = 'toplista.txt';
$nevek = $pontok = $useridk = array();

//  userid     nev     pont
$sorok = file($fajl_utvonal);
foreach ($sorok as $sor) { 
    $tags = explode("\t", $sor);
    $useridk[] = trim($tags[0]);
    $nevek[] = trim($tags[1]);
    $pontok[] = trim($tags[2]);
}


//  Még nincs benne a toplistában, akkor beleteszi
if (!in_array($userid, $useridk)) {
    $useridk[] = $userid;
    $nevek[] = $usernev;
    $pontok[] = $pont;
}
//  Egyébként frissíti a pontjait
else {
    $i=0;
    while ($useridk[$i] != $userid) $i++;
    
    if ($pont > $pontok[$i])
        $pontok[$i] = $pont;
}

array_multisort($pontok, SORT_NUMERIC, SORT_DESC, $nevek, $useridk);



$fajl = fopen($fajl_utvonal, 'w+');
for ($i=0; $i<count($nevek); $i++) {
    fwrite($fajl, $useridk[$i]."\t".$nevek[$i]."\t".$pontok[$i]."\r\n");
}
fclose($fajl);

?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="style.css?v=2016-02-27" media="all"/>

<script type="text/javascript" src="lib/jquery-1.8.2.min.js"></script>

<script src="http://connect.facebook.net/hu_HU/all.js#xfbml=1"></script>
<SCRIPT LANGUAGE="JavaScript">
$(document).ready(function() {
    $('#fbshare').click(function(){
		FB.init({appId: "410321835716706", status: true, cookie: true});
		FB.ui({
			method: 'feed',
			link: 'http://tv2.hu/musoraink/a_nagy_duett',
			picture: 'http://supertv2.hu/data/files/5/5763.325x183.jpg',
			name: 'A Nagy Duett játék a tv2.hu-n',
			description: 'A Nagy Duett játékban <?=$_SESSION['fbpont']?> pontot értem el'
		});
    });
});
</SCRIPT>

</head>

<body class="befejezo">
    <div class="pontszam"><?=$_SESSION['fbpont']?></div>
    <div id="fbshare"></div>
    <a href="toplista.php" class="toplista"></a>
    <a href="jatek.php" class="ujjatek"></a>
</body>
</html>

<? $_SESSION['fbpont'] = 0; ?>
