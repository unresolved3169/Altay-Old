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

use pocketmine\math\Vector3;

class PathPoint{

    /** @var int */
    public $xCoord;

    /** @var int */
    public $yCoord;

    /** @var int */
    public $zCoord;

    /** @var int A hash of the coordinates used to identify this point */
    private $hash;

    /** @var int The index of this point in its assigned path */
    public $index = -1;

    /** @var float The distance along the path to this point */
    public $totalPathDistance;

    /** @var float The linear distance to the next point */
    public $distanceToNext;

    /** @var float The distance to the target */
    public $distanceToTarget;

    /** @var PathPoint The point preceding this in its assigned path */
    public $previous;

    /** @var bool True if the pathfinder has already visited this point */
    public $visited;

    public function __construct(int $x, int $y, int $z){
        $this->xCoord = $x;
        $this->yCoord = $y;
        $this->zCoord = $z;
        $this->hash = self::makeHash($x, $y, $z);
    }

    public static function makeHash(int $x, int $y, int $z) : int{
        return $y & 255 | ($x & 32767) << 8 | ($z & 32767) << 24 | ($x < 0 ? INT32_MIN : 0) | ($z < 0 ? 32768 : 0);
    }

    /**
     * Returns the linear distance to another path point
     *
     * @param PathPoint $pp
     * @return float
     */
    public function distanceTo(PathPoint $pp) : float{
        return sqrt($this->distanceToSquared($pp));
    }

    /**
     * Returns the squared distance to another path point
     *
     * @param PathPoint $pp
     * @return float
     */
    public function distanceToSquared(PathPoint $pp) : float{
        $f = ($pp->xCoord - $this->xCoord);
        $f1 = ($pp->yCoord - $this->yCoord);
        $f2 = ($pp->zCoord - $this->zCoord);
        return $f * $f + $f1 * $f1 + $f2 * $f2;
    }

    public function equals(PathPoint $pp){
        return $this->hash == $pp->hash && $this->xCoord == $pp->xCoord && $this->yCoord == $pp->yCoord && $this->zCoord == $pp->zCoord;
    }

    public function getHash() : int{
        return $this->hash;
    }

    /**
     * Returns true if this point has already been assigned to a path
     *
     * @return bool
     */
    public function isAssigned() : bool{
        return $this->index >= 0;
    }

    public function __toString(){
        return "PathPoint(x=" . $this->xCoord . ",y=" . $this->yCoord . ",z=" . $this->zCoord . ")";
    }

    public function toVector3(){
    	return new Vector3($this->xCoord, $this->yCoord, $this->zCoord);
    }

}