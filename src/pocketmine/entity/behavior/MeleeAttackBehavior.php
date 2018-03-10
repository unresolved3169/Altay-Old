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

use pocketmine\entity\Attribute;
use pocketmine\entity\Mob;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

class MeleeAttackBehavior extends Behavior{

    /** @var float */
    protected $speedMultiplier;
    /** @var float */
    protected $followRange;

    /** @var int */
    protected $attackCooldown;
    /** @var int */
    protected $delay;
    /** @var Path */
    protected $currentPath;
    /** @var Vector3 */
    protected $lastPlayerPos;

    public function __construct(Mob $mob, float $speedMultiplier, float $followRange){
        parent::__construct($mob);

        $this->speedMultiplier = $speedMultiplier;
        $this->followRange = $followRange;
    }

    public function canStart(): bool{
        $target = $this->mob->getTargetEntity();
        if($target == null) return false;

        $this->currentPath = Path::findPath($this->mob, $target, $this->followRange);

        if(!$this->currentPath->havePath()) return false;

        $this->lastPlayerPos = $target->asVector3();

        return true;
    }

    public function onStart(): void{
        $this->delay = 0;
    }

    public function canContinue(): bool{
        return $this->mob->getTargetEntityId() != null;
    }

    public function onTick(int $tick): void{
        $target = $this->mob->getTargetEntity();
        if($target == null) return;

        $distanceToPlayer = $this->mob->distance($target);

        --$this->delay;

        $deltaDistance = $this->lastPlayerPos->distance($target);

        $canSee = $this->mob->canSeeEntity($target);

        if($canSee || $this->delay <= 0 || $deltaDistance > 1 || $this->random->nextFloat() < 0.05){
            $this->currentPath = Path::findPath($this->mob, $target, $this->followRange);
            $this->lastPlayerPos = $target->asVector3();

            $this->delay = 4 + $this->random->nextBoundedInt(7);

            if($distanceToPlayer > 32){
                $this->delay += 10;
            }elseif($distanceToPlayer > 16){
                $this->delay += 5;
            }

            if(!$this->currentPath->havePath()){
                $this->delay += 15;
            }
        }

        // Movement
        if($this->currentPath->havePath()){
            $next = $this->currentPath->getNextTile($this->mob);
            if($next !== null){
                $this->mob->lookAt(new Vector3($next->x + 0.5, $this->mob->y, $next->y + 0.5));
                $this->mob->moveForward($this->speedMultiplier);
            } // else something is really wrong
        }else{
            $this->mob->resetMotion();
        }

        $this->mob->lookAt($target);

        $this->attackCooldown = max($this->attackCooldown - 1, 0);
        if($this->attackCooldown <= 0 && $distanceToPlayer < $this->getAttackReach()){
            $damage = $this->mob->getAttributeMap()->getAttribute(Attribute::ATTACK_DAMAGE)->getValue();
            $target->attack(new EntityDamageByEntityEvent($this->mob, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage));
            $this->attackCooldown = 20;
        }
    }

    public function getAttackReach() : float{
        return $this->mob->width * 2.0 + $this->mob->getTargetEntity()->width;
    }

    public function onEnd() : void{
        $this->mob->resetMotion();
        $this->mob->pitch = 0;
        $this->attackCooldown = $this->delay = 0;
        $this->currentPath = null;
    }

}