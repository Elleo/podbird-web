<?php
require_once("templating.php");
$smarty->assign('page', 'charts');
require_once("authentication.php");
require_once("model/chart.php");

$chart = new Chart();

$smarty->assign('title', 'Charts');
$smarty->assign('chart', $chart->top(100));
$smarty->display('charts.tpl');
?>
