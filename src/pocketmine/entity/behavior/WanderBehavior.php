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

use pocketmine\block\Block;
use pocketmine\block\Grass;
use pocketmine\entity\Animal;
use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\entity\pathfinder\Path;

class WanderBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier = 1.0, $followRange = 16.0;
	/** @var int */
	protected $chance = 120;

	protected $targetPos;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $chance = 120){
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->chance = $chance;
	}

	public function canStart() : bool{
		if($this->random->nextBoundedInt($this->chance) === 0){
			$pos = $this->findRandomTargetBlock($this->mob, 10, 7);

			if($pos === null) return false;

			$path = Path::findPath($this->mob, $pos, $this->followRange = $this->mob->distanceSquared($pos) + 2);

			$this->targetPos = $pos;

			return $path->havePath();
		}

		return false;
	}

	public function canContinue() : bool{
		return $this->targetPos !== null;
	}

	public function onTick() : void{
		if(!$this->mob->getNavigator()->tryMoveTo($this->targetPos, $this->speedMultiplier, $this->followRange)){
			$this->targetPos = null;
		}
	}

	public function onEnd() : void{
		$this->mob->setMotion($this->mob->getMotion()->multiply(0, 1.0, 0.0));
		$this->targetPos = null;
	}

	public function findRandomTargetBlock(Entity $entity, int $dxz, int $dy) : ?Block{
		$currentWeight = PHP_INT_MIN;
		$currentBlock = null;
		for($i = 0; $i < 10; $i++){
			$x = $this->random->nextBoundedInt(2 * $dxz + 1) - $dxz;
			$y = $this->random->nextBoundedInt(2 * $dy + 1) - $dy;
			$z = $this->random->nextBoundedInt(2 * $dxz + 1) - $dxz;

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
			return (int) 0.5 - max($entity->level->getBlockLightAt(...$vec), $entity->level->getBlockSkyLightAt(...$vec));
		}
	}

	/**
	 * @param mixed $targetPos
	 * @return WanderBehavior
	 */
	public function setTargetPos($targetPos)
	{
		$this->targetPos = $targetPos;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getChance(): int
	{
		return $this->chance;
	}

	/**
	 * @param int $chance
	 */
	public function setChance(int $chance): void
	{
		$this->chance = $chance;
	}

	/**
	 * @return float
	 */
	public function getSpeedMultiplier(): float
	{
		return $this->speedMultiplier;
	}

	/**
	 * @param float $speedMultiplier
	 */
	public function setSpeedMultiplier(float $speedMultiplier): void
	{
		$this->speedMultiplier = $speedMultiplier;
	}

	/**
	 * @return mixed
	 */
	public function getTargetPos()
	{
		return $this->targetPos;
	}
}