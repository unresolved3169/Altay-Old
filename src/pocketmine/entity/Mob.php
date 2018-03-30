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
	Behavior, TargetBehavior
};
use pocketmine\entity\behavior\EntityNavigator;
use pocketmine\math\Vector3;

abstract class Mob extends Living{

	public $height = 0.6;
	public $width = 1.8;

	/** @var array */
	protected $behaviors = [], $targetBehaviors = [], $behaviorTasks = [];
	/** @var bool */
	protected $behaviorsEnabled = true; // test
	/** @var Behavior|null */
	protected $currentBehavior = null, $currentTargetBehavior = null;
	/** @var EntityNavigator */
	protected $navigator;

	protected function initEntity(){
		parent::initEntity();

		$this->behaviors = $this->getNormalBehaviors();
		$this->targetBehaviors = $this->getTargetBehaviors();
		$this->navigator = new EntityNavigator($this);
		$this->setImmobile(false);
	}

	/**
	 * @param array $behaviors
	 * @param null|Behavior $currentBehavior
	 * @return null|Behavior
	 */
	public function getReadyBehavior(array $behaviors, ?Behavior $currentBehavior = null): ?Behavior{
		foreach($behaviors as $index => $behavior){
			if($behavior == $currentBehavior){
				if($behavior->canContinue()){
					return $behavior;
				}
				$behavior->onEnd();
				$currentBehavior = null;
			}
			if($behavior->canStart()){
				if($currentBehavior == null or (array_search($currentBehavior, $behaviors)) > $index){
					if($currentBehavior != null){
						$currentBehavior->onEnd();
					}
					$behavior->onStart();
					return $behavior;
				}
			}
		}
		return null;
	}

	/**
	 * @param int $tick
	 * @return bool
	 */
	public function onUpdate(int $tick): bool{
		if($this->isAlive() and $this->behaviorsEnabled){
			$this->currentBehavior = $this->getReadyBehavior($this->behaviors, $this->currentBehavior);
			if($this->currentBehavior instanceof Behavior){
				$this->currentBehavior->onTick($tick);
			}
			$this->currentTargetBehavior = $this->getReadyBehavior($this->targetBehaviors, $this->currentTargetBehavior);
			if($this->currentTargetBehavior instanceof TargetBehavior){
				$this->currentTargetBehavior->onTick($tick);
			}
			foreach($this->getBehaviorTasks() as $task){
				$task->onExecute();
			}
		}

		return parent::onUpdate($tick);
	}

	/**
	 * @param int $index
	 * @param Behavior $b
	 */
	public function setBehavior(int $index, Behavior $b) : void{
		if($b instanceof TargetBehavior){
			$this->targetBehaviors[$index] = $b;
		}else {
			$this->behaviors[$index] = $b;
		}
	}

	/**
	 * @param int $key
	 */
	public function removeBehavior(int $key) : void{
		unset($this->behaviors[$key]);
	}

	/**
	 * @param int $key
	 */
	public function removeTargetBehavior(int $key) : void{
		unset($this->targetBehaviors[$key]);
	}

	/**
	 * @return bool
	 */
	public function isBehaviorsEnabled() : bool{
		return $this->behaviorsEnabled;
	}

	/**
	 * @param bool $value
	 */
	public function setBehaviorsEnabled(bool $value = true) : void{
		$this->behaviorsEnabled = $value;
	}

	/**
	 * @return Behavior[]
	 */
	protected function getNormalBehaviors() : array{
		return [];
	}

	protected function getBehaviorTasks() : array{
		return [];
	}

	/**
	 * @return array
	 */
	protected function getTargetBehaviors() : array{
		return [];
	}

	/**
	 * @param float $spm
	 */
	public function moveForward(float $spm) : void{
		$sf = $this->getMovementSpeed() * $spm * 0.7;
		$level = $this->level;
		$dir = $this->getDirectionVector()->normalize();
		$dir->y = 0;

		$coord = $this->add($dir->multiply($sf)->add($dir->multiply($this->width * 0.5)));

		$block = $level->getBlock($coord);
		$blockUp = $level->getBlock($coord->add(0,1,0));
		$blockUpUp = $level->getBlock($coord->add(0,2,0));

		$collide = $block->isSolid() or ($this->height >= 1 and $blockUp->isSolid());
		/*$xxx = $dir->multiply($sf);
		$boundingBox = $this->getBoundingBox()->offset($xxx->x, $xxx->y, $xxx->z);
		$entityCollide = count($this->level->getCollidingEntities($boundingBox, $this)) > 0;

		if(!$entityCollide){
			$bbox = $boundingBox->addCoord(0.3,0.3, 0.3);

			foreach($this->level->getEntities() as $entry){
				if($entry == $this) continue;

				if($entry->getId() < $this->getId() and $bbox->isVectorInside($entry->asVector3())){
					if($this->motionX === 0 and $this->motionY === 0 and $this->motionZ === 0 and $this->level->getRandom()->nextBoundedInt(1000) === 0){
						break;
					}

					$entityCollide = true;
					break;
				}
			}
		}*/
		$entityCollide = false;

		if(!$collide and !$entityCollide){
			$blockDown = $block->getSide(0);
			if($this->onGround or $blockDown->isSolid()){
				$velocity = $dir->multiply($sf);
				$entityVelocity = $this->getMotion();
				$entityVelocity->y = 0;

				$m = $entityVelocity->length() < $velocity->length() ? $entityVelocity->add($velocity->subtract($entityVelocity)) : $velocity;
				$this->setMotion($m);
			}
		}else{
			if($this->canClimb() and !$entityCollide){
				$this->setMotion(new Vector3(0,0.2,0));
			}elseif(!$entityCollide and !$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())){
				if($this->onGround and $this->motionY === 0){
					$this->jump();
				}
			}else{
				$this->motionX = 0;
				$this->motionZ = 0;
			}
		}
	}

	/**
	 * @return EntityNavigator
	 */
	public function getNavigator() : EntityNavigator{
		return $this->navigator;
	}
}
