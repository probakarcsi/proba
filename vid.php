<?
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
session_start();


function generateRequestUrl($path, $timeOffset) {
	$secret = 'phoo6Kahph0auheC';
	$time = time() + $timeOffset;
	$replacements = array(
		'+' => '-',
		'/' => '_',
		'=' => ''
	);
	
	$hash = strtr(base64_encode(md5($secret.$path.$time, TRUE)), $replacements);
	//$requestUrl = $path ."?". rawurlencode(sprintf ("st=%s&e=%d", $hash, $time));
	$requestUrl = $path ."?". sprintf ("st=%s&e=%d", $hash, $time);
	return $requestUrl;
}




$index = $_SESSION["index"];
$dal_sorszam = $_SESSION["dalok"][$index];

$adatok = $ossz_eloado = $ossz_cim = array();


$fajl = file('videok.txt');

$i = 0;
foreach ($fajl as $sor) {
    $sor = explode("\t", $sor);

    $list_eloado = trim($sor[0]);
    $list_cim = trim($sor[1]);
    $list_shortkod = trim($sor[2]);
    $list_ido = trim($sor[3]);

    if (!in_array($list_eloado, $ossz_eloado)) $ossz_eloado[] = $list_eloado;
    if (!in_array($list_cim, $ossz_cim)) $ossz_cim[] = $list_cim;
	
	if ($i == $dal_sorszam) {
		$eloado = $list_eloado;
		$cim = $list_cim;
		$shortkod = $list_shortkod;
		$start = $list_ido;
	}
	
    $i++;
}



$sk = str_replace("id_", "", $shortkod);
$surl = "http://stream2.tv2.hu/vod2/$sk.phone_h264_800k.mp4";  // mobil stream

$utags = parse_url($surl);
$e_base = $utags["scheme"]."://".$utags["host"];
$e_path = $utags["path"];
$video_url = $e_base . generateRequestUrl($e_path, 43200);



//  Legenerálja a 4 választható egyedi címet vagy előadót
$valaszok = array();
$random_szam = rand(0,1);

if ($random_szam == 0)
{
	//  Előadók
	$tippek = array_diff($ossz_eloado, array($eloado));
	$rand_ind = array_rand($tippek, 3);
	
	foreach ($rand_ind as $i) $valaszok[] = $tippek[$i]; //  a 3 rossz válasz
	$valaszok[] = $eloado;  // a jó válasz
}
else
{
	//  Címek
	$tippek = array_diff($ossz_cim, array($cim));
	$rand_ind = array_rand($tippek, 3);

	foreach ($rand_ind as $i)  $valaszok[] = $tippek[$i]; //  a 3 rossz válasz
	$valaszok[] = $cim;  // a jó válasz
}


?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" type="text/css" href="style.css?v=2016-02-27" media="all"/>
	<script type="text/javascript" src="lib/jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="lib/samblink.js"></script>
</head>


<body class="vid">
    
<table id="valasz1"><tr><td style="vertical-align:middle"><?=$valaszok[0]?></td></tr></table>
<table id="valasz2"><tr><td style="vertical-align:middle"><?=$valaszok[1]?></td></tr></table>
<table id="valasz3"><tr><td style="vertical-align:middle"><?=$valaszok[2]?></td></tr></table>
<table id="valasz4"><tr><td style="vertical-align:middle"><?=$valaszok[3]?></td></tr></table>

<video width="482" height="266" autoplay>
	<source src="<?=$video_url?>" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"'></source>
</video>

<div class="load"><img src="img/load.gif"/></div>

<div class="mask"></div>

<div class="vol"></div>
<div id="plusz"></div>
<div id="minusz"></div>


<script>
var pont = parseInt($('#pont', window.parent.document).html());

$('.potty', window.parent.document).css('background', 'url(img/potty.png) no-repeat');
$('#potty'+<?=($index+1)?>, window.parent.document).css('background', 'url(img/potty_aktiv.png) no-repeat');
$('#plusz').css('opacity', 0.5);


var video = document.getElementsByTagName("video")[0];
video.addEventListener("timeupdate", timeUpdate, false);
video.addEventListener("loadedmetadata", function(){ video.currentTime = <?=$start?>; }, false);

var szamlalo = 0;

// Megy a lejátszás
function timeUpdate(){
	var eltelt_ido = Math.floor(video.currentTime - <?=$start?>);

	szamlalo = 60 - eltelt_ido;
	$('#ido', window.parent.document).html(szamlalo);

	if (szamlalo == 59) {
        $("table").fadeIn();
		$('.load').hide();
    }

	// 10 alatt villogva mutatja az időt
    else if (szamlalo == 10) {
        $('#ido', window.parent.document).blink();
    }

	// Letelt az idő
	else if (szamlalo <= 0) {
        $('#ido', window.parent.document).unblink();
		kovetkezoDal(<?=($index+2)?>);		
	}
}



// Hangerő szabályozása
var hangero = 1;

$('#minusz').click(function(){
	if (hangero > 0.11) hangero -= 0.1;
	video.volume = hangero;

	if (hangero <= 0.2) $('#minusz').css('opacity', 0.5);
	else $('#minusz').css('opacity', 1);

	$('#plusz').css('opacity', 1);
});


$('#plusz').click(function(){
	if (hangero < 1) hangero += 0.1;
	video.volume = hangero;
	
	if (hangero >= 1) $('#plusz').css('opacity', 0.5);
	else $('#plusz').css('opacity', 1);
	
	$('#minusz').css('opacity', 1);
});


// Választ
var jo_valasz = "<?=($random_szam == 0) ? addslashes($eloado) : addslashes($cim) ?>";
jo_valasz = jo_valasz.replace("&", "");

$('table').click(function(){
	var tipp = $(this).find("tr td").html().replace("&amp;", "");
	
	video.removeEventListener("timeupdate", timeUpdate, false);
	
	if (tipp == jo_valasz) {
		pont += szamlalo;
		$('#pont', window.parent.document).html(pont);
		$('#pont', window.parent.document).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);

		$('#resp', window.parent.document).html('<img src="img/ok.png">');
		setTimeout(function(){ $('#resp', window.parent.document).html(''); kovetkezoDal(<?=($index+2)?>); },10000);
		
		$('table').hide();
		$('.mask').animate({opacity: 0}, {duration: 1500});
		$('.load').remove();
	}

	else {
		$('#resp', window.parent.document).html('<img src="img/error.png">');
		$('table').unbind('click');
		setTimeout(function(){ $('#resp', window.parent.document).html(''); kovetkezoDal(<?=($index+2)?>); },2000);
	}

});
	


// Következő dal
function kovetkezoDal(dal) {
	video.pause();

	if (dal >= 6) {
		pont = parseInt($('#pont', window.parent.document).html());

		$.ajax({ 
			type: "POST",
			url: "lib/ajax.php",
			data: { pont: pont },
			success: function(msg) {
				window.parent.document.location = 'befejezo.php';
			}
		});
	}
	else {
		$('#ido', window.parent.document).html('');
		window.parent.document.getElementById('if').src += '';
	}
}
</script>

<? $_SESSION["index"]++; ?>

</body>
</html>
