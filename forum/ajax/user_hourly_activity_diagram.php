<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
define('STATISTICS_REQUEST', -12);
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

if(!$fmanager->gen_user_hourly_activity(reqvar("uid")))
{
    gen_message_image(text("NoData"));
    exit;
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

  if(empty($_SESSION["user_posts"])) 
  {
    $xposts = array_keys($_SESSION["user_hits"]);
    $yposts = array_fill(0,count($xposts),0);
  }
}

$maxhits = max(round(1.1*max($yhits)),15);
$maxposts = max(round(1.1*max($yposts)),15);

//-----------------------------------------------------
$hitsgraph = new Graph(598,240);

$hitsgraph->SetMargin(70,50,20,20);

$hitsgraph->SetScale('lin',0,$maxhits);

$hitsgraph->SetY2Scale("lin",0,$maxhits);

$hitsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("HitsPerHour"));
$hitsgraph->yaxis->title->Set($label);
$hitsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->yaxis->title->SetMargin(25,15,15,15);

$plot = new LinePlot($yhits,$xhits);

$hitsgraph->Add($plot);

$plot->SetWeight("1"); 
$plot->SetStyle("solid"); 
$plot->SetColor("darkblue");

$hitsgraph->legend->SetLayout(LEGEND_HOR);
$hitsgraph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$hitsgraph->legend->Pos(0.11,0.07,"left","bottom");
//-----------------------------------------------------
$postsgraph = new Graph(598,290);

$postsgraph->SetMargin(70,50,20,75);

$postsgraph->SetScale('lin',0,$maxposts);

$postsgraph->SetY2Scale("lin",0,$maxposts);

$postsgraph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("MessagesPerHour"));
$postsgraph->yaxis->title->Set($label);
$postsgraph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$postsgraph->yaxis->title->SetMargin(25,15,15,15);

$plot = new LinePlot($yposts,$xposts);

$postsgraph->Add($plot);

$plot->SetWeight("1"); 
$plot->SetStyle("solid"); 
$plot->SetColor("#C51C20");

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