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

use pocketmine\entity\behaviors\EntityJumpHelper;
use pocketmine\entity\behaviors\EntityMoveHelper;

abstract class Mob extends Living{

    /** @var EntityMoveHelper */
    protected $moveHelper;
    /** @var EntityJumpHelper */
    protected $jumpHelper;

    /** @var float */
    protected $moveForward;
    /** @var float */
    protected $landMovementFactor;
    /** @var bool */
    protected $isJumping;

    protected function initEntity(){
        $this->moveHelper = new EntityMoveHelper($this);
        $this->jumpHelper = new EntityJumpHelper($this);
        parent::initEntity();
    }

    public function setJumping(bool $jump = true) : void{
        $this->isJumping = $jump;
    }

    public function setMoveForward(float $moveForward) : void{
        $this->moveForward = $moveForward;
    }

    public function setAIMoveSpeed(float $speed) : void{
        $this->landMovementFactor = $speed;
        $this->moveForward = $speed;
    }

    public function getAIMoveSpeed() : float{
        return $this->landMovementFactor;
    }

    public function getMoveHelper() : EntityMoveHelper{
        return $this->moveHelper;
    }

    public function getJumpHelper() : EntityJumpHelper{
        return $this->jumpHelper;
    }

    public function getMaxFallHeight() : int{
        return 3;
    }

    public function setDefaultMovementSpeed(float $speed){
        $this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setDefaultValue($speed);
    }

    public function setDefaultAttackDamage(float $attackDamage){
        $this->getAttributeMap()->getAttribute(Attribute::ATTACK_DAMAGE)->setDefaultValue($attackDamage);
    }
}