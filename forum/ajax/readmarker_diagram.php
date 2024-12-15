<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', -3);
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
//-----------------------------------------------------------------------

function time_format_callback($time) 
{
  return date("d", $time) . " " . month_name(date("n", $time), true) . ", " . date("H:i", $time);
}

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

if(!$fmanager->gen_load_statistics())
{
}

if(empty($_SESSION["forum_rm_count"]) || empty($_SESSION["topic_rm_count"]) ||
   empty($_SESSION["total_forum_rm_count"]) || empty($_SESSION["total_topic_rm_count"])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!empty($_SESSION["forum_rm_count"]))
{
  $x_forum_rm_count = array_keys($_SESSION["forum_rm_count"]);
  $y_forum_rm_count = array_values($_SESSION["forum_rm_count"]);
}

if(!empty($_SESSION["topic_rm_count"]))
{
  $x_topic_rm_count = array_keys($_SESSION["topic_rm_count"]);
  $y_topic_rm_count = array_values($_SESSION["topic_rm_count"]);
}

if(!empty($_SESSION["total_forum_rm_count"]))
{
  $x_total_forum_rm_count = array_keys($_SESSION["total_forum_rm_count"]);
  $y_total_forum_rm_count = array_values($_SESSION["total_forum_rm_count"]);
}

if(!empty($_SESSION["total_topic_rm_count"]))
{
  $x_total_topic_rm_count = array_keys($_SESSION["total_topic_rm_count"]);
  $y_total_topic_rm_count = array_values($_SESSION["total_topic_rm_count"]);
}

$max_forum_rm_count = max(round(1.2*max($y_forum_rm_count)),50);
$max_topic_rm_count = max(round(1.1*max($y_topic_rm_count)),50);

$max_total_forum_rm_count = max(round(1.2*max($y_total_forum_rm_count)),50);
$max_total_topic_rm_count = max(round(1.1*max($y_total_topic_rm_count)),50);

//-----------------------------------------------------
$read_marker_graph = new Graph(938,380);

$read_marker_graph->SetMargin(80,80,30,40);

$read_marker_graph->SetScale('datlin',0,$max_forum_rm_count);
$read_marker_graph->SetY2Scale("lin",0,$max_topic_rm_count);

$read_marker_graph->xaxis->SetLabelAngle(50);
$read_marker_graph->xaxis->HideLabels();
$read_marker_graph->xscale->SetDateFormat(text("DateFormat") . " H:i");

$read_marker_graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("ForumUniqueRMCount"));
$read_marker_graph->yaxis->title->Set($label);
$read_marker_graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->yaxis->title->SetMargin(35,15,15,15);

$line = new LinePlot($y_forum_rm_count, $x_forum_rm_count);
$read_marker_graph->Add($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#C51C20");
$label = utf8_to_nce(text("ForumUniqueRMCount"));
$line->SetLegend($label);

$label = utf8_to_nce(text("TopicUniqueRMCount"));
$read_marker_graph->y2axis->title->Set($label);
$read_marker_graph->y2axis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->y2axis->title->SetMargin(40,15,15,15);

$line = new LinePlot($y_topic_rm_count, $x_topic_rm_count);
$read_marker_graph->AddY2($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#14C814");
$label = utf8_to_nce(text("TopicUniqueRMCount"));
$line->SetLegend($label);

$read_marker_graph->legend->SetLayout(LEGEND_HOR);
$read_marker_graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->legend->Pos(0.08,0.06,"left","bottom");
//-----------------------------------------------------
$total_read_marker_graph = new Graph(938,380);

$total_read_marker_graph->SetMargin(80,80,30,85);

$total_read_marker_graph->SetScale('datlin',0,$max_total_forum_rm_count);
$total_read_marker_graph->SetY2Scale("lin",0,$max_total_topic_rm_count);

$total_read_marker_graph->xaxis->SetLabelAngle(50);
$total_read_marker_graph->xaxis->SetLabelFormatCallback('time_format_callback');

$total_read_marker_graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$total_read_marker_graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$total_read_marker_graph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("ForumTotalRMCount"));
$total_read_marker_graph->yaxis->title->Set($label);
$total_read_marker_graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$total_read_marker_graph->yaxis->title->SetMargin(35,15,15,15);
$total_read_marker_graph->xscale->SetDateFormat(text("DateFormat") . " H:i");

$line = new LinePlot($y_total_forum_rm_count, $x_total_forum_rm_count);
$total_read_marker_graph->Add($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#C51C20");
$label = utf8_to_nce(text("ForumTotalRMCount"));
$line->SetLegend($label);

$label = utf8_to_nce(text("TopicTotalRMCount"));
$total_read_marker_graph->y2axis->title->Set($label);
$total_read_marker_graph->y2axis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$total_read_marker_graph->y2axis->title->SetMargin(40,15,15,15);

$total_read_marker_graph->xscale->SetDateFormat(text("DateFormat") . " H:i");

$line = new LinePlot($y_total_topic_rm_count, $x_total_topic_rm_count);
$total_read_marker_graph->AddY2($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#14C814");
$label = utf8_to_nce(text("TopicTotalRMCount"));
$line->SetLegend($label);

$total_read_marker_graph->legend->SetLayout(LEGEND_HOR);
$total_read_marker_graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$total_read_marker_graph->legend->Pos(0.08,0.06,"left","bottom");
//-----------------------------------------------------
$mgraph = new MGraph(938);
$mgraph->Add($read_marker_graph,0,0);
$mgraph->Add($total_read_marker_graph,0,360);
//-----------------------------------------------------
header("Content-type: image/png");
header("Content-Disposition: inline; filename=\"load_statistics.png\"");
//-----------------------------------------------------
$mgraph->Stroke();
//-----------------------------------------------------
?>