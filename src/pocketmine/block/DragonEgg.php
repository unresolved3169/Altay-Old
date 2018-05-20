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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

class DragonEgg extends Fallable{
	
	protected $id = self::DRAGON_EGG;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Dragon Egg";
	}

	public function getHardness() : float{
		return 3;
	}

	public function getBlastResistance() : float{
		return 45;
	}

	public function getLightLevel() : int{
		return 1;
 }

	public function isBreakable(Item $item) : bool{
		return false;
	}
	
	public function onActivate(Item $item, Player $player = null) : bool{
		$attempts = 0;
		while(true){
			$x = $this->getX() + rand(-15,15);
			$y = $this->getY() + rand(-7,7);
			$z = $this->getZ() + rand(-15,15);
			if($y < Level::Y_MAX && $level->getBlockIdAt($x, $y, $z) == 0){
				break;
 	 	 }
 	 	 
 	 	 if(++$attempts > 15){
 	 	 	 return false;
 	 	 }
  }
  
  $level->setBlock($this, new Air(), true, true);
  $oldpos = clone $this;
  $pos = new Position($x, $y, $z, $level);
  $newpos = clone $pos;
  $level->setBlock($pos, $this, true, true);
  $posdistance = $newpos->subtract($oldpos->x, $oldpos->y, $oldpos->z);
  $intdistance = $oldpos->distance($newpos);
  for($c = 0; $c <= $intdistance; $c++){
  	 $progress = $c / $intdistance;
  	 $this->getLevel()->broadcastLevelEvent(new Vector3($oldpos->x + $posdistance->x * $progress, 1.62 + $oldpos->y + $posdistance->y * $progress, $oldpos->z + $posdistance->z * $progress), 2010);
  }
  return true;
 }
}