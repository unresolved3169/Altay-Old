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

namespace pocketmine\entity\behaviors;

use pocketmine\entity\Mob;

abstract class Behavior{

	/**
	 * A bitmask telling which other tasks may not run concurrently. The test is a simple bitwise AND - if it yields
	 * zero, the two tasks may run concurrently, if not - they must run exclusively from each other.
	 *
	 * @var int
	 */
	private $mutexBits;

	/** @var Mob */
	protected $entity;

	public function __construct(Mob $mob){
		$this->entity = $mob;
	}

	/**
	 * Returns whether the EntityAIBase should begin execution.
	 */
	public abstract function shouldExecute() : bool;

	/**
	 * Returns whether an in-progress EntityAIBase should continue executing
	 */
	public function continueExecuting() : bool{
		return $this->shouldExecute();
	}

	/**
	 * Determine if this AI Task is interruptible by a higher (= lower value) priority task. All vanilla AITask have
	 * this value set to true.
	 */
	public function isInterruptible() : bool{
		return true;
	}

	/**
	 * Execute a one shot task or start executing a continuous task
	 */
	public function startExecuting() : void{}

	/**
	 * Resets the task
	 */
	public function resetTask() : void{}

	/**
	 * Updates the task
	 */
	public function updateTask() : void{}

	/**
	 * Sets a bitmask telling which other tasks may not run concurrently. The test is a simple bitwise AND - if it
	 * yields zero, the two tasks may run concurrently, if not - they must run exclusively from each other.
	 *
	 * @param int $mutexBits
	 */
	public function setMutexBits(int $mutexBits): void{
		$this->mutexBits = $mutexBits;
	}

	/**
	 * Get a bitmask telling which other tasks may not run concurrently. The test is a simple bitwise AND - if it yields
	 * zero, the two tasks may run concurrently, if not - they must run exclusively from each other.
	 */
	public function getMutexBits() : int{
		return $this->mutexBits;
	}
}