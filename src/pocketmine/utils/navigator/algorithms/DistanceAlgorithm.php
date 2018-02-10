<?php

namespace pocketmine\utils\navigator\algorithms;

use pocketmine\utils\navigator\Tile;

interface DistanceAlgorithm{
	
	public function calculate(Tile $from, Tile $to) : int;
}