<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
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
  
  $graph = new CanvasGraph(598,200);	
  $t1 = new Text($text);
  $t1->SetPos(0.5,0.5, "center", "center");
  
  $t1->SetFont(FF_VERDANA,FS_NORMAL,14);
  $t1->SetColor("#C51C20");
  $graph->AddText($t1);

  $graph->Stroke();
}

if (reqvar("type") == "hourly") {
    if(!$fmanager->gen_user_hourly_activity(reqvar("uid")))
    {
        gen_message_immage(text("NoData"));
        exit;
    }
} elseif (reqvar("type") == "weekday") {
    if(!$fmanager->gen_user_weekday_activity(reqvar("uid")))
    {
        gen_message_immage(text("NoData"));
        exit;
    }
} else {
    if(!$fmanager->gen_user_daily_activity(reqvar("uid")))
    {
        gen_message_immage(text("NoData"));
        exit;
    }
}

if(empty($_SESSION["user_posts"]) && empty($_SESSION["user_hits"])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!empty($_SESSION["user_posts"]))
{
  $xposts = array_keys($_SESSION["user_posts"]);
  $yposts = array_values($_SESSION["user_posts"]);
  
  $_SESSION["user_posts_avg"] = array();
  if(reqvar("type") == "daily") build_trendline($_SESSION["user_posts"], $_SESSION["user_posts_avg"]);

  if(empty($_SESSION["user_hits"])) 
  {
    $xhits = array_keys($_SESSION["user_posts"]);
    $yhits = array_fill(0,count($xhits),0);
  }
}

if(!empty($_SESSION["user_hits"]))
{
  $xhits = array_keys($_SESSION["user_hits"]);
  $yhits = array_values($_SESSION["user_hits"]);

  $_SESSION["user_hits_avg"] = array();
  if(reqvar("type") == "daily") build_trendline($_SESSION["user_hits"], $_SESSION["user_hits_avg"]);

  if(empty($_SESSION["user_posts"])) 
  {
    $xposts = array_keys($_SESSION["user_hits"]);
    $yposts = array_fill(0,count($xposts),0);
  }
}

if(!empty($_SESSION["user_posts_avg"]))
{
  $xposts_avg = array_keys($_SESSION["user_posts_avg"]);
  $yposts_avg = array_values($_SESSION["user_posts_avg"]);
}

if(!empty($_SESSION["user_hits_avg"]))
{
  $xhits_avg = array_keys($_SESSION["user_hits_avg"]);
  $yhits_avg = array_values($_SESSION["user_hits_avg"]);
}

$maxhits = max(round(1.1*max($yhits)),15);
$maxposts = max(round(1.1*max($yposts)),15);

//-----------------------------------------------------
$hitsgraph = new Graph(598,240);

$hitsgraph->SetMargin(70,50,20,20);

if (reqvar("type") == "daily") {
    $hitsgraph->SetScale('datlin',0,$maxhits);
} elseif (reqvar("type") == "hourly") {
    $hitsgraph->SetScale('lin',0,$maxhits);
} else {
    $hitsgraph->SetScale('textlin',0,$maxhits);
}

$hitsgraph->SetY2Scale("lin",0,$maxhits);

$hitsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

if (reqvar("type") == "daily") $hitsgraph->xaxis->HideLabels();

$label = utf8_to_nce(reqvar("type") == "hourly" ? text("HitsPerHour") : text("HitsPerDay"));
$hitsgraph->yaxis->title->Set($label);
$hitsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->title->SetMargin(25,15,15,15);

if (reqvar("type") == "weekday") {
    $hitsgraph->xaxis->SetTickLabels($xhits);

    $plot = new BarPlot($yhits);
    $hitsgraph->Add($plot);

    $plot->SetColor("darkblue@0.7");
    $plot->SetFillColor('darkblue@0.7');
} else {  
    if(reqvar("type") == "daily") {
        $hitsgraph->xaxis->SetLabelAngle(50);
        $hitsgraph->xscale->SetDateFormat(text("DateFormat"));
    }

    $plot = new LinePlot($yhits,$xhits);

    $hitsgraph->Add($plot);

    $plot->SetWeight("1"); 
    $plot->SetStyle("solid"); 
    $plot->SetColor("darkblue");

    if(reqvar("type") == "daily") $plot->SetLegend($label);

    if(!empty($_SESSION["user_hits_avg"]) && reqvar("type") == "daily")
    {
      $plot = new LinePlot($yhits_avg,$xhits_avg);

      $hitsgraph->Add($plot);

      $plot->SetWeight("1"); 
      $plot->SetStyle("solid"); 
      $plot->SetColor("red");
      
      $label = utf8_to_nce(text("TrendLine"));
      $plot->SetLegend($label);
    }
} 

$hitsgraph->legend->SetLayout(LEGEND_HOR);
$hitsgraph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->legend->Pos(0.11,0.07,"left","bottom");
//-----------------------------------------------------
$postsgraph = new Graph(598,290);

$postsgraph->SetMargin(70,50,20,75);

if (reqvar("type") == "daily") {
    $postsgraph->SetScale('datlin',0,$maxposts);
} elseif (reqvar("type") == "hourly") {
    $postsgraph->SetScale('lin',0,$maxposts);
} else {
    $postsgraph->SetScale('textlin',0,$maxposts);
}

$postsgraph->SetY2Scale("lin",0,$maxposts);

$postsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(reqvar("type") == "hourly" ? text("MessagesPerHour") : text("MessagesPerDay"));
$postsgraph->yaxis->title->Set($label);
$postsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->yaxis->title->SetMargin(25,15,15,15);

if (reqvar("type") == "weekday") {
    $postsgraph->xaxis->SetTickLabels($xposts);

    $plot = new BarPlot($yposts);
    $postsgraph->Add($plot);

    $plot->SetColor("#C51C20@0.7");
    $plot->SetFillColor("#C51C20@0.7");
} else {  
    if(reqvar("type") == "daily") {
       $postsgraph->xaxis->SetLabelAngle(50);
       $postsgraph->xscale->SetDateFormat(text("DateFormat"));
    }

    $plot = new LinePlot($yposts,$xposts);

    $postsgraph->Add($plot);

    $plot->SetWeight("1"); 
    $plot->SetStyle("solid"); 
    $plot->SetColor("#C51C20");

    if(reqvar("type") == "daily") $plot->SetLegend($label);

    if(!empty($_SESSION["user_posts_avg"]) && reqvar("type") == "daily")
    {
      $plot = new LinePlot($yposts_avg,$xposts_avg);

      $postsgraph->Add($plot);

      $plot->SetWeight("1"); 
      $plot->SetStyle("solid"); 
      $plot->SetColor("blue");

      $label = utf8_to_nce(text("TrendLine"));
      $plot->SetLegend($label);
    }
}

$postsgraph->legend->SetLayout(LEGEND_HOR);
$postsgraph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->legend->Pos(0.11,0.06,"left","bottom");
//-----------------------------------------------------
$mgraph = new MGraph(598);
$mgraph->Add($hitsgraph,0,0);
$mgraph->Add($postsgraph,0,240);
//-----------------------------------------------------
header("Content-type: image/png");
header("Content-Disposition: inline; filename=\"user_statistics.png\"");
//-----------------------------------------------------
$mgraph->Stroke();
//-----------------------------------------------------
?>