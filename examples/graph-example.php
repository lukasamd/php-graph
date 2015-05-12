<?php
set_time_limit(600);
ini_set('memory_limit','512M');
require_once 'class.Graph.php';

$load = 'graph1a.txt';
$save = 'graph1aNEW.txt';

// Load from file
$graph = new Graph();
$graph->loadFromFile($load);

$graph->printGraphData();
$graph->printMatrix();
$graph->printGraphType();
#
#$graph->printIsGraphFull();
#$graph->printIsGraphRegular();
#$graph->printIsGraphRound();
#$graph->printIsGraphCyclic();
#
#$graph->changeGraphToRound();
#$graph->changeGraphToCyclic($key);
#$graph->printFindShortestPath(1, 3);
#$graph->colorGraph();

#$graph->printTSPResult();
#$graph->printTSPResult(1, 5);

#$graph->printFindShortestPath(1, 5);
#$graph->printCPPResult();


$graph->saveToFile($save);
#$graph->printDebug();