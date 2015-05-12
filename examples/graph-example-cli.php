<?php
set_time_limit(600);
ini_set('memory_limit','512M');
require_once 'class.Graph.php';

$cli = true;

if ($cli)
{
    $load = '';

    // Arguments
    $mode = '';
    $a1 = 0;
    $a2 = 0;  
    
    // Get data from CLI
    if (isset($argv[1]))
    {
        $load = $argv[1];
    }
    if (isset($argv[2]))
    {
        $mode = $argv[2];
    }
    if (isset($argv[3]))
    {
        $a1 = (int) $argv[3];
    }
    if (isset($argv[4]))
    {
        $a2 = (int) $argv[4];
    } 
    
}
else
{
    $load = 'graph3.txt';
    $save = 'graph3NEW.txt';
}

// Load from file
$graph = new Graph($cli);
$graph->loadFromFile($load);

#$graph->printGraphData();
#$graph->printMatrix();
#$graph->printGraphType();
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


if ($cli)
{  
    if ($mode == 'FSP')
    {
        $graph->printFindShortestPath($a1, $a2);   
    }
    if ($mode == 'TSP')
    {
        $graph->printTSPResult($a1);   
    }
    if ($mode == 'CPP')
    {
        $graph->printCPPResult($a1);      
    }
}
else
{
    $graph->printCPPResult(1);
}


#$graph->saveToFile($save);
#$graph->printDebug();