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
use pocketmine\math\Vector3;

class MoveTowardsRestrictionBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier;
	/** @var Vector3 */
	protected $movePos;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0){
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->setMutexBits(1);
	}

	public function shouldExecute() : bool{
		if($this->entity->isWithinHomeDistanceCurrentPosition()){
			return false;
		}else{
			$blockpos = $this->entity->getHomePosition();
			$vec3 = RandomPositionGenerator::findRandomTargetBlockTowards($this->entity, 16, 7, $blockpos);

			if($vec3 == null) {
				return false;
			}else{
				$this->movePos = $vec3;
				return true;
			}
		}
	}

	public function continueExecuting() : bool{
		return !$this->entity->getNavigator()->noPath();
	}

	public function startExecuting() : void{
		$this->entity->getNavigator()->tryMoveToXYZ($this->movePos->x, $this->movePos->y, $this->movePos->z, $this->speedMultiplier);
	}
}