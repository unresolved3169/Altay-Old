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

namespace pocketmine\entity;

use pocketmine\entity\behavior\{
	Behavior, BehaviorTask
};
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Utils;
use pocketmine\entity\pathfinder\EntityNavigator;

abstract class Mob extends Living{

	/** @var array */
	protected $behaviorTasks = [];
	/** @var EntityNavigator */
	protected $navigator;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->setImmobile(true);
	}

	protected function initEntity() : void{
		parent::initEntity();

		$this->jumpVelocity = $this->jumpVelocity + ($this->width / 10) + $this->getAdditionalJumpVelocity(); // hmmmmmm
		$this->navigator = new EntityNavigator($this);
		
		foreach($this->getDefaultBehaviors() as $behaviors){
			if(is_array($behaviors)){
				$this->addBehaviorTask($behaviors);
			}else{
				throw new \RuntimeException("Behaviors must be an array");
			}
		}
	}

	/**
	 * @param int $diff
	 * @return bool
	 */
	public function entityBaseTick(int $diff = 1) : bool{
		$update = parent::entityBaseTick($diff);
		foreach($this->behaviorTasks as $task){
			$task->checkBehaviors();
		}
		
		return $update;
	}
	
	/**
	 * @return Behavior[][]
	 */
	protected function getDefaultBehaviors() : array{
		return [];
	}
	
	public function addBehaviorTask(array $behaviors) : void{
		if(Utils::validateObjectArray($behaviors, Behavior::class)){
			$this->behaviorTasks[] = new BehaviorTask($behaviors);
		}
	}
	
	public function getBehaviorTask(int $index) : ?BehaviorTask{
		return $this->behaviorTasks[$index] ?? null;
	}
	
	public function removeBehaviorTask(int $index) : void{
	 unset($this->behaviorTasks[$index]);
	}

	/**
	 * @param float $spm
	 * @return bool
	 */
	public function moveForward(float $spm) : bool{
		$sf = $this->getMovementSpeed() * $spm * 0.7;
		$level = $this->level;
		$dir = $this->getDirectionVector();
		$dir->y = 0;

		$boundingBox = (clone $this->getBoundingBox())->offsetBy($dir->multiply($sf));
		$entityCollide = count($this->level->getCollidingEntities($boundingBox, $this)) > 0;

		$coord = $this->add($dir->multiply($sf)->add($dir->multiply($this->width * 0.5)));

		$block = $level->getBlock($coord);
		$blockUp = $block->getSide(Vector3::SIDE_UP);
		$blockUpUp = $block->getSide(Vector3::SIDE_UP, 2);

		$collide = $block->isSolid() || ($this->height >= 1 and $blockUp->isSolid());

		if(!$collide and !$entityCollide){
			$blockDown = $block->getSide(Vector3::SIDE_DOWN);
			if (!$this->onGround && !$blockDown->isSolid()) return false;

			$velocity = $dir->multiply($sf);
			$entityVelocity = $this->getMotion();
			$entityVelocity->y = 0;

			$m = $entityVelocity->length() < $velocity->length() ? $this->getMotion()->add($velocity->subtract($this->getMotion())) : $velocity;
			$this->setMotion($m);
			
			return true;
		}else{
			if($this->canClimb() and !$entityCollide){
				$this->setMotion(new Vector3(0,0.2,0));
				return true;
			}elseif(!$entityCollide and !$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())){
				if($this->onGround and $this->motionY === 0){
					$this->server->getLogger()->debug("Jump Velocity: ".$this->getJumpVelocity());
					$this->motionY += $this->getJumpVelocity(); // shortcut jump
					return true;
				}
			}else{
				$this->motionX = $this->motionZ = 0;
			}
		}
		return false;
	}

	/**
	 * @return EntityNavigator
	 */
	public function getNavigator() : EntityNavigator{
		return $this->navigator;
	}

	public function getAdditionalJumpVelocity() : float{
		return 0.01;
	}

	public function hasEntityCollisionUpdate(): bool{
		return true;
 }
}
