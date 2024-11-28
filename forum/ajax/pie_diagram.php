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
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_pie.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_pie3d.php");
require_once(APPLICATION_ROOT . "jpgraph/jpgraph_canvas.php");
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

if(reqvar_empty("report1") || empty($_SESSION[reqvar("report1")])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!reqvar_empty("report2") && empty($_SESSION[reqvar("report1")][reqvar("report2")])) 
{
  gen_message_image(text("NoData"));
  exit;
}

if(!reqvar_empty("report2"))
{
  $report_data = $_SESSION[reqvar("report1")][reqvar("report2")];
}
else
{
  $report_data = $_SESSION[reqvar("report1")];
}

if(isset($report_data))
{
  $data = array_values($report_data);
  $legends = array_keys($report_data);

  foreach($legends as $id => $legend)
  {
    $legends[$id] = ($id+1) . ". " . spec_cut($legend, 15) . " - %.2f%%";
  }
}

$count = count($data);
$max_labels = 7;

if($count > $max_labels+1)
{
  $rest = array_slice($data, $max_labels, $count - $max_labels);
  $elm = array_sum($rest);

  array_splice($legends, $max_labels, $count - $max_labels);
  array_splice($data, $max_labels, $count - $max_labels);

  $legends[$max_labels] = text("Others");
  $data[$max_labels] = $elm;
}

$max_value = max($data);
$max_id = array_search($max_value, $data);

// Create the Pie Graph.
$graph = new PieGraph(938, 360, "auto");

if (!reqvar_empty("title")) {
  $graph->title->Set(reqvar("title"));
  $graph->title->SetFont(FF_ARIAL,FS_BOLD, 12);
}

// Create 3D pie plot
$p1 = new PiePlot3d($data);
$p1->SetCenter(0.50);
$p1->SetSize(140);

// Adjust projection angle
$p1->SetAngle(65);

$p1->ExplodeSlice($max_id);

$p1->SetLabels($legends, 1);

// Setup the slice values
$p1->value->SetFont(FF_ARIAL,FS_NORMAL,8);
//$p1->value->SetFormat("%.2f%%");
$p1->value->HideZero(TRUE);

$graph->Add($p1);
$graph->Stroke();

?>