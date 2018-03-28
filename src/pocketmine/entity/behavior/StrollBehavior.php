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

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\entity\Mob;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class StrollBehavior extends Behavior{

	/** @var int */
	protected $duration;
	/** @var float */
	protected $speed;
	/** @var float */
	protected $speedMultiplier;
	/** @var int */
	protected $timeLeft;

	public function __construct(Mob $mob, int $duration, float $speed, float $speedMultiplier){
		parent::__construct($mob);

		$this->duration = $this->timeLeft = $duration;
		$this->speed = $speed;
		$this->speedMultiplier = $speedMultiplier;
	}

	public  function canStart(): bool{
		return $this->random->nextBoundedInt(120) == 0;
	}

	public function canContinue(): bool{
		return $this->timeLeft-- > 0;
	}

	public function onTick(int $tick) : void{
		$speedFactor = (float) ($this->speed*$this->speedMultiplier*0.7*($this->mob->isInsideOfWater() ? 0.3 : 1.0)); // 0.7 is a general mob base factor
		$level = $this->mob->level;
		$coordinates = $this->mob->asVector3();
		$direction = $this->mob->getDirectionVector()->multiplyVector(new Vector3(1, 0, 1));

		$blockDown = $level->getBlock($coordinates->getSide(Vector3::SIDE_DOWN));
		if ($this->mob->motionY < 0 && $blockDown instanceof Air) {
			$this->timeLeft = 0;
			return;
		}

		$offset = $direction->multiply($speedFactor)->add($direction->multiply($this->mob->width / 2));
		$coord = $coordinates->add($offset);

		$players = $level->getPlayers();
		$entityCollide = false;
		$boundingBox = $this->mob->getBoundingBox()->offset($offset->x, $offset->y, $offset->z);
		foreach($players as $player) {
			if($player->getBoundingBox()->intersectsWith($boundingBox)){
				$entityCollide = true;
				break;
			}
		}

		if(!$entityCollide){
			foreach($this->mob->getViewers() as $ent){
				if($ent === $this->mob) continue;

				if($ent->getId() > $this->mob->getId() /* && TODO $this->mob->IsColliding(bbox, ent) */) {
					if($this->mob->getMotion()->equals(new Vector3()) && $this->random->nextBoundedInt(1000) == 0){
						break;
					}
					$entityCollide = true;
					break;
				}
			}
		}

		$block = $level->getBlock($coord);
		$blockUp = $block->getSide(Vector3::SIDE_UP);
		$blockUpUp = $block->getSide(Vector3::SIDE_UP, 2);

		$colliding = $block->isSolid() || ($this->mob->height >= 1 && $blockUp->isSolid());
		if(!$colliding && !$entityCollide){
			$velocity = $direction->multiply($speedFactor);

			if($this->mob->getMotion()->multiplyVector(new Vector3(1, 0, 1))->length() < $velocity->length()){
				$this->mob->setMotion($this->mob->getMotion()->add($velocity->subtract($this->mob->getMotion())));
			}else{
				$this->mob->setMotion($velocity);
			}
		}else{
			if(!$entityCollide && !$blockUp->isSolid() && !($this->mob->height > 1 && $blockUpUp->isSolid()) && $this->random->nextBoundedInt(4) != 0){
				$this->mob->motionY = 0.42;
			}else{
				$rot = $this->random->nextBoundedInt(2) == 0 ? $this->random->nextMinMax(45, 180) : $this->random->nextMinMax(-180, -45);
				$this->mob->yaw += $rot;
				$this->mob->pitch += $rot;
				$this->mob->lookAt($this->mob->getDirectionVector());
			}
		}
	}

	public function onEnd(): void{
		$this->timeLeft = $this->duration;
		$this->mob->setMotion($this->mob->getMotion()->multiplyVector(new Vector3(0, 1, 0)));
	}

	protected function areaIsClear(Level $level, AxisAlignedBB $aabb) : bool{
		for($x = $aabb->minX; $x < $aabb->maxX; $x++){
			for($y = $aabb->minY; $y < $aabb->maxY; $y++){
				for($z = $aabb->minZ; $z < $aabb->maxZ; $z++){
					if($level->getBlock(new Vector3($x, $y, $z))->getId() != Block::AIR) return false;
				}
			}
		}

		return true;
	}
}