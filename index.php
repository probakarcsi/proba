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

if ($_GET["code"]) {
	header("Location: http://tv2.hu/szorakozas/jatekok/122438_felismered_a_nagy_duett_enekeseit_es_dalait.html");
	exit;
}

$fbme = $facebook->api('/me');
$usernev = $fbme["last_name"]." ".$fbme["first_name"];


?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="style.css?v=2016-02-27" media="all"/>
</head>

<body class="kezdo">
    <a href="jatek.php?<?=time()?>"></a>
</body>
</html>