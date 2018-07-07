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

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\entity\behavior\BehaviorPool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\pathfinder\EntityNavigator;

abstract class Mob extends Living{

    /** @var BehaviorPool */
    protected $behaviorPool;
    /** @var BehaviorPool */
    protected $targetBehaviorPool;
    /** @var EntityNavigator */
    protected $navigator;
    /** @var Vector3 */
    protected $lookPosition;

    protected $seenEntities = [];
    protected $unseenEntities = [];

    /** @var bool */
    protected $aiEnabled = false;

    public function __construct(Level $level, CompoundTag $nbt){
        parent::__construct($level, $nbt);
        $this->setImmobile(true);
    }

    /**
     * @return bool
     */
    public function isAiEnabled(): bool
    {
        return $this->aiEnabled;
    }

    /**
     * @param bool $aiEnabled
     */
    public function setAiEnabled(bool $aiEnabled): void
    {
        $this->aiEnabled = $aiEnabled;
    }

    protected function initEntity() : void{
        parent::initEntity();

        $this->targetBehaviorPool = new BehaviorPool();
        $this->behaviorPool = new BehaviorPool();
        $this->navigator = new EntityNavigator($this);

        $this->addBehaviors();
    }

    public function onUpdate(int $tick) : bool{
        if($this->closed) return false;

        if($this->isAlive() and $this->aiEnabled) {
            $this->onBehaviorUpdate($tick);
        }

        return parent::onUpdate($tick);
    }

    protected function onBehaviorUpdate(int $tick) : void{
        $this->targetBehaviorPool->onUpdate($tick);
        $this->behaviorPool->onUpdate($tick);

        $this->navigator->onNavigateUpdate($tick);

        if($this->getLookPosition() !== null){
            $this->lookAt($this->getLookPosition(), true);
            $this->lookPosition = null;
        }

        $this->handleWaterMovement();

        $this->clearSightCache();
    }

    public function canSeeEntity(Entity $target) : bool{
        // TODO: wtf?? why this always return false!? fix this.
        /*if(in_array($target->getId(), $this->unseenEntities)){
            return false;
        }elseif(in_array($target->getId(), $this->seenEntities)){
            return true;
        }else{
            $blocks = VoxelRayTrace::betweenPoints($this->floor(), $target->floor());
            $canSee = $blocks === null or count(array_filter($blocks, function (Block $b){return $b->isSolid();})) === 0;
            if($canSee){
                $this->seenEntities[] = $target->getId();
            }else{
                $this->unseenEntities[] = $target->getId();
            }

            return $canSee;
        }*/

        return true;
    }

    public function clearSightCache() : void{
        $this->seenEntities = [];
        $this->unseenEntities = [];
    }

    public function getLookPosition() : ?Vector3{
        return $this->lookPosition;
    }

    public function setLookPosition(?Vector3 $pos) : void{
        $this->lookPosition = $pos;
    }

    protected function addBehaviors() : void{

    }

    public function getBehaviorPool() : BehaviorPool{
        return $this->behaviorPool;
    }

    public function getTargetBehaviorPool() : BehaviorPool{
        return $this->targetBehaviorPool;
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