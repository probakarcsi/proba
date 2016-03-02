<?
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
session_start();

include('lib/config.php');
include('lib/facebook-php-sdk-master/src/facebook.php');


// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => $fbconfig['appId'],
  'secret' => $fbconfig['secret'],
));


// Get User ID
$userid = $facebook->getUser();

if ($userid) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $userid = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($userid) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $params = array('display'=>'page', 'redirect_uri'=>$fbconfig['appUrl']);
  $loginUrl = $facebook->getLoginUrl($params);
  echo "<script type='text/javascript'>top.location.href = '". $loginUrl. "';</script>"; exit;
}

$fbme = $facebook->api('/me');
$usernev = $fbme["first_name"]." ".$fbme["last_name"];



$_SESSION['index'] = 0;

$fajl = file('videok.txt');

//  Véletlen 5 videó kiválasztása
$numbers = range(1, count($fajl));
shuffle($numbers);
$_SESSION["dalok"] = array_slice($numbers, 0, 5);


?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<meta charset="utf-8"/>
<link rel="stylesheet" type="text/css" href="style.css?v=2016-02-27" media="all"/>
<script type="text/javascript" src="lib/jquery-1.8.2.min.js"></script>
</head>

<body class="jatek">

<div id="pont">0</div>
<div id="ido"></div>
<div id="resp"></div>

<iframe id="if" src="vid.php" style="position:absolute; left:26px; top:55px; width:481px; height:260px; overflow:hidden;" frameBorder="0"></iframe>

<div id="potty1" class="potty"></div>
<div id="potty2" class="potty"></div>
<div id="potty3" class="potty"></div>
<div id="potty4" class="potty"></div>
<div id="potty5" class="potty"></div>

</body>
</html>
