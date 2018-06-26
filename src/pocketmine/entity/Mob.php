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

use pocketmine\entity\behavior\Behavior;
use pocketmine\entity\behavior\BehaviorTask;
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
		
		$this->navigator = new EntityNavigator($this);

		foreach($this->getDefaultBehaviors() as $behaviors){
			if(is_array($behaviors)){
				$this->addBehaviorTask($behaviors);
			}else{
				throw new \RuntimeException("Behaviors must be an array");
			}
		}
	}

	public function entityBaseTick(int $diff = 1) : bool{
	      foreach($this->behaviorTasks as $task){
			   $task->checkBehaviors();
		    }
	      
	      return parent::entityBaseTick($diff);
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

	public function moveForward(float $spm) : bool{
		$sf = $this->getMovementSpeed() * $spm * 0.7;
		$dir = $this->getDirectionVector();
		$dir->y = 0;

		$coord = $this->add($dir->multiply($sf)->add($dir->multiply($this->width * 0.5)));

		$block = $this->level->getBlock($coord);
		$blockUp = $block->getSide(Vector3::SIDE_UP);
		$blockUpUp = $block->getSide(Vector3::SIDE_UP, 2);

		$collide = $block->isSolid() || ($this->height >= 1 and $blockUp->isSolid());

		if(!$collide){
			$blockDown = $block->getSide(Vector3::SIDE_DOWN);
			if (!$this->onGround && !$blockDown->isSolid()) return false;
     
			$velocity = $dir->multiply($sf);
			$entityVelocity = $this->getMotion();
			$entityVelocity->y = 0;

			$m = $entityVelocity->length() < $velocity->length() ? $this->getMotion()->add($velocity->subtract($this->getMotion())) : $this->getMotion();
			$this->setMotion($m);
			return true;
		}else{
			if($this->canClimb()){
				$this->setMotion($this->getMotion()->setComponents(0, 0.2, 0));
				return true;
			}elseif(!$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())){
				$this->motion->y = $this->getJumpVelocity();
				return true;
			}else{
				$this->motion->x = $this->motion->z = 0;
			}
		}
		return false;
	}

	public function getNavigator() : EntityNavigator{
		return $this->navigator;
	}

	public function canBePushed(): bool{
		return true;
	}

	public function setDefaultMovementSpeed(float $value) : void{
		$this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setDefaultValue($value);
	}
}