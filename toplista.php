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

array_multisort($pontok, SORT_NUMERIC, SORT_DESC, $nevek, $useridk);


$baratok_id = $baratok_nev = array();
$bhelyek = $bnevek = $bpontok = array();
$friends = $facebook->api("/me/friends");
foreach ($friends['data'] as $val) { 
    $baratok_id[] = $val['id'];
    $baratok_nev[] = $val['name'];
}

for ($i=0; $i<count($baratok_id); $i++) {

    if (in_array($baratok_id[$i], $useridk)) {  // ha játszott a barátom is
        $bnevek[] = $baratok_nev[$i];
        
//        echo $baratok_nev[$i]."\n".$baratok_id[$i]."\n";
        
        $j=0;
        while ($baratok_id[$i] != $useridk[$j]) $j++;
        
//        echo "$j\n";
        
        $bhelyek[] = $j+1;
        $bpontok[] = $pontok[$j];
    }
}


?>
<!DOCTYPE html>
<html>
<head>
<title></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="style.css?v=2016-02-27" media="all"/>

<script type="text/javascript" src="lib/jquery-1.8.2.min.js"></script>

<script type="text/javascript">
var dbperold = 10;

var db = <?=count($nevek)?>;
var gal_old = Math.ceil(db/dbperold);
var nevek = new Array(<?=count($nevek)?>);
var pontok = new Array(<?=count($pontok)?>);

var bdb = <?=count($bnevek)?>;
var bgal_old = Math.ceil(bdb/dbperold);
var bhelyek = new Array(<?=count($bhelyek)?>);
var bnevek = new Array(<?=count($bnevek)?>);
var bpontok = new Array(<?=count($bpontok)?>);
<?
$i=0;
foreach ($nevek as $nev)
    echo 'nevek['.$i++.'] = "'.$nev.'"; ';
    
$i=0;
foreach ($pontok as $pont)
    echo 'pontok['.$i++.'] = "'.$pont.'"; ';

echo "\n";

$i=0;
foreach ($bhelyek as $hely)
    echo 'bhelyek['.$i++.'] = "'.$hely.'"; ';

$i=0;
foreach ($bnevek as $nev)
    echo 'bnevek['.$i++.'] = "'.$nev.'"; ';

$i=0;
foreach ($bpontok as $pont)
    echo 'bpontok['.$i++.'] = "'.$pont.'"; ';

?>


$(document).ready(function(){
    if (db) toplistaMegjelenit(1);
    $('#mindenki').css('backgroundPosition', '0px -22px');
    
    $('#ism').click(function() {
        $('#mindenki').css('backgroundPosition', '0px 0px');
        $('#ism').css('backgroundPosition', '0px -22px');
        btoplistaMegjelenit(1);
    });
    
    $('#mindenki').click(function() {
        $('#mindenki').css('backgroundPosition', '0px -22px');
        $('#ism').css('backgroundPosition', '0px 0px');
        toplistaMegjelenit(1);
    });
    
    
});


//  A toplista adott oldalát megjeleníti
function toplistaMegjelenit(oldal) {
    var top_html = lapok_html = '';
    
    //  Galéria képeinek megjelenítése
    for (i=(oldal-1)*dbperold; i<(oldal)*dbperold; i++) {
        if (nevek[i]) {
            top_html += '<div class="sor"> <div class="h">'+(i+1)+'</div> <div class="n">'+nevek[i]+'</div> <div class="p">'+pontok[i]+'</div>  </div>';
        }
    }
    $('#lista').html(top_html);


    //  Balra lapozás
    if (oldal > 1) $('#bal').show().unbind('click').click(function(){ toplistaMegjelenit(oldal-1); });
    else $('#bal').hide();

    //  Jobbra lapozás
    if (oldal < gal_old) $('#jobb').show().unbind('click').click(function(){ toplistaMegjelenit(oldal+1); });
    else $('#jobb').hide();



    var i;
    //  Első 4 oldalon van
    if (oldal < 3) {
        i=1;
        while (i<=4 && i<=gal_old) {
            if (i == oldal) lapok_html += '<a class="aktiv" href="javascript: toplistaMegjelenit('+i+')">'+i+'</a>';
            else lapok_html += '<a href="javascript: toplistaMegjelenit('+i+')">'+i+'</a>';

            i++;
        }
    }

    //  3, 4, [5], 6, 7 ...
    else if (oldal >= 3 && oldal<=gal_old-4) {
        for (i=oldal-2; i<=oldal+2; i++) {
            if (i == oldal) lapok_html += '<a class="aktiv" href="javascript: toplistaMegjelenit('+i+')">'+i+'</a>';
            else lapok_html += '<a href="javascript: toplistaMegjelenit('+i+')">'+i+'</a>';
        }
    }

    //  Utolsó 4 oldalon van
    else if (oldal > gal_old-4) {
        for (i=gal_old-4; i<=gal_old; i++) {
            if (i>0) {
                if (i == oldal) lapok_html += '<a class="aktiv" href="javascript: toplistaMegjelenit('+i+')">'+i+'</a>';
                else lapok_html += '<a href="javascript: toplistaMegjelenit('+i+')">'+i+'</a>';
            }
        }
    }

    $('#lapok').html(lapok_html);
}




//  A barát toplista adott oldalát megjeleníti
function btoplistaMegjelenit(oldal) {
    var top_html = lapok_html = '';
    
    //  Galéria képeinek megjelenítése
    for (i=(oldal-1)*dbperold; i<(oldal)*dbperold; i++) {
        if (bnevek[i]) {
            top_html += '<div class="sor"> <div class="h">'+(bhelyek[i])+'</div> <div class="n">'+bnevek[i]+'</div> <div class="p">'+bpontok[i]+'</div>  </div>';
        }
    }
    $('#lista').html(top_html);


    //  Balra lapozás
    if (oldal > 1) $('#bal').show().unbind('click').click(function(){ btoplistaMegjelenit(oldal-1); });
    else $('#bal').hide();

    //  Jobbra lapozás
    if (oldal < bgal_old) $('#jobb').show().unbind('click').click(function(){ btoplistaMegjelenit(oldal+1); });
    else $('#jobb').hide();



    var i;
    //  Első 4 oldalon van
    if (oldal < 3) {
        i=1;
        while (i<=4 && i<=bgal_old) {
            if (i == oldal) lapok_html += '<a class="aktiv" href="javascript: btoplistaMegjelenit('+i+')">'+i+'</a>';
            else lapok_html += '<a href="javascript: btoplistaMegjelenit('+i+')">'+i+'</a>';

            i++;
        }
    }

    //  3, 4, [5], 6, 7 ...
    else if (oldal >= 3 && oldal<=bgal_old-4) {
        for (i=oldal-2; i<=oldal+2; i++) {
            if (i == oldal) lapok_html += '<a class="aktiv" href="javascript: btoplistaMegjelenit('+i+')">'+i+'</a>';
            else lapok_html += '<a href="javascript: btoplistaMegjelenit('+i+')">'+i+'</a>';
        }
    }

    //  Utolsó 4 oldalon van
    else if (oldal > bgal_old-4) {
        for (i=bgal_old-4; i<=bgal_old; i++) {
            if (i>0) {
                if (i == oldal) lapok_html += '<a class="aktiv" href="javascript: btoplistaMegjelenit('+i+')">'+i+'</a>';
                else lapok_html += '<a href="javascript: btoplistaMegjelenit('+i+')">'+i+'</a>';
            }
        }
    }

    $('#lapok').html(lapok_html);
}
</script>
</head>

<body class="toplista">

    <div id="lista"></div>

    <a id="ism"></a>
    <div id="bal"></div>
    <div id="lapok"></div>
    <div id="jobb"></div>
    <a id="mindenki"></a>
</body>
</html>
