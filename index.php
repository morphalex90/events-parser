<?php
$output = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>London Events</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>';


// https://www.allinlondon.co.uk/rss/
$rsss = [
	'https://www.allinlondon.co.uk/rss/feeds/whatson-music.xml',
	'https://www.allinlondon.co.uk/rss/feeds/whatson-art.xml',
	'https://www.allinlondon.co.uk/rss/feeds/whatson-nights.xml',
	'https://www.allinlondon.co.uk/rss/feeds/whatson-exhibition.xml'
];

$emails = [
	'piero.nanni@gmail.com'
];

$risultati = array();
$countEventi = 0;	


foreach($rsss as $key => $rss){
	

	$fileXml = file_get_contents($rss);
	
	$convertito = utf8_decode($fileXml);
	$xml = new SimpleXMLElement($convertito);

	$risultati[$key] = array(
		'title' => htmlspecialchars(str_replace(' this month - All In London','',$xml->channel->title)),
		'description' => htmlspecialchars($xml->channel->description),
	);

	foreach($xml->channel->item as $evento){
		
		preg_match('#\((.*?)\)#', $evento->title, $date);
		#$date = array_reverse($date);

		
		$eventoTitolo = str_replace(' '.$date[0],'',$evento->title);
		$eventoDesc = htmlspecialchars($evento->description);

		$dateSplit = explode(' to ', $date[1]);
		$dateFrom = strtotime($dateSplit[0]);
		$dateTo = isset($dateSplit[1]) ? strtotime($dateSplit[1]) : '';
		
		
		#$output.= 'evento date: da '.date('d-m-Y',$dateFrom).($dateTo!='' ? ' a '.date('d-m-Y',$dateTo) : '').'.<br><br>';
		
		if(($dateTo=='' && $dateFrom>(date('U')-86400)) || ($dateTo!='' && $dateTo>date('U'))){
			$risultati[$key]['eventi'][] = array(
				'title' => $eventoTitolo,
				'description' => $eventoDesc,
				'link' => $evento->link,
				'from' => $dateFrom,
				'to' => $dateTo
			);
			$countEventi++;
		}
			
	}

}


$menu = '<ul class="nav nav-tabs" style="position:fixed; background:white; width:100%; z-index:10;">';
$tab = '<div class="tab-content" style="margin-top:42px;">';

foreach($risultati as $key => $risultato){
	
	$menu .= '<li'.($key==0 ? ' class="active"' : '').'><a data-toggle="tab" href="#tab'.$key.'">'.$risultato['title'].' ('.count($risultato['eventi']).')</a></li>';

	$tab .= '<div id="tab'.$key.'" class="tab-pane fade'.($key==0 ? ' in active' : '').'">';
	
	foreach($risultato['eventi'] as $evento){
		
		$tab .= '<div class="evento well'.($evento['to']=='' && date('d-m-Y',$evento['from'])==date('d-m-Y') ? ' today' : ' not_today').'">';
		$tab .= '<div class="row">';
		$tab .= '<div class="col-sm-2">';
		
			$tab .= 'from: ' . date('d-m-Y',$evento['from']).'<br>';
			
			if($evento['to']!='')
				$tab .= 'to: ' . date('d-m-Y',$evento['to']).'<br>';
			
		$tab .= '</div>'; // chiudo col-sm-2
		
		$tab .= '<div class="col-sm-10">';
		
		
		$tab .= '<h4 style="margin-top:0"><a href="'.$evento['link'].'" target="_blank">' . $evento['title'].'</a></h4>';
		$tab .= '<h5 style="margin-bottom:0">'. $evento['description'].'</h5>';
		
		$tab .= '</div>'; // chiudo col-sm-10
		
		
		$tab .= '</div>'; // chiudo row
		$tab .= '</div>'; // chiudo well
		
		#file_get_contents($evento['link']);
	}
	
	$tab .= '</div>';
	

}

$menu .= '<li style="float:right;"><a href="#" class="mostraOggi" data-activated="0">Show only today</a></li>';
$menu .= '</ul>';
$tab .= '</div>';


$output .= '<div class="container-fluid">';
$output .= '<div class="row">';
$output .= '<div class="col-sm-12">';
$output .= $menu;
$output .= $tab;
$output .= '</div>';
$output .= '</div>';
$output .= '</div>';


$output.= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script>
jQuery(document).ready(function(){
	jQuery(".mostraOggi").click(function(event){
		event.preventDefault();
		
		if(jQuery(this).data("activated") == 0){
			jQuery(".not_today").hide();
			jQuery(".mostraOggi").html("Show all");
			jQuery(this).data("activated",1);
		} else {
			jQuery(".not_today").show();
			jQuery(".mostraOggi").html("Show only today");
			jQuery(this).data("activated",0);
		}
	});
});
</script>
</body>
</html>';

echo $output;
?>