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

namespace pocketmine\utils\navigator\algorithms;

use pocketmine\utils\navigator\Tile;

class ManhattanHeuristicAlgorithm implements DistanceAlgorithm{
	
	public function calculate(Tile $from, Tile $to) : float{
		return abs($from->x - $to->x) + abs($from->y - $to->y);
	}
}