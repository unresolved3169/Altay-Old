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

		$this->jumpVelocity = $this->jumpVelocity + ($this->width / 10) + $this->getAdditionalJumpVelocity(); // hmmmmmm
		$this->behaviors = $this->getNormalBehaviors();
		$this->targetBehaviors = $this->getTargetBehaviors();
		$this->navigator = new EntityNavigator($this);
		$this->setImmobile(); // for disable client-side mobai
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
			if (!$this->onGround && !$blockDown->isSolid()) return;

			$velocity = $dir->multiply($sf);
			$entityVelocity = $this->getMotion();
			$entityVelocity->y = 0;

			$m = $entityVelocity->length() < $velocity->length() ? $this->getMotion()->add($velocity->subtract($this->getMotion())) : $velocity;
			$this->setMotion($m);
		}else{
			if($this->canClimb() and !$entityCollide){
				$this->setMotion(new Vector3(0,0.2,0));
			}elseif(!$entityCollide and !$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())){
				if($this->onGround and $this->motionY === 0){
					$this->server->getLogger()->debug("Jump Velocity: ".$this->getJumpVelocity());
					$this->motionY += $this->getJumpVelocity(); // shortcut jump
				}
			}else{
				//$this->motionX = $this->motionZ = 0;
			}
		}
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

	public function hasEntityCollisionUpdate(): bool
    {
        return true;
    }
}