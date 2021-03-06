<?php
    fscanf(STDIN, "%d %d %d %d",
        $w, // width of the board
        $h, // height of the board
        $playerCount, // number of players (2 or 3)
        $myId // id of my player (0 = 1st player, 1 = 2nd player, ...)
    );
    $w = $w * 2 - 2;
    $h = $h * 2 - 2;

    // game loop
    $step = 0;
    while (true) {
        $walls = $enemy = $I = [];

        for ($i = 0; $i < $playerCount; $i++) {
            $some = [];
            $some['ID'] = $i;
            fscanf(STDIN, "%d %d %d",
                $some['x'], // x-coordinate of the player
                $some['y'], // y-coordinate of the player
                $some['wallsLeft'] // number of walls available for the player
            );

            if ($myId == $i) {
                $I = $some;

            } else {
                if ($some['wallsLeft'] != -1)
                    $enemy[] = $some;
            }
        }

        $x = $y = $POS = 0;
        fscanf(STDIN, "%d", $wallCount); // number of walls on the board
        for ($i = 0; $i < $wallCount; $i++) {
            fscanf(STDIN, "%d %d %s",
                $x, // x-coordinate of the wall
                $y, // y-coordinate of the wall
                $POS // wall orientation ('H' or 'V')
            );
            $x = $x * 2;
            $y = $y * 2;
            $walls[$y][$x] = $POS;

            if ($POS == 'V') {
                $walls[$y + 1][$x] = $POS;
                $walls[$y + 2][$x] = $POS;
                $walls[$y + 3][$x] = $POS;

            } else {
                $walls[$y][$x + 1] = $POS;
                $walls[$y][$x + 2] = $POS;
                $walls[$y][$x + 3] = $POS;
            }
        }

        # =================================================================================================================
        //error_log(var_export($step, true));
        $buildWall = 0;

        if ($I['wallsLeft']) {
            if (($I['ID'] == 0 && $I['x'] > 5) || ($I['ID'] == 1 && $I['x'] < 3) || ($I['ID'] == 2 && $I['y'] > 5)) {
                /*foreach ($enemy as $one) {
                    $buildWall = buildWall($one, $walls);
                }*/
            }
        }

        if (!$buildWall) {
            $I['x'] = $I['x'] * 2 + 1;
            $I['y'] = $I['y'] * 2 + 1;
            $map = buildMap($w, $h);
            drawWalls($map, $walls);

            // find path to end -------------
            if ($myId == 0) {
                for ($y = 0; $y < $h; $y++) {
                    $map[$y][$w + 1] = 'S';
                }
            } elseif ($myId == 1) {
                for ($y = 0; $y < $h; $y++) {
                    $map[$y][-1] = 'S';
                }
            } elseif ($myId == 2) {
                for ($x = 0; $x < $w; $x++) {
                    $map[$h + 3][$x] = 'S';
                }
            }

            $map[$I['y']][$I['x']] = 'M' . $I['ID'];
            findPath($map, $I['x'], $I['y'], 0);
            showMAP($map, $w, $h);
            //-/ find path to end -------------


            //--- find closest end -------------
            $min = [];
            if ($myId == 0) {
                for ($y = 1; $y < $h; $y += 2) {
                    $min[$map[$y][$w - 1]] = $y;
                }
                ksort($min);
                $x = $w - 1;
                $y = current($min);

            } elseif ($myId == 1) {
                for ($y = 1; $y < $h; $y += 2) {
                    $min[$map[$y][1]] = $y;
                }
                ksort($min);
                $x = 1;
                $y = current($min);

            } elseif ($myId == 2) {
                for ($x = 1; $x < $w; $x += 2) {
                    $min[$map[$h - 1][$x]] = $x;
                }
                ksort($min);
                $x = current($min);
                $y = $h - 1;
            }
            error_log(var_export($y, true));
            //---/ find closest end -------------

            //---/ find path from end to start -------------
            $map = buildMap($w, $h);
            drawWalls($map, $walls);
            $map[$I['y']][$I['x']] = 'S';

            if ($myId == 1) {
                $x -= 2;
            } elseif ($myId == 2) {
                $y += 4;
            } else {
                $x += 2;
            }

            $map[$y][$x] = 'E';
            findPath($map, $x, $y, 0);
            showMAP($map, $w, $h);

            goToExit($map, $I['x'], $I['y'], $walls);
            //error_log(var_export($I, true));
            // Write an action using echo(). DON'T FORGET THE TRAILING \n
            // To debug (equivalent to var_dump): error_log(var_export($var, true));
            // action: LEFT, RIGHT, UP, DOWN or "putX putY putOrientation" to place a wall
        }
    }

    # =================================================================================================================
    # =================================================================================================================
    # =================================================================================================================

    function buildWall($enemy, $walls)
    {
        $x = $enemy['x'];
        $y = $enemy['y'];
        $build = 0;

        if (empty($walls) && $x - 1 >= 0) {
            echo ($x - 1) . ' ' . $y . " V\n";
            $build = 1;

        } else {
            if ($enemy['ID'] == 1) {
                if (!isset($walls[$y * 2][$x * 2]) && !isset($walls[$y * 2 + 2][$x * 2])) {
                    echo $x . ' ' . $y . " V\n";
                    $build = 1;
                }
            } elseif ($enemy['ID'] == 2) {
                if (!isset($walls[$y * 2 + 2][$x * 2]) && !isset($walls[$y * 2 + 2][$x * 2 + 2])) {
                    if ($x < 8 && $y < 8 && $x >= 0 && $y > -2) {
                        echo $x . ' ' . ($y + 1) . " H\n";
                        $build = 1;
                    }
                } elseif (!isset($walls[$y * 2 + 2][$x * 2 - 2]) && !isset($walls[$y * 2 + 2][$x * 2])) {
                    if ($x < 9 && $y < 8 && $x > 0 && $y > 0) {
                        echo ($x - 1) . ' ' . ($y + 1) . " H\n";
                        $build = 1;
                    }
                }
            } else {
                if (!isset($walls[$y * 2][$x * 2 + 2]) && !isset($walls[$y * 2 + 2][$x * 2 + 2]) && $y < 8) {
                    echo ($x + 1) . ' ' . $y . " V\n";
                    $build = 1;

                } elseif (!isset($walls[$y * 2 - 2][$x * 2 + 2]) && !isset($walls[$y * 2][$x * 2 + 2]) && $y > 0) {
                    echo ($x + 1) . ' ' . ($y - 1) . " V\n";
                    $build = 1;
                }
                //error_log(var_export($enemy, true));
            }
        }

        return $build;
    }

    function findPath(&$MAP, $X, $Y, $i)
    {
        $WAVE = oneWave($MAP, $X, $Y, ++$i);

        while (count($WAVE)) {
            $i++;
            $newWave = [];
            foreach ($WAVE as $Y => $W) {
                foreach ($W as $X => $x) {
                    oneWave($MAP, $X, $Y, $i, $newWave);
                }
            }
            $WAVE = $newWave;
        }
    }

    function oneWave(&$MAP, $X, $Y, $i, &$WAVE = [])
    {
        $prevBorderX = $X - 1; // borderX
        $prevBorderY = $Y - 1; // borderX
        $nextBorderX = $X + 1; // borderX
        $nextBorderY = $Y + 1; // borderX

        $prevX = $X - 2;
        $prevY = $Y - 2;
        $nextX = $X + 2;
        $nextY = $Y + 2;

        //error_log(var_export($Y." ".$maxY, true));

        if ($Y > 1 && (!isset($MAP[$prevBorderY][$X]) || $MAP[$prevBorderY][$X] !== 'H')) { //UP
            if (empty($MAP[$prevY][$X])) {
                $MAP[$prevY][$X] = $i;
                $WAVE[$prevY][$X] = 1;

            } elseif ($MAP[$prevY][$X] === 'S') {
                return array();
            }
        }

        if ($Y < 17 && (!isset($MAP[$nextBorderY][$X]) || $MAP[$nextBorderY][$X] !== 'H')) { //DOWN
            //error_log(var_export($nextY.'-'.$X, true));
            //error_log(var_export($MAP[$nextY][$X], true));
            if (empty($MAP[$nextY][$X])) {
                $MAP[$nextY][$X] = $i;
                $WAVE[$nextY][$X] = 1;
                //error_log(var_export('DOWN', true));

            } elseif ($MAP[$nextY][$X] === 'S') {
                return array();
            }
        }

        if ($X > 1 && (!isset($MAP[$Y][$prevBorderX]) || $MAP[$Y][$prevBorderX] !== 'V')) { //LEFT
            if (empty($MAP[$Y][$prevX])) {
                $MAP[$Y][$prevX] = $i;
                $WAVE[$Y][$prevX] = 1;
                //error_log(var_export('LEFT', true));

            } elseif ($MAP[$Y][$prevX] === 'S') {
                return array();
            }
        }

        if ($X < 17 && (!isset($MAP[$Y][$nextBorderX]) || $MAP[$Y][$nextBorderX] !== 'V')) { //RIGHT //check border
            if (empty($MAP[$Y][$nextX])) { //check field
                $MAP[$Y][$nextX] = $i;
                $WAVE[$Y][$nextX] = 1;

            } elseif ($MAP[$Y][$nextX] === 'S') {
                return array();
            }
        }


        return $WAVE;
    }

    function goToExit($MAP, $X, $Y)
    {
        $prevBorderX = $X - 1; // borderX
        $prevBorderY = $Y - 1; // borderX
        $nextBorderX = $X + 1; // borderX
        $nextBorderY = $Y + 1; // borderX

        $prevX = $X - 2;
        $prevY = $Y - 2;
        $nextX = $X + 2;
        $nextY = $Y + 2;

        //0 2 4 - borders
        //1 3 5 - field
        $RIGHT = $DOWN = $LEFT = $UP = 9999;

        if (isset($MAP[$Y][$nextX]) && (!isset($MAP[$Y][$nextBorderX]) || $MAP[$Y][$nextBorderX] !== 'V') && intval($MAP[$Y][$nextX]))
            $RIGHT = $MAP[$Y][$nextX];

        if (isset($MAP[$nextY][$X]) && (!isset($MAP[$nextBorderY][$X]) || $MAP[$nextBorderY][$X] !== 'H') && intval($MAP[$nextY][$X]))
            $DOWN = $MAP[$nextY][$X];

        if (isset($MAP[$Y][$prevX]) && (!isset($MAP[$Y][$prevBorderX]) || $MAP[$Y][$prevBorderX] !== 'V') && intval($MAP[$Y][$prevX]))
            $LEFT = $MAP[$Y][$prevX];

        if (isset($MAP[$prevY][$X]) && (!isset($MAP[$prevBorderY][$X]) || $MAP[$prevBorderY][$X] !== 'H') && intval($MAP[$prevY][$X]))
            $UP = $MAP[$prevY][$X];

        //error_log(var_export($MAP[$Y][$X] . " " . $Y . " " . $X, true));
        //error_log(var_export($RIGHT . " " . $DOWN . " " . $LEFT . " " . $UP . " ", true));


        if ($RIGHT <= $LEFT && $RIGHT <= $DOWN && $RIGHT <= $UP) {
            echo "RIGHT";

        } elseif ($DOWN < $UP && $DOWN <= $LEFT) {
            echo "DOWN";

        } elseif ($LEFT < $RIGHT && $LEFT <= $UP) {
            echo "LEFT";

        } else {
            echo "UP";
        }

        echo "\n";
    }

    function showMAP($MAP, $xMax, $yMax)
    {
        $sMap = "\n";
        $sMap .= " ||";

        for ($x = -2; $x < $xMax + 2; $x++) {
            if ($x < 10)
                $sMap .= " ";
            $sMap .= $x . "|";
        }
        $sMap .= "\n";

        for ($y = -2; $y < $yMax + 2; $y++) {
            if ($x < 10)
                $sMap .= " ";
            $sMap .= $y . "||";

            for ($x = -2; $x < $xMax + 2; $x++) {
                if (empty($MAP[$y][$x])) {
                    $sMap .= "  |";
                } else {
                    if ($MAP[$y][$x] < 10)
                        $sMap .= " ";

                    $sMap .= $MAP[$y][$x] . "|";
                }
            }

            $sMap .= "\n\n";
        }

        error_log(var_export($sMap, true));
    }

    function buildMap($w, $h)
    {
        $map = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $map[$y][$x] = 0;
            }
        }

        return $map;
    }

    function drawWalls(&$map, $walls)
    {
        foreach ($walls as $Y => $W) {
            foreach ($W as $X => $POS) {
                $map[$Y][$X] = $POS;
            }
        }
    }
