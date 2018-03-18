<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\behaviors\pathfinding;

use pocketmine\utils\Utils;

class Path{

    /** @var PathPoint[] Contains the points in this path */
    private $pathPoints = [];

    /** @var int The number of points in this path */
    private $count;

    public function addPoint(PathPoint $point) : PathPoint{
        if($point->index >= 0){
            throw new \InvalidArgumentException();
        }else{
            if($this->count == count($this->pathPoints)){
                $pp = [];
                Utils::arrayCopy($this->pathPoints, 0, $pp, 0, $this->count);
                $this->pathPoints = $pp;
            }

            $this->pathPoints[$this->count] = $point;
            $point->index = $this->count;
            $this->sortBack($this->count++);

            return $point;
        }
    }

    public function clearPath() : void{
        $this->count = 0;
    }

    public function dequeue() : PathPoint{
        $pathpoint = $this->pathPoints[0];
        $this->pathPoints[0] = $this->pathPoints[--$this->count];
        $this->pathPoints[$this->count] = null;

        if($this->count > 0)
            $this->sortForward(0);

        $pathpoint->index = -1;
        return $pathpoint;
    }

    public function changeDistance(PathPoint $pp, float $distance) : void{
        $f = $pp->distanceToTarget;
        $pp->distanceToTarget = $distance;

        if($distance < $f)
            $this->sortBack($pp->index);
        else
            $this->sortForward($pp->index);
    }

    private function sortBack(int $index): void{
        $pathpoint = $this->pathPoints[$index];

        for($f = $pathpoint->distanceToTarget; $index > 0; $index = $i){
            $i = $index - 1 >> 1;
            $pathpoint1 = $this->pathPoints[$i];

            if($f >= $pathpoint1->distanceToTarget)
                break;

            $this->pathPoints[$index] = $pathpoint1;
            $pathpoint1->index = $index;
        }

        $this->pathPoints[$index] = $pathpoint;
        $pathpoint->index = $index;
    }

    private function sortForward(int $index) : void{
        $pathpoint = $this->pathPoints[$index];
        $f = $pathpoint->distanceToTarget;

        while(true){
            $i = 1 + ($index << 1);
            $j = $i + 1;

            if($i >= $this->count)
                break;

            $pathpoint1 = $this->pathPoints[$i];
            $f1 = $pathpoint1->distanceToTarget;

            if($j >= $this->count){
                $pathpoint2 = null;
                $f2 = INT32_MAX;
            }else{
                $pathpoint2 = $this->pathPoints[$j];
                $f2 = $pathpoint2->distanceToTarget;
            }

            if($f1 < $f2){
                if($f1 >= $f)
                    break;

                $this->pathPoints[$index] = $pathpoint1;
                $pathpoint1->index = $index;
                $index = $i;
            }else{
                if($f2 >= $f)
                    break;

                $this->pathPoints[$index] = $pathpoint2;
                $pathpoint2->index = $index;
                $index = $j;
            }
        }

        $this->pathPoints[$index] = $pathpoint;
        $pathpoint->index = $index;
    }

    public function isPathEmpty() : bool{
        return $this->count == 0;
    }

}