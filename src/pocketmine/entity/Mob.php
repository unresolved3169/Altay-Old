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
use pocketmine\entity\behavior\Behavior;
use pocketmine\utils\Random;

abstract class Mob extends Living{

    /** @var array */
    protected $behaviors = [];
    /** @var bool */
    protected $behaviorsEnabled = true; // test
    /** @var Behavior|null */
    protected $currentBehavior = null;
    /** @var int */
    protected $jumpCooldown = 0;

    protected function initEntity(){
        parent::initEntity();

        $this->behaviors = $this->getNormalBehaviors();
    }

    public function getReadyBehavior(): ?Behavior{
        foreach($this->behaviors as $index => $behavior){
            if($behavior == $this->currentBehavior){
                if($behavior->canContinue()){
                    return $behavior;
                }
                $behavior->onEnd();
                $this->currentBehavior = null;
            }
            if($behavior->canStart()){
                if($this->currentBehavior == null or (array_search($this->currentBehavior, $this->behaviors)) > $index){
                    if($this->currentBehavior != null){
                        $this->currentBehavior->onEnd();
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
            $this->currentBehavior = $this->getReadyBehavior();
            if($this->currentBehavior instanceof Behavior){
                $this->currentBehavior->onTick($tick);
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

    public function moveForward(float $spm) : void{
        if($this->jumpCooldown > 0){
            $this->jumpCooldown--;
        }

        $speedFactor = $this->getMovementSpeed() * $spm * 0.7;
        $level = $this->level;
        $dir = $this->getDirectionVector()->normalize();

        $entityCollide = false;
        $boundingBox = $this->getBoundingBox()->offset($dir * $speedFactor, $dir * $speedFactor, $dir * $speedFactor);

        $players = $level->getPlayers();
        foreach($players as $player){
            $bbox = $boundingBox->grow(0.15, 0.15, 0.15);
            if ($player->getBoundingBox()->intersectsWith($bbox)) {
                $entityCollide = true;
                break;
            }
        }

        if(!$entityCollide){
            $bbox = $boundingBox->grow(0.3, 0.3, 0.3);
            foreach($this->getViewers() as $ent){
                if($ent === $this) continue;

                if($ent->getId() < $this->getId() && $this->isColliding($bbox, $ent)){
                    if($this->getMotion()->equals(new Vector3()) && (new Random())->nextBoundedInt(1000) == 0){
                        break;
                    }
                    $entityCollide = true;
                    break;
                }
            }
        }

        $coord = $this->add($dir->multiply($speedFactor)->multiply((float)($this->length() * 0.5))); // TODO : Add length
        $block = $this->level->getBlock($coord);
        $blockUp = $block->getSide(Vector3::SIDE_UP);
        $blockUpUp = $blockUp->getSide(Vector3::SIDE_UP);

        $colliding = $block->isSolid() || ($this->height >= 1 && $blockUp->isSolid());

        if(!$colliding && !$entityCollide){
            $blockDown = $block->getSide(Vector3::SIDE_DOWN);
            if(!$this->isOnGround() && !$blockDown->isSolid()) return;

            $velocity = $dir->multiply($speedFactor);
            if(($this->getMotion()->multiplyVector(new Vector3(1, 0, 1))->length() < $velocity->length())){
                $this->setMotion($this->getMotion()->add($velocity->subtract($this->getMotion())));
            }else{
                $this->setMotion($velocity);
            }
        }else{
            if($this->canClimb() && !$entityCollide){
                $this->setMotion(new Vector3(0, 0.2, 0));
            }elseif(!$entityCollide && !$blockUp->isSolid() && !($this->height > 1 && $blockUpUp->isSolid())){
                // Above is wrong. Checks the wrong block in the wrong way.

                if($this->isOnGround() && $this->jumpCooldown <= 0){
                    $this->jumpCooldown = 10;
                    $this->motionY += 0.42;
                }
            }else{
                $this->motionX = $this->motionZ = 0;
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