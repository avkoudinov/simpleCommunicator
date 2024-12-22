<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', -7);
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])))
{
  exit;
}
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
  
  $graph = new CanvasGraph(938,300);	
  $t1 = new Text($text);
  $t1->SetPos(0.5,0.5, "center", "center");
  
  $t1->SetFont(FF_VERDANA,FS_NORMAL,14);
  $t1->SetColor("#C51C20");
  $graph->AddText($t1);

  $graph->Stroke();
}

if (!empty($maintenance_until) && empty($_SESSION["admdebug"])) {
    gen_message_image(sprintf(text("MaintenanceComment"), $maintenance_until, $time_zone_name));
    exit;
}

$fmanager->track_hit("", "");

if(!$fmanager->gen_forum_daily_activity())
{
}

if(empty($_SESSION["forum_posts"]) && empty($_SESSION["forum_hits"])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!empty($_SESSION["forum_posts"]))
{
  $xposts = array_keys($_SESSION["forum_posts"]);
  $yposts = array_values($_SESSION["forum_posts"]);
  
  $_SESSION["forum_posts_avg"] = array();
  build_trendline($_SESSION["forum_posts"], $_SESSION["forum_posts_avg"]);
  
  if(empty($_SESSION["forum_hits"])) 
  {
    $xhits = array_keys($_SESSION["forum_posts"]);
    $yhits = array_fill(0,count($xhits),0);

    $x_bot_hits = $xhits;
    $y_bot_hits = $yhits;
  }
}

if(!empty($_SESSION["forum_hits"]))
{
  $xhits = array_keys($_SESSION["forum_hits"]);
  $yhits = array_values($_SESSION["forum_hits"]);

  $_SESSION["forum_hits_avg"] = array();
  build_trendline($_SESSION["forum_hits"], $_SESSION["forum_hits_avg"]);

  if(empty($_SESSION["forum_bot_hits"])) 
  {
    $x_bot_hits = $xhits;
    $y_bot_hits = $yhits;
  }

  if(empty($_SESSION["forum_posts"])) 
  {
    $xposts = array_keys($_SESSION["forum_hits"]);
    $yposts = array_fill(0,count($xposts),0);
  }
}

if(!empty($_SESSION["forum_bot_hits"]))
{
  $x_bot_hits = array_keys($_SESSION["forum_bot_hits"]);
  $y_bot_hits = array_values($_SESSION["forum_bot_hits"]);

  $_SESSION["forum_bot_hits_avg"] = array();
  build_trendline($_SESSION["forum_bot_hits"], $_SESSION["forum_bot_hits_avg"]);
}

if(!empty($_SESSION["forum_posts_avg"]))
{
  $xposts_avg = array_keys($_SESSION["forum_posts_avg"]);
  $yposts_avg = array_values($_SESSION["forum_posts_avg"]);
}

if(!empty($_SESSION["forum_hits_avg"]))
{
  $xhits_avg = array_keys($_SESSION["forum_hits_avg"]);
  $yhits_avg = array_values($_SESSION["forum_hits_avg"]);
}

if(!empty($_SESSION["forum_bot_hits_avg"]))
{
  $x_bot_hits_avg = array_keys($_SESSION["forum_bot_hits_avg"]);
  $y_bot_hits_avg = array_values($_SESSION["forum_bot_hits_avg"]);
}

$maxhits = max(round(1.1*max($yhits)),50);
$max_bot_hits = max(round(1.1*max($y_bot_hits)),50);
$maxposts = max(round(1.1*max($yposts)),50);

//-----------------------------------------------------
$hitsgraph = new Graph(938,290);

$hitsgraph->SetMargin(70,60,20,40);

$hitsgraph->SetScale('datlin',0,$maxhits);

$hitsgraph->SetY2Scale("lin",0,$maxhits);

$hitsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$hitsgraph->xaxis->HideLabels();

$label = utf8_to_nce(text("HitsPerDay"));
$hitsgraph->yaxis->title->Set($label . " (" . utf8_to_nce(text("Browsers")) . ")");
$hitsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->title->SetMargin(25,15,15,15);

$hitsgraph->xaxis->SetLabelAngle(50);
$hitsgraph->xscale->SetDateFormat(text("DateFormat"));

$plot = new LinePlot($yhits,$xhits);

$hitsgraph->Add($plot);

$plot->SetWeight("1"); 
$plot->SetStyle("solid"); 
$plot->SetColor("darkblue");

$plot->SetLegend($label);

if(!empty($_SESSION["forum_hits_avg"]))
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
$hitsgraph->legend->Pos(0.07,0.05,"left","bottom");
//-----------------------------------------------------
$bot_hitsgraph = new Graph(938,290);

$bot_hitsgraph->SetMargin(70,60,20,40);

$bot_hitsgraph->SetScale('datlin',0,$max_bot_hits);

$bot_hitsgraph->SetY2Scale("lin",0,$max_bot_hits);

$bot_hitsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$bot_hitsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$bot_hitsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$bot_hitsgraph->xaxis->HideLabels();

$label = utf8_to_nce(text("HitsPerDay"));
$bot_hitsgraph->yaxis->title->Set($label . " (" . utf8_to_nce(text("Bots")) . ")");
$bot_hitsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$bot_hitsgraph->yaxis->title->SetMargin(25,15,15,15);

$bot_hitsgraph->xaxis->SetLabelAngle(50);
$bot_hitsgraph->xscale->SetDateFormat(text("DateFormat"));

$plot = new LinePlot($y_bot_hits,$x_bot_hits);

$bot_hitsgraph->Add($plot);

$plot->SetWeight("1"); 
$plot->SetStyle("solid"); 
$plot->SetColor("darkgreen");

$plot->SetLegend($label);

if(!empty($_SESSION["forum_bot_hits_avg"]))
{
  $plot = new LinePlot($y_bot_hits_avg,$x_bot_hits_avg);

  $bot_hitsgraph->Add($plot);

  $plot->SetWeight("1"); 
  $plot->SetStyle("solid"); 
  $plot->SetColor("red");
  
  $label = utf8_to_nce(text("TrendLine"));
  $plot->SetLegend($label);
}

$bot_hitsgraph->legend->SetLayout(LEGEND_HOR);
$bot_hitsgraph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$bot_hitsgraph->legend->Pos(0.07,0.05,"left","bottom");
//-----------------------------------------------------
$postsgraph = new Graph(938,280);

$postsgraph->SetMargin(70,60,20,75);

$postsgraph->SetScale('datlin',0,$maxposts);

$postsgraph->SetY2Scale("lin",0,$maxposts);

$postsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("MessagesPerDay"));
$postsgraph->yaxis->title->Set($label);
$postsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->yaxis->title->SetMargin(25,15,15,15);

$postsgraph->xaxis->SetLabelAngle(50);
$postsgraph->xscale->SetDateFormat(text("DateFormat"));

$plot = new LinePlot($yposts,$xposts);

$postsgraph->Add($plot);

$plot->SetWeight("1"); 
$plot->SetStyle("solid"); 
$plot->SetColor("#C51C20");

$plot->SetLegend($label);

if(!empty($_SESSION["forum_posts_avg"]))
{
  $plot = new LinePlot($yposts_avg,$xposts_avg);

  $postsgraph->Add($plot);

  $plot->SetWeight("1"); 
  $plot->SetStyle("solid"); 
  $plot->SetColor("blue");

  $label = utf8_to_nce(text("TrendLine"));
  $plot->SetLegend($label);
}

$postsgraph->legend->SetLayout(LEGEND_HOR);
$postsgraph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->legend->Pos(0.07,0.05,"left","bottom");
//-----------------------------------------------------
$mgraph = new MGraph(938);
$mgraph->Add($hitsgraph,0,0);
$mgraph->Add($bot_hitsgraph,0,280);
$mgraph->Add($postsgraph,0,560);
//-----------------------------------------------------
header("Content-type: image/png");
header("Content-Disposition: inline; filename=\"forum_statistics.png\"");
//-----------------------------------------------------
$mgraph->Stroke();
//-----------------------------------------------------
?>