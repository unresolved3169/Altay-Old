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

use pocketmine\entity\behaviors\Behavior;
use pocketmine\entity\behaviors\EntityAITask;
use pocketmine\entity\behaviors\EntityJumpHelper;
use pocketmine\entity\behaviors\EntityMoveHelper;
use pocketmine\entity\behaviors\pathfinding\PathNavigate;
use pocketmine\entity\behaviors\pathfinding\PathNavigateGround;

abstract class Mob extends Living{

    /** @var EntityMoveHelper */
    protected $moveHelper;
    /** @var EntityJumpHelper */
    protected $jumpHelper;

    /** @var float */
    protected $moveForward;
    /** @var float */
    protected $landMovementFactor;
    /** @var PathNavigate */
    private $navigator;
    /** @var bool */
    protected $isJumping;

	/** @var EntityAITask */
	protected $behaviors;
	/** @var EntityAITask */
	protected $targetBehaviors;

	protected function initEntity(){
        $this->moveHelper = new EntityMoveHelper($this);
        $this->jumpHelper = new EntityJumpHelper($this);
        $this->navigator = new PathNavigateGround($this);
        $this->setMovementSpeed($this->getSpeed());

        foreach($this->getBehaviors() as $priority => $behavior)
        	$this->behaviors->addTask($priority, $behavior);

		foreach($this->getTargetBehaviors() as $priority => $behavior)
			$this->targetBehaviors->addTask($priority, $behavior);

        parent::initEntity();
    }

	/**
	 * @return Behavior[]
	 */
	public function getBehaviors() : array{
		return [];
	}

	/**
	 * @return Behavior[]
	 */
	public function getTargetBehaviors() : array{
		return  [];
	}

    public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->targetBehaviors->onUpdateTasks();
		$this->behaviors->onUpdateTasks();
		$this->navigator->onUpdateNavigation();
		$this->updateBehaviors();
		$this->moveHelper->onUpdateMoveHelper();
		$this->jumpHelper->doJump();
		//$this->updateMovement();

		return $hasUpdate;
	}

	protected function updateBehaviors(){}

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

	/**
	 * @return PathNavigate|PathNavigateGround
	 */
	public function getNavigator() : PathNavigate{
		return $this->navigator;
	}

    public function getSpeed() : float{
        return 0.1;
    }
}