<?php

namespace pocketmine\utils\navigator\algorithms;

use pocketmine\utils\navigator\Tile;

class PythagorasAlgorithm implements DistanceAlgorithm{
	
	public function calculate(Tile $from, Tile $to) : int{
		return sqrt(pow($to->x - $from->x, 2) + pow($to->y - $from->y, 2));
	}
}