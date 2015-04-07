<?php


class Graph
{
    private $graph = array();
    
    // Adjacency matrix
    private $aMatrix = array();
    
    // Incidence matrix
    private $iMatrix = array();
    
    // Weight matrix
    private $wMatrix = array();
    
    // Degrees matrix
    private $dMatrix = array();
    
    // Colors matrix
    private $cMatrix = array();
    
    private $numEdge = 0;
    private $numVertex = 0;
    
    // EulerCycle
    private $eMatrix = array();
    private $ePath = array();
    
    //CLI
    private $cli;
    
    
    public function __construct($cli = false)
    {
        $this->cli = $cli;
    }

    // Dane debugowania do druku
    public function printDebug()
    {
        echo '<h2>DEBUG MODE</h2>';
        echo '<pre>';
        print_r($this);
        echo '</pre>';
        
    } 
    
    // Wczytywanie grafu z pliku
    public function loadFromFile($filename)
    {
        $handle = @fopen($filename, "r"); 
        $info = fgets($handle);    
        $info = explode(' ', $info);
        $info = array_map('intval', $info);
        
        // Dane dodatkowe
        $this->numVertex = $info[0];
        $this->numEdge = $info[1]; 

        // Fill matrix
        $this->aMatrix = array_fill(1, $info[0], array_fill(1, $info[0], 0));
        for ($i = 1; $i <= $info[1]; $i++)
        {
            $data = fgets($handle); 
            $data = explode(' ', $data);
            $data = array_map('intval', $data);

            @$this->aMatrix[$data[0]][$data[1]]++;
            if ($data[0] != $data[1])
            {
                @$this->aMatrix[$data[1]][$data[0]]++;
            }
            

            // Macierz incydencji
            $edge = $data[0] . '-' . $data[1];

            // Uzupelnij wage
            if (isset($data[2]))
            {
                $this->wMatrix[$data[0]][$data[1]][] = $data[2];
                $this->wMatrix[$data[1]][$data[0]][] = $data[2];
            }
            
            // Macierz incydencji2
            isset($this->iMatrix[$edge]) ? $this->iMatrix[$edge]++ : $this->iMatrix[$edge] = 1;   
        }
    }

    // // Zapisz graf do pliku
    public function saveToFile($filename)
    {
        $content = "graph G {\n";
        foreach ($this->iMatrix as $edge => $num)
        {
            $temp = explode('-', $edge);
            for ($i = 1; $i <= $num; $i++)
            {
                $weight = $color1 = $color2 = '';
                if (!empty($this->wMatrix))
                {
                    $weight = ' [label="' . $this->wMatrix[$temp[0]][$temp[1]][$i-1] . '"]';
                }
                if (!empty($this->cMatrix))
                {
                    $color1 = 'k' . $this->cMatrix[$temp[0]];
                    $color2 = 'k' . $this->cMatrix[$temp[1]];
                }
                $content .= "\t" . $temp[0] . $color1 . " -- " . $temp[1] . $color2 . $weight . "\n";
            }
        }
        $content .= "}";
        file_put_contents($filename, $content);
    }
    
    // Liczenie stopni wierzcholkow
    private function calculateVertexDegree()
    {
        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            $this->dMatrix[$i] = array_sum($this->aMatrix[$i]);                 
        } 
    }
    
    // Wyswietlanie stopnie wierzcholkow
    public function printVertexDegrees()
    {
        $this->calculateVertexDegree();
        
        echo '<h2>Stopnie wierzcholkow:</h2>';
        foreach ($this->dMatrix as $key => $val)
        {
            echo "deg($key) = $val<br />";
        }

        sort($this->dMatrix);
        echo 'Ciag stopni grafu G: ' . implode(', ', $this->dMatrix);
    }
    
    
    // Okreslanie typu grafu
    public function printGraphType()
    {
        echo '<h2>Typ grafu:</h2>';

        // Sprawdz petle
        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            if ($this->aMatrix[$i][$i])
            {
                echo 'Graf ogolny';
                return;
            }              
        }

        // Sprawdz krawedzie wielokrotne
        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            for ($j = 1; $j <= $this->numVertex; $j++)
            {
                if ($this->aMatrix[$i][$j] > 1)
                {
                    echo 'Graf ogolny';
                    return;
                }
            }  
        }

        echo 'Graf prosty'; 
    }

    // Podstawowe dane grafu
    public function printGraphData()
    {
        echo '<h2>Podstawowe dane grafu:</h2>';
        echo 'Liczba wierzcholkow grafu G wynosi: ' . $this->numVertex . '<br />';
        echo 'Zbior wierzcholkow V = {' . implode(', ', array_keys($this->aMatrix)) . '}';
        echo '<br />';

        echo '<br />';
        echo 'Liczba krawedzi grafu G wynosi: ' . $this->numEdge . '<br />';

        $edges = array();
        foreach ($this->iMatrix as $edge => $num)
        {
            for ($i = 1; $i <= $num; $i++)
            {
                $edges[] = $edge;
            }

        }
        echo 'Zbior krawedzi E = {' . implode(', ', $edges) . '}';
        echo '<br />';
    }
    
    // SPrawdzanie czy graf jest pelny
    private function checkIsGraphFull()
    {
        // Szukanie brakujacych krawedzi
        $lost = array();
        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            for ($j = $i; $j <= $this->numVertex; $j++)
            {
                if ($i == $j)
                {
                    continue;
                }
                if ($this->aMatrix[$i][$j] == 0)
                {
                    $lost[] = $i . '-' . $j;
                }    
            }   
        } 
        
        // Czy zgadza sie liczba krawedzi
        $num = ($this->numVertex * ($this->numVertex - 1)) / 2;
        if (empty($lost) && $num == $this->numEdge)
        {
            return true;
        }

        if (!empty($lost))
        {
            return implode(', ', $lost);
        }
        return false;
        
        
    }

    // Czy graf jest grafem pelnym?
    public function printIsGraphFull()
    {
        $result = $this->checkIsGraphFull();
        
        echo '<h2>Czy graf jest pelny?</h2>';
        if ($result === TRUE)
        {
            echo 'Tak. Ten graf jest grafem pelnym';
        }
        else
        {
            echo 'Nie, to nie jest graf pelny.<br />';
        }
        
        if (is_string($result))
        {
            echo 'Brakujace krawedzie: ' . $result;
        }
    }

    // Rysowanie macierzy
    public function printMatrix()
    {
        // Sasiedctwa
        echo '<h2>Macierz sasiedztwa:</h2>';
        echo '<table border=1>';
        echo '<tr><th></th><th>';   
        echo implode('</th><th>', array_keys($this->aMatrix));

        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            echo '<tr>';
            echo '<td><b>' . $i . '</b></td>';
            for ($j = 1; $j <= $this->numVertex; $j++)
            {
                echo '<td>' . $this->aMatrix[$i][$j] . '</td>';
            }
            echo '</tr>';    
        }
        echo '</tr>';
        echo '</table>';

        // Incydencji (rozbijanie tablicy)
        echo '<h2>Macierz incydencji:</h2>';
        echo '<table border=1>';
        echo '<tr><th></th><th>';   
        echo implode('</th><th>', array_keys($this->iMatrix));

        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            echo '<tr>';
            echo '<td><b>' . $i . '</b></td>';

            foreach ($this->iMatrix as $key => $row)
            {
                $elements = explode('-', $key);

                if (in_array($i, $elements))
                {
                    echo '<td>' . $row . '</td>';
                }
                else
                {
                    echo '<td>0</td>';
                }
            }  
        }

        echo '</tr>';
        echo '</table>';
    }

    // Rysowanie listy wierzoclkow
    public function printVertexList()
    {
        $list = getVertexList();

        echo '<h2>Lista wierzcholkow grafu:</h2>';
        foreach ($list as $vertex => $edges)
        {
            echo $vertex . ' -> ' . implode(', ', $edges) . '<br />';
        }
    }

    // Czy graf regular?
    public function printIsGraphRegular()
    {
        $this->calculateVertexDegree();

        // Sprawdzanie regularnosci
        $temp = array_unique($this->dMatrix);

        echo '<h2>Czy graf jest regularny?</h2>';
        if (count($temp) == 1)
        {
            echo 'Tak, to jest graf regularny.<br />';
            echo 'Stopien grafu:' . current($temp);
        }
        else
        {
            echo 'Nie, ten graf nie jest regularny.';
        }
    }

    // Pobierz liste wierzcholkow
    private function getVertexList()
    {
        $list = array();

        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            for ($j = 1; $j <= $this->numVertex; $j++)
            {
                if ($i == $j)
                {
                    continue;
                }

                if ($this->aMatrix[$i][$j] > 0)
                {
                    $list[$i][] = $j;
                }
            }
        }

        return $list;  
    }
    
    // Czy graf regularny
    
    private function isGraphRegular($degree)
    {
        $list = $this->getVertexList(); 
        
        foreach ($list as $element)
        {
            if ($degree != count($element))
            {
                return false;
            }
        }
        return true;
    }
    
    
    // Czy graf cykliczny?
    public function printIsGraphCyclic()
    {
        // sprawdzic czy regular stopnia 2
        $visited = array();

        if ($this->isGraphRegular(2))
        {
            $this->checkGraphCycle(1, $visited);
        }

        echo '<h2>Czy graf cykliczny?</h2>';
        if (count($visited) == $this->numVertex)
        {
            echo 'To jest graf cykliczny';
        }
        else
        {
            echo 'To nie jest graf cykliczny'; 
        } 
    }

    private function checkGraphCycle($key, &$visited)
    {
        static $list;
        
        // Tylko jeden raz
        if (empty($list))
        {
            $list = $this->getVertexList(); 
        }
        
        // Zapis id
        $visited[] = $key;
        
        // Wyklucz z wyboru
        $allowed = array_diff($list[$key], $visited);
        
        // Idz dalej lub wracaj
        if (!empty($allowed))
        { 
            $this->checkGraphCycle(current($allowed), $visited);
        }
        else
        {
            return;
        }
    }
    
    
    // Zamiana na graf kolowy
    public function changeGraphToRound()
    {
        $key = $this->numVertex + 1;
        $num = $this->numVertex;
        for ($i = 1; $i <= $num; $i++)
        {
            $this->aMatrix[$i][$key] = 1;
            $this->aMatrix[$key][$i] = 1;
            $edge = $key . "-" . $i;
            $this->iMatrix[$edge] = 1;

            $this->numEdge++;
        }
        $this->numVertex++;
        $this->aMatrix[$key][$key] = 0;
    }

    // Czy graf jest kolowy?
    public function printIsGraphRound()
    {
        $degree = $this->numVertex - 1;
        $central = 0;

        $pattern = array(
            3 => $this->numVertex - 1,
            $degree => 1,
        );

        $degrees = array();
        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            $value = array_sum($this->aMatrix[$i]);
            if (isset($degrees[$value]))
            {
                $degrees[$value]++;
            }                 
            else
            {
                $degrees[$value] = 1;
            }

            if ($value == $degree)
            {
                $central = $i;
            }
        }     

        echo '<h2>Czy graf jest kolem?</h2>';
        if (empty(array_diff($degrees, $pattern)))
        {
            echo 'Ten graf jest kolem';
        }
        else
        {
            echo 'Ten graf nie jest kolem';
        }
    }

    // Zamiana grafu na cykliczny
    public function changeGraphToCyclic($key)
    {
        // Usuwanie z tablicy
        foreach ($this->aMatrix[$key] as $element => $value)
        {
            if ($value > 0)
            {
                $edge1 = $key . '-' . $element;
                $edge2 = $element . '-' . $key;

                unset($this->iMatrix[$edge1]);
                unset($this->iMatrix[$edge2]);
                unset($this->aMatrix[$element][$key]);

                $this->numEdge--;
            }     
        }
        unset($this->aMatrix[$key]);
        $this->numVertex--;
    }
    
    // Obliczanie najkrotszej dorgi
    private function DijkstraCalculate($source, $target)
    {
        // Tymczasowa tabela dla algorytmu
        $graph_array = array();
        foreach ($this->iMatrix as $edge => $num)
        {
            $temp = explode('-', $edge);
            for ($i = 0; $i < $num; $i++)
            {
                // $graph_array[] = array($temp[0], $temp[1], $this->wMatrix[$temp[0]][$temp[1]][$i]);
                $graph_array[] = array($temp[0], $temp[1], $this->wMatrix[$temp[0]][$temp[1]][0]);
            }
        }
        
        // Odrzuc zbyt krotkie
        if(count($graph_array) < 2)
        {
            return false;   
        }
            
        $vertices = [];
        $neighbours = [];
        foreach ($graph_array as $edge)
        {
            array_push($vertices, $edge[0], $edge[1]);
            $neighbours[$edge[0]][] = array("end" => $edge[1], "cost" => $edge[2]);
            $neighbours[$edge[1]][] = array("end" => $edge[0], "cost" => $edge[2]);
        }
        $vertices = array_unique($vertices);
 
        foreach ($vertices as $vertex)
        {
            $dist[$vertex] = INF;
            $previous[$vertex] = NULL;
        }
 
        $dist[$source] = 0;
        $Q = $vertices;
        while (count($Q) > 0)
        {
            $min = INF;
 
            foreach ($Q as $vertex)
            {
                if ($dist[$vertex] < $min) 
                    {
                    $min = $dist[$vertex];
                    $u = $vertex;
                }
            }
 
            $Q = array_diff($Q, array($u));
            if ($dist[$u] == INF || $u == $target)
                break;
 
 
            if (isset($neighbours[$u]))
            {
                foreach ($neighbours[$u] as $arr)
                {
                    $alt = $dist[$u] + $arr["cost"];
                    if ($alt < $dist[$arr["end"]])
                    {
                        $dist[$arr["end"]] = $alt;
                        $previous[$arr["end"]] = $u;
                    }
                }
            }
        }

        $path = array();
        $u = $target;
        while (isset($previous[$u]))
        {
            array_unshift($path, $u);
            $u = $previous[$u];
        }
        array_unshift($path, $u);
        return $path;
    }
    
    // Liczenie kosztu drogi
    
    // Liczenie kosztu drogi
    private function getRouteCost($route)
    {
        $cost = 0;
        $count = count($route) - 1;
        
        for ($i = 0; $i < $count; $i++)
        {
            if (isset($this->wMatrix[$route[$i]][$route[$i+1]][0]))
            {
                $cost += ($this->wMatrix[$route[$i]][$route[$i+1]][0]);
            }
            else
            {
                $cost = INF;
            }
        } 
        
        return $cost;
    }
    
    
    

    // Szukanie najkrotszej drogi w grafie
    public function printFindShortestPath($w1 = 0, $w2 = 0)
    {
        $route = $this->DijkstraCalculate($w1, $w2);
        $cost = $this->getRouteCost($route);
        
        if ($this->cli)
        {
            echo "Najkrotsza droga pomiedzy wierzcholkami {$w1} i {$w2} wynosi: {$cost}";
        }
        else 
        {
            echo '<h2>Najkrotsza droga miedzy wierzcholkami</h2>';
            echo 'Wierzcholek 1: ' . $w1 . '<br />';
            echo 'Wierzcholek 2: ' . $w2 . '<br />';
            echo implode('-', $route) . '<br />';
            echo 'Dlugosc drogi miedzy wierzcholkami: ' . $cost . '<br />';  
        }


    }

    // Kolorowanie grafu
    public function colorGraph()
    {
        // Tablica kolorow danego wierzcholka
        $this->cMatrix = array_fill(1, $this->numVertex, 0);

        // Tablica kolorow sasiadow
        $neighbours = array_fill(1, $this->numVertex, 0);

        // Liczba uzytych kolorow
        $numColors = 0;

        for ($i = 1; $i <= $this->numVertex; $i++)
        {
            for ($j = 1; $j <= $this->numVertex; $j++)
            {
                $neighbours[$j] = 0;
            }

            for ($j = 1; $j < $i; $j++)
            {
                if ($this->aMatrix[$i][$j])
                {
                    $neighbours[$this->cMatrix[$j]] = 1;
                }
            }

            for ($j = 1; $j <= $this->numVertex && $neighbours[$j]; $j++);
            $this->cMatrix[$i] = $j;
            if ($numColors < $j)
            {
                $numColors++;
            }

        } 
    }

    // Permutacje
    private function getPermutations(array $elements)
    {
        if (count($elements) <= 1) 
        {
            yield $elements;
        } 
        else 
        {
            foreach ($this->getPermutations(array_slice($elements, 1)) as $permutation) 
            {
                foreach (range(0, count($elements) - 1) as $i) 
                {
                    yield array_merge(array_slice($permutation, 0, $i), [$elements[0]], array_slice($permutation, $i));
                }
            }
        }
    }
    
    // Problem komiwojazdezra
    public function printTSPResult($idxA = false, $idxB = false)
    {
        $result = $this->calculateTSP($idxA, $idxB);
        
        if ($this->cli)
        {
            echo "Problem komiwojazera - najkrotsza droga dla wierzcholka {$idxA} wynosi: {$result['cost']}\n\r";
            echo implode('-', $result['route']);
        }
        else
        {
            echo '<h2>Problem komiwojazera:</h2>';
            if ($idxA && $idxB)
            {
                echo 'Najlepsza droga z punktu ' . $idxA . ' do punktu ' . $idxB . ': ' . implode('-', $result['route']) . '<br />'; 
            }
            else
            {
                echo 'Najlepsza droga: ' . implode('-', $result['route']) . '<br />'; 
            }
            echo 'Koszt drogi: ' . $result['cost'];      
        }
    }
    
    // Liczenie problemu komiwojadzera
    private function calculateTSP($idxA = false, $idxB = false)
    {
        $best = array('route' => array(), 'cost' => INF);
        
        // Wszystkie wirzcholki
        $list = array_keys($this->getVertexList());
        $num = count($list) - 1;

        // i ich permutacje
        foreach ($this->getPermutations($list) as $permutation) 
        {
            // Przypadek wyboru punktu startoego i koncowego
            if ($idxA && $idxB)
            {
                if ($permutation[0] != $idxA || $permutation[$num] != $idxB)
                {
                    continue;
                }
            }
            
            // Obliczanie drogi
            $cost = $this->getRouteCost($permutation);
            if ($cost < $best['cost'])
            {
                $best = array('route' => $permutation, 'cost' => $cost);
            }
        }
        
        // Dodaj powrot
        $num = count($best['route']) - 1;
        $cost = $this->getRouteCost($this->DijkstraCalculate($best['route'][$num], $best['route'][0]));
        $best['cost'] += $cost;

        return $best;
    }
    

    

    // Problem chinskiego listonosza
    function printCPPResult($start = 1)
    {
        static $numer = 0;
        
        // Wariant I - Jezeli euler - to eulera
        $odd = array();
        $num_odd = 0;
        $this->calculateVertexDegree();
        foreach ($this->dMatrix as $vertex => $degree)
        {
            if ($degree % 2 != 0)
            {
               $odd[] = $vertex;
            }
        }
        $num_odd = count($odd);
        
        // Wariant I - Jezeli euler - to eulera
        if (0 == $num_odd)
        {
            $this->eMatrix = $this->aMatrix;
            $this->calculateDFSEuler($start);
            
            $cost = $this->getRouteCost($this->ePath);
            $cost2 = $this->getRouteCost($this->DijkstraCalculate(reset($this->ePath), end($this->ePath)));
            $final_cost = $cost + $cost2;
            
            if ($this->cli)
            {
                echo "Problem chinskiego listonosza - najkrotsza trasa dla wierzcholka {$start} wynosi:: {$final_cost}";
                return;
            }
            else
            {
                echo '<h2>Problem chinskiego listonosza</h2>';
                echo 'Najlepsza droga: ' . implode('-', $this->ePath) . '<br />'; 
                echo 'Koszt drogi: ' . ($cost + $cost2);   
                return;  
            }

        }
        
        // Wariant III - nieparzysta ilosc, odpada
        if ($num_odd % 2 != 0)
        {
            echo '<h2>Problem chinskiego listonosza</h2>';
            echo 'Brak rozwiązania.';
            return;
        }
        
        // Wariant III - dwie, djkstra
        if (2 == $num_odd)
        {
            $this->doubleEdge($odd);
            $this->printCPPResult();
            return;
        }

        // Wariant IV - 4 niepatrzyste, kombinacje zbiorow 2-elementowych
        $combinations = array();
        for ($i = 0; $i < $num_odd; $i++)
        {
            for ($j = $i + 1; $j < $num_odd; $j++)
            {
                $combinations[] = array($odd[$i], $odd[$j]);
            }
        }

        // Zbior mozliwych par podzbiorow wraz z ich kosztem 
        $num_ids = count($combinations);
        $routes_pairs = array();
        $costs = array();
        
        for ($i = 0; $i < $num_ids; $i++)
        {
            $found = false;
            
            for ($j = $i + 1; $j < $num_ids; $j++)
            {
                if (count(array_diff($combinations[$i], $combinations[$j])) == 2)
                {
                    $routes_pairs[$i] = array($combinations[$i], $combinations[$j]); 
                    $found = true;
                    
                    $cost1 = $this->getRouteCost($this->DijkstraCalculate($combinations[$i][0], $combinations[$i][1]));
                    $cost2 = $this->getRouteCost($this->DijkstraCalculate($combinations[$j][0], $combinations[$j][1]));
                    $costs[$i] = $cost1 + $cost2;
                    break;
                }
            }
            
            if ($found)
            {
                continue;
            }
        }

        // Wybor pary o najmniejszym koszcie
        $idx = array_keys($costs, min($costs))[0];

        // Dodawanie brakujacych krawedzi
        foreach ($routes_pairs[$idx] as $pair)
        {
            $this->doubleEdge($pair);
        }
        
        // I jeszcze raz
        $this->printCPPResult();
        return;
    }
    
    // Dodawanie nowej krawedzi
    private function doubleEdge($pair)
    {
        $route = $this->DijkstraCalculate($pair[0], $pair[1]);
        $count = count($route) - 1;
        
        for ($i = 0; $i < $count; $i++)
        {
            $a = $route[$i];
            $b = $route[$i+1];
            
            $this->aMatrix[$a][$b]++;
            if ($a != $b)
            {
                @$this->aMatrix[$b][$a]++;
            }

            $edge = $a . '-' . $b;
            isset($this->iMatrix[$edge]) ? $this->iMatrix[$edge]++ : $this->iMatrix[$edge] = 1; 
            $this->numEdge++;
        } 
    }

    
    // Obliczanie sciezki Eulera
    private function calculateDFSEuler($v)
    {
        for ($i = 0; $i <= $this->numVertex; $i++)
        {
            if (!isset($this->eMatrix[$v][$i]))
            {
                continue;
            }
            while ($this->eMatrix[$v][$i] > 0)
            {
                $this->eMatrix[$v][$i]--;
                $this->eMatrix[$i][$v]--;
                $this->calculateDFSEuler($i);
            }
        }
        $this->ePath[] = $v;
    }
}