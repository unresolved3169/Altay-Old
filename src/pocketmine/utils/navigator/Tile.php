<?php

namespace pocketmine\utils\navigator;

/**
 * Ported from NiclasOlofsson's AStarNavigator fork
 */
 
class Tile{
	
	public $x = 0;
	public $y = 0;
	public $fScore = 0;
	public $gScore = 0;
	
	public function __construct(int $x, int $y){
		$this->x = $x;
		$this->y = $y;
	}
	
	public function equals(Tile $tile) : bool{
		return $this->x === $tile->x and $this->y === $tile->y;
	}
	
	public function getHashCode() : int{
		return ($this->x * 397) ^ $this->y;
	}
	
	public function __toString(){
		return $this->x . ":" . $this->y . ":" . $this->fScore . ":" . $this->gScore;
	}
	
	public static function fromString(string $str) : Tile{
		$part = explode(":", $str);
		$tile = new Tile();
		$tile->x = (int) $part[0] ?? 0;
		$tile->y = (int) $part[1] ?? 0;
		$tile->fScore = (int) $part[2] ?? 0;
		$tile->gScore = (int) $part[3] ?? 0;
		
		return $tile;
	}
}