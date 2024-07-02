<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', -1);
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])))
{
  exit;
}
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once(APPLICATION_ROOT . "jpgraph/jpgraph.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_line.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_date.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_mgraph.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_canvas.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_bar.php");
//-----------------------------------------------------------------------

function gen_message_image($text)
{
  $text = utf8_to_nce(val_or_empty($text));
  
  $graph = new CanvasGraph(598,200);	
  $t1 = new Text($text);
  $t1->SetPos(0.5,0.5, "center", "center");
  
  $t1->SetFont(FF_VERDANA,FS_NORMAL,14);
  $t1->SetColor("#C51C20");
  $graph->AddText($t1);

  $graph->Stroke();
}

if(!$fmanager->gen_bot_daily_activity(reqvar("bot")))
{
    gen_message_immage(text("NoData"));
    exit;
}

if(empty($_SESSION["bot_hits"])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!empty($_SESSION["bot_hits"]))
{
  $xhits = array_keys($_SESSION["bot_hits"]);
  $yhits = array_values($_SESSION["bot_hits"]);

  $_SESSION["bot_hits_avg"] = array();
  build_trendline($_SESSION["bot_hits"], $_SESSION["bot_hits_avg"]);
}

if(!empty($_SESSION["bot_hits_avg"]))
{
  $xhits_avg = array_keys($_SESSION["bot_hits_avg"]);
  $yhits_avg = array_values($_SESSION["bot_hits_avg"]);
}

$maxhits = max(round(1.1*max($yhits)),15);

//-----------------------------------------------------
$hitsgraph = new Graph(598,290);

$hitsgraph->SetMargin(70,50,20,75);

$hitsgraph->SetScale('datlin',0,$maxhits);

$hitsgraph->SetY2Scale("lin",0,$maxhits);

$hitsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("HitsPerDay"));
$hitsgraph->yaxis->title->Set($label);
$hitsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->title->SetMargin(25,15,15,15);

$hitsgraph->xaxis->SetLabelAngle(50);
$hitsgraph->xscale->SetDateFormat(text("DateFormat"));

$plot = new LinePlot($yhits,$xhits);

$hitsgraph->Add($plot);

$plot->SetWeight("1"); 
$plot->SetStyle("solid"); 
$plot->SetColor("darkgreen");

$plot->SetLegend($label);

if(!empty($_SESSION["bot_hits_avg"]))
{
  $plot = new LinePlot($yhits_avg,$xhits_avg);

  $hitsgraph->Add($plot);

  $plot->SetWeight("1"); 
  $plot->SetStyle("solid"); 
  $plot->SetColor("red");
  
  $label = utf8_to_nce(text("TrendLine"));
  $plot->SetLegend($label);
}

$hitsgraph->legend->SetLayout(LEGEND_HOR);
$hitsgraph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->legend->Pos(0.11,0.06,"left","bottom");
//-----------------------------------------------------
$mgraph = new MGraph(598);
$mgraph->Add($hitsgraph,0,0);
//-----------------------------------------------------
header("Content-type: image/png");
header("Content-Disposition: inline; filename=\"user_statistics.png\"");
//-----------------------------------------------------
$mgraph->Stroke();
//-----------------------------------------------------
?>