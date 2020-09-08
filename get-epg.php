<?php

header("Content-type: text/xml;charset=utf-8");

function clear_string($string){

  $search  = array('"', '"', ';');
  $replace = array('&quot', '&quot', '');

  $string = str_replace($search, $replace, $string);
  return trim($string);
}

// Atribui o conteúdo do arquivo para variável $arquivo

$datetime = new DateTime();
$hoje = $datetime->format('Y-m-d');

$datetime = new DateTime('tomorrow');
$amanha = $datetime->format('Y-m-d');

$arquivo = file_get_contents("https://apim.oi.net.br/app/oiplay/ummex/v1/epg?starttime={$hoje}T03:00:00.000Z&endtime={$amanha}T02:59:59.999Z");

// Decodifica o formato JSON e retorna um Objeto
$json = json_decode($arquivo);

$channel_id = "MyChannelID";

/*$XMLdoc = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><tv generator-info-name=\"\" generator-info-url=\"\"></tv>");*/

$XMLdoc = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><tv></tv>");

// Loop para percorrer o Objeto
foreach($json as $canal):
  $channel = $XMLdoc->addChild("channel");
  $channel->addAttribute("id", clear_string($canal->title));
  $titleNode = $channel->addChild("display-name",clear_string($canal->title));
  $titleNode->addAttribute("lang", "pt");
  $iconNode = $channel->addChild("icon");
  $iconNode->addAttribute("src", $canal->positiveLogoUrl);
endforeach;

// Loop para percorrer o Objeto
foreach($json as $canal):

  foreach ($canal->schedules as $programacao):

    $programme = $XMLdoc->addChild("programme");

    $programme->addAttribute("start", date_format(date_create($programacao->startTimeUtc), 'YmdHis +0000'));

    $date = date_create($programacao->startTimeUtc);
    $duracao = 'PT'.$programacao->durationSeconds.'S';
    $stopTimeUtc = $date->add(new DateInterval($duracao));

    $programme->addAttribute("stop", date_format($stopTimeUtc, 'YmdHis +0000'));

    $programme->addAttribute("channel", clear_string($canal->title));

    $titleNode = $programme->addChild("title", clear_string($programacao->program->seriesTitle));

    $titleNode->addAttribute("lang", "pt");

    $descNode = $programme->addChild("desc", clear_string($programacao->program->synopsis));

    $descNode->addAttribute("lang", "pt");

    $categoryNode = $programme->addChild("category", clear_string($programacao->program->genres));

    $categoryNode->addAttribute("lang", "pt");

  endforeach;
endforeach;


echo str_replace(">",">\n",$XMLdoc->asXML());

?>
