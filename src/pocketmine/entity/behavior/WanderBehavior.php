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

namespace pocketmine\entity\behavior;

use pocketmine\entity\Entity;
use pocketmine\block\Block;
use pocketmine\block\Grass;
use pocketmine\entity\Mob;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\entity\Animal;

class WanderBehavior extends Behavior{

    /** @var float */
	protected $speedMultiplier = 1.0;
	/** @var int */
	protected $chance = 120;
	/** @var Path */
	protected $currentPath = null;
	
	public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $chance = 120){
		parent::__construct($mob);
		
		$this->speedMultiplier = $speedMultiplier;
		$this->chance = $chance;
	}
	
	public function canStart() : bool{
		if(rand(0,$this->chance) === 0){
			$pos = $this->findRandomTargetBlock($this->mob, 10, 7);
			
			if($pos === null) return false;
			
			$path = Path::findPath($this->mob, $pos, $this->mob->distance($pos));
			
			$this->currentPath = $path;
			
			return $path->havePath();
		}

		return false;
	}
	
	public function canContinue() : bool{
		return $this->currentPath->havePath();
	}
	
	public function onTick(int $tick) : void{
		if($this->currentPath->havePath()){
			if($next = $this->currentPath->getNextTile($this->mob)){
				$this->mob->lookAt(new Vector3($next->x + 0.5, $this->mob->y, $next->y + 0.5));
				$this->mob->moveForward($this->speedMultiplier);
			}
		}
	}
	
	public function onEnd() : void{
		$this->mob->motionX = 0; $this->mob->motionZ = 0;
		$this->currentPath = null;
	}
	
	public function findRandomTargetBlock(Entity $entity, int $dxz, int $dy) : ?Block{
		$random = new Random();
		
		$currentWeight = 0;
		$currentBlock = null;
		for($i = 0; $i < 10; $i++){
			$x = $random->nextRange(0, 2 * $dxz + 1) - $dxz;
			$y = $random->nextRange(0, 2 * $dy + 1) - $dy;
			$z = $random->nextRange(0, 2 * $dxz + 1) - $dxz;
			
			$blockCoords = new Vector3($x,$y,$z);
			$block = $entity->level->getBlock($this->mob->asVector3()->add($blockCoords));
			$blockDown = $block->getSide(0);
			if($blockDown->isSolid()){
				$weight = $this->calculateBlockWeight($entity, $block, $blockDown);
				if($weight > $currentWeight){
					$currentWeight = $weight;
					$currentBlock = $block;
				}
			}
		}
		
		return $currentBlock;
	}
	
	public function calculateBlockWeight(Entity $entity, Block $block, Block $blockDown) : int{
		$vec = [$block->getX(), $block->getY(), $block->getZ()];
		if($entity instanceof Animal){
			if($blockDown instanceof Grass) return 20;
			
			return (int) (max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec)) - 0.5);
		}else{
			return (int) abs(0.5 - max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec)));
		}
	}
}