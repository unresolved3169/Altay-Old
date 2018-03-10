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

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\entity\behavior\{Behavior, TargetBehavior};
use pocketmine\utils\Random;

abstract class Mob extends Living{

    public $height = 0.6;
    public $width = 1.8;

    /** @var array */
    protected $behaviors = [], $targetBehaviors = [];
    /** @var bool */
    protected $behaviorsEnabled = true; // test
    /** @var Behavior|null */
    protected $currentBehavior = null, $currentTargetBehavior = null;
    /** @var int */
    protected $jumpCooldown = 0;

    protected function initEntity(){
        parent::initEntity();

        $this->behaviors = $this->getNormalBehaviors();
        $this->targetBehaviors = $this->getTargetBehaviors();
    }

    public function getReadyBehavior(array $behaviors, ?Behavior $currentBehavior = null): ?Behavior{
        foreach($behaviors as $index => $behavior){
            if($behavior == $this->currentBehavior){
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
        }

        return parent::onUpdate($tick);
    }

    public function getCurrentBehavior() : ?Behavior{
        return $this->currentBehavior;
    }

    public function setCurrentBehavior(Behavior $behavior = null) : void{
        $this->currentBehavior = $behavior;
    }

    public function addBehavior(Behavior $behavior) : void{
        $this->behaviors[] = $behavior;
    }

    public function setBehavior(int $index, Behavior $b) : void{
        $this->behaviors[$index] = $b;
    }

    public function removeBehavior(int $key) : void{
        unset($this->behaviors[$key]);
    }

    public function isBehaviorsEnabled() : bool{
        return $this->behaviorsEnabled;
    }

    public function setBehaviorsEnabled(bool $value = true) : void{
        $this->behaviorsEnabled = $value;
    }

    public function getBehaviors() : array{
        return $this->behaviors;
    }

    /**
     * @return Behavior[]
     */
    protected function getNormalBehaviors() : array{
        return [];
    }
    
    protected function getTargetBehaviors() : array{
        return [];
    }

    public function moveForward(float $spm) : void{
		$sf = $this->getMovementSpeed() * $spm * 0.7;
		$level = $this->level;
		$dir = $this->getDirectionVector()->normalize();
		$dir->y = 0;
		
		$entityCollide = $level->getCollidingEntities($this->getBoundingBox()->grow(0.15,0.15,0.15), $this);
		$coord = $this->add($dir->multiply($sf)->add($dir->multiply($this->width * 0.5)));
		
		$block = $level->getBlock($coord);
		$blockUp = $level->getBlock($coord->add(0,1,0));
		$blockUpUp = $level->getBlock($coord->add(0,2,0));
		
		$collide = $block->isSolid() or ($this->height >= 1 and $blockUp->isSolid());
		
		if(!$collide and !$entityCollide){
			$blockDown = $level->getBlock($coord->add(0,-1,0));
			
			if($this->isOnGround() and $blockDown->isSolid()){
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
			if($this->canClimb() and !$entityCollide){
				$this->setMotion(new Vector3(0,0.2,0));
			}elseif(!$entityCollide and !$blockUp->isSolid() and !($this->height > 1 and $blockUpUp->isSolid())){
				if($this->isOnGround() and $this->motionY === 0){
					$this->jump();
				}
			}else{
				$this->motionX = 0;
				$this->motionZ = 0;
			}
		}
	}
    public function isColliding(AxisAlignedBB $aabb, Entity $entity){
        if (!$this->compare((int) $this->x, (int) $entity->x, 4)) return false;
        if (!$this->compare((int) $this->z, (int) $entity->z, 4)) return false;
        if (!$aabb->intersectsWith($entity->getBoundingBox())) return false;

        return true;
    }

    public function compare(int $a, int $b, int $m) : bool{
        $a = $a >> $m;
        $b = $b >> $m;
        return $a == $b || $a == ($b - 1) || $a == ($b + 1);
    }
}