<?php

namespace pocketmine\utils\navigator\algorithms;

use pocketmine\utils\navigator\Tile;

class ManhattanHeuristicAlgorithm implements DistanceAlgorithm{
	
	public function calculate(Tile $from, Tile $to) : int{
		return abs($from->x - $to->x) + abs($from->y - $to->y);
	}
}