<?php
date_default_timezone_set('America/Bogota');

$url = getenv('URL_API');

if (!$url) {
    die("Error: URL_API no está definida en Railway");
};

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$html = curl_exec($ch);

libxml_use_internal_errors(true);
$dom = new DOMDocument();
@$dom->loadHTML($html);
$text = $dom->textContent;

// Extracción robusta por fila
$patron = '/(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\s([A-Za-z]{3})\s(\d{1,2})' .
          '\s*((?:Colombia\s*v\s*(?:(?!TBD|\d{1,2}:\d)[\w\s])+|(?:(?!TBD|\d{1,2}:\d)[\w ])+?\s*v\s*Colombia|Colombiav(?:(?!TBD|\d{1,2}:\d)[\w\s])+))' .
          '\s*(TBD|\d{1,2}:\d{2}\s*(?:AM|PM))' .
          '\s*(?:[A-Za-zÀ-ÿ0-9][A-Za-zÀ-ÿ0-9\s\/&\-\.\']*?)' . // Competición
          '\s*((?:ESPN[\w\s\/]*|FS\d|FOX|NBC|Univision|TUDN|beIN[\w\s]*)?)' . // TV
          '(?=(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*,|\z)/s';


$patron = '/(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\s([A-Za-z]{3})\s(\d{1,2})' .
          '\s*((?:Colombia\s*v\s*(?:(?!TBD|\d{1,2}:\d)[\w\s])+|(?:(?!TBD|\d{1,2}:\d)[\w ])+?\s*v\s*Colombia|Colombiav(?:(?!TBD|\d{1,2}:\d)[\w\s])+))' .
          '\s*(TBD|\d{1,2}:\d{2}\s*(?:AM|PM))' .
          '\s*([A-Za-zÀ-ÿ0-9][A-Za-zÀ-ÿ0-9\s\/&\-\.\']*?)' .
          '\s*((?:ESPN[\w\s\/]*|FS\d|FOX|NBC|Univision|TUDN|beIN[\w\s]*)?)' .
          '(?=(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*,|\z)/s';

preg_match_all($patron, $text, $matches, PREG_SET_ORDER);

$meses = [
    "Ene"=>1,"Feb"=>2,"Mar"=>3,"Abr"=>4,"May"=>5,"Jun"=>6,
    "Jul"=>7,"Ago"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dic"=>12
];

$year    = date("Y");
$partidos = [];

foreach ($matches as $m) {
    $mes     = $m[1];
    $dia     = $m[2];

    // limpiar nombre partido
    $partido = $m[3];
    // arreglar "v"
    $partido = preg_replace('/\s*v\s*/i', ' vs ', $partido);

    // separar palabras pegadas
    $partido = preg_replace('/([a-z])([A-Z])/', '$1 $2', $partido);

    // quitar números basura
    $partido = preg_replace('/\d+$/', '', $partido);

    // limpiar espacios
    $partido = trim(preg_replace('/\s+/', ' ', $partido));

    $horaRaw = trim($m[4]);

    if ($horaRaw === 'TBD') {
        $hora = 'TBD';
    } else {
        preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/', $horaRaw, $h);

        if ($h) {
            $horaObj = DateTime::createFromFormat('g:i A', "{$h[1]}:{$h[2]} {$h[3]}");
            $hora = $horaObj ? $horaObj->format('H:i') : 'TBD';
        } else {
            $hora = 'TBD';
        }
    }
    $competicion = preg_replace('/\d+$/', '', trim($m[5]));
    $tv          = trim($m[6]);
    $tv .= '/Caracol';

    $fecha = DateTime::createFromFormat('Y-n-j', "$year-{$meses[$mes]}-$dia");
    if ($fecha < new DateTime()) {
        $fecha->modify('+1 year');
    }

    $partidos[] = [
        "fecha"       => $fecha,
        "partido"     => $partido,
        "hora"        => $hora,
        "competicion" => $competicion,
        "tv"          => $tv,
    ];
}

usort($partidos, fn($a, $b) => $a["fecha"] <=> $b["fecha"]);

$proximo = $partidos[0] ?? null;
$hoy     = new DateTime();
$dias    = $proximo ? $hoy->diff($proximo["fecha"])->days : 0;

$meses_es = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',
             7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

$dias_es = [
  'Sunday' => 'Domingo',
  'Monday' => 'Lunes',
  'Tuesday' => 'Martes',
  'Wednesday' => 'Miércoles',
  'Thursday' => 'Jueves',
  'Friday' => 'Viernes',
  'Saturday' => 'Sábado',
];
