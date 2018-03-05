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

namespace pocketmine\entiy\behavior\navigator;
 
class Tile{

    /** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var float */
	public $fScore;
	/** @var float */
	public $gScore;
	
	public function __construct(int $x, int $y, float $fScore = 0.0, float $gScore = 0.0){
		$this->x = $x;
		$this->y = $y;
		$this->fScore = $fScore;
		$this->gScore = $gScore;
	}
	
	public function equals(Tile $other) : bool{
		return $this->x == $other->x && $this->y == $other->y;
	}
	
	public function getHashCode() : int{
		return ($this->x * 397) ^ $this->y;
	}
	
	public function __toString(){
		return "Tile : " . $this->x . ":" . $this->y . ":" . $this->fScore . ":" . $this->gScore;
	}
	
	public static function fromString(string $str) : ?Tile{
        $parts = explode(":", $str);

        if(count($parts) !== 5)
            return null; // TODO : Throw ekleriz belki

        array_shift($parts);
        return new Tile((int)$parts[0], (int)$parts[1], (float)$parts[2], (float)$parts[3]);
    }
}