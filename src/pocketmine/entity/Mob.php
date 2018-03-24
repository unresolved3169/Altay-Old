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

use pocketmine\entity\behavior\EntityNavigator;
use pocketmine\math\Vector3;
use pocketmine\entity\behavior\{Behavior, TargetBehavior};

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
    $onGround = $block->getSide(0) !== 0 or $block->getSide(0,2) !== 0;
		
		$collide = $block->isSolid() or ($this->height >= 1 and $blockUp->isSolid());
		
		if(!$collide){
			if($onGround){
				$velocity = $dir->multiply($sf);
				$entityVelocity = $this->getMotion();
				$entityVelocity->y = 0;
				if($entityVelocity->length() < $velocity->length()){
					$this->setMotion($entityVelocity->add($velocity->subtract($entityVelocity->x, $entityVelocity->y, $entityVelocity->z)));
				}else{
					$this->setMotion($velocity);
				}
			}
		}else{
			if($this->canClimb()){
				$this->setMotion(new Vector3(0,0.2,0));
			}elseif(!$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())){
				if($onGround){
					$this->jump();
        // $this->moveForward($spm);
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
