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
$fmanager->track_hit("", "");
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

if(empty($_SESSION["exec_times"]) || empty($_SESSION["load_hits"])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!empty($_SESSION["load_hits"]))
{
  $x_hits = array_keys($_SESSION["load_hits"]);
  $y_hits = array_values($_SESSION["load_hits"]);
}

if(!empty($_SESSION["exec_times"]))
{
  $x_exec_times = array_keys($_SESSION["exec_times"]);
  $y_exec_times = array_values($_SESSION["exec_times"]);
}

if(!empty($_SESSION["exec_hits"]))
{
  $x_exec_hits = array_keys($_SESSION["exec_hits"]);
  $y_exec_hits = array_values($_SESSION["exec_hits"]);
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

$max_hits = max(round(1.2*max($y_hits)),50);

$max_exec_times = max(round(1.2*max($y_exec_times)),50);
$max_exec_hits = max(round(1.1*max($y_exec_hits)),50);

$max_forum_rm_count = max(round(1.2*max($y_forum_rm_count)),50);
$max_topic_rm_count = max(round(1.1*max($y_topic_rm_count)),50);

//-----------------------------------------------------
$exec_times_graph = new Graph(938,320);

$exec_times_graph->SetMargin(80,80,20,40);

$exec_times_graph->SetScale('datlin',0,$max_exec_times);
$exec_times_graph->SetY2Scale("lin",0,$max_exec_hits);

$exec_times_graph->xaxis->SetLabelAngle(50);
$exec_times_graph->xaxis->HideLabels();
$exec_times_graph->xscale->SetDateFormat(text("DateFormat") . " H:i");

$exec_times_graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$exec_times_graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$exec_times_graph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("QueryExecTime"));
$exec_times_graph->yaxis->title->Set($label);
$exec_times_graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$exec_times_graph->yaxis->title->SetMargin(35,15,15,15);

$line = new LinePlot($y_exec_times, $x_exec_times);
$exec_times_graph->Add($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#C51C20");
$label = utf8_to_nce(text("QueryExecTime"));
$line->SetLegend($label);

$label = utf8_to_nce(text("QueryExecCount"));
$exec_times_graph->y2axis->title->Set($label);
$exec_times_graph->y2axis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$exec_times_graph->y2axis->title->SetMargin(40,15,15,15);

$line = new LinePlot($y_exec_hits, $x_exec_hits);
$exec_times_graph->AddY2($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#14C814");
$label = utf8_to_nce(text("QueryExecCount"));
$line->SetLegend($label);

$exec_times_graph->legend->SetLayout(LEGEND_HOR);
$exec_times_graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$exec_times_graph->legend->Pos(0.08,0.06,"left","bottom");
//-----------------------------------------------------
$read_marker_graph = new Graph(938,320);

$read_marker_graph->SetMargin(80,80,20,40);

$read_marker_graph->SetScale('datlin',0,$max_forum_rm_count);
$read_marker_graph->SetY2Scale("lin",0,$max_topic_rm_count);

$read_marker_graph->xaxis->SetLabelAngle(50);
$read_marker_graph->xaxis->HideLabels();
$read_marker_graph->xscale->SetDateFormat(text("DateFormat") . " H:i");

$read_marker_graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("ForumRMCount"));
$read_marker_graph->yaxis->title->Set($label);
$read_marker_graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->yaxis->title->SetMargin(35,15,15,15);

$line = new LinePlot($y_forum_rm_count, $x_forum_rm_count);
$read_marker_graph->Add($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#C51C20");
$label = utf8_to_nce(text("ForumRMCount"));
$line->SetLegend($label);

$label = utf8_to_nce(text("TopicRMCount"));
$read_marker_graph->y2axis->title->Set($label);
$read_marker_graph->y2axis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->y2axis->title->SetMargin(40,15,15,15);

$line = new LinePlot($y_topic_rm_count, $x_topic_rm_count);
$read_marker_graph->AddY2($line);
$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("#14C814");
$label = utf8_to_nce(text("TopicRMCount"));
$line->SetLegend($label);

$read_marker_graph->legend->SetLayout(LEGEND_HOR);
$read_marker_graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$read_marker_graph->legend->Pos(0.08,0.06,"left","bottom");
//-----------------------------------------------------
$hits_graph = new Graph(938,260);

$hits_graph->SetMargin(80,80,20,85);

$hits_graph->SetScale('datlin',0,$max_hits);
$hits_graph->SetY2Scale("lin",0,$max_hits);

$hits_graph->xaxis->SetLabelAngle(50);
$hits_graph->xaxis->SetLabelFormatCallback('time_format_callback');

$hits_graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hits_graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,8);
$hits_graph->y2axis->SetFont(FF_VERDANA,FS_NORMAL,8);

$label = utf8_to_nce(text("HitsPerMinute"));
$hits_graph->yaxis->title->Set($label);
$hits_graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$hits_graph->yaxis->title->SetMargin(40,15,15,15);
$hits_graph->xscale->SetDateFormat(text("DateFormat") . " H:i");

$line = new LinePlot($y_hits,$x_hits);

$hits_graph->Add($line);

$line->SetWeight("1"); 
$line->SetStyle("solid"); 
$line->SetColor("darkblue");

$label = utf8_to_nce(text("HitsPerMinute"));
$line->SetLegend($label);

$hits_graph->legend->SetLayout(LEGEND_HOR);
$hits_graph->legend->SetFont(FF_VERDANA,FS_NORMAL,8);
$hits_graph->legend->Pos(0.08,0.06,"left","bottom");
//-----------------------------------------------------
$mgraph = new MGraph(938);
$mgraph->Add($exec_times_graph,0,0);
$mgraph->Add($read_marker_graph,0,300);
$mgraph->Add($hits_graph,0,600);
//-----------------------------------------------------
header("Content-type: image/png");
header("Content-Disposition: inline; filename=\"load_statistics.png\"");
//-----------------------------------------------------
$mgraph->Stroke();
//-----------------------------------------------------
?>