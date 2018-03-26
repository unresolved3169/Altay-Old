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

use pocketmine\block\Door;
use pocketmine\block\WoodenDoor;
use pocketmine\entity\behaviors\pathfinding\PathNavigateGround;
use pocketmine\entity\Mob;
use pocketmine\math\Vector3;

class DoorInteractBehavior extends Behavior{

	/** @var Vector3 */
	protected $doorPosition;

	/** @var Door */
	protected $doorBlock;

	/**
	 * If is true then the Entity has stopped Door Interaction and completed the task.
	 */
	/** @var bool */
	public $hasStoppedDoorInteraction;
	/** @var float */
	public $x;
	/** @var float */
	public $z;

	public function __construct(Mob $mob){
		parent::__construct($mob);

		if(!($mob->getNavigator() instanceof PathNavigateGround)){
			throw new \InvalidArgumentException("Unsupported mob type for DoorInteractBehavior");
		}
	}

	public function shouldExecute(): bool{
		if(!$this->entity->isCollidedHorizontally){
			return false;
		}else{
			$pathEntity = $this->entity->getNavigator()->getPath();

			if($pathEntity != null && !$pathEntity->isFinished() && $this->entity->getNavigator()->getEnterDoors()){
				for($i = 0; $i < min($pathEntity->getCurrentPathIndex() + 2, $pathEntity->getCurrentPathLength()); ++$i){
					$pathPoint = $pathEntity->getPathPointFromIndex($i);
					$this->doorPosition = $pathPoint->toVector3()->add(0, 1, 0);

					if($this->entity->distanceSquared($this->doorPosition) <= 2.25){
						$this->doorBlock = $this->entity->level->getBlock($this->doorPosition);
						$this->doorBlock = $this->doorBlock instanceof WoodenDoor ? $this->doorBlock : null;

						if($this->doorBlock != null)
							return true;
					}
				}

				$this->doorPosition = $this->entity->getSide(Vector3::SIDE_UP);
				$this->doorBlock = $this->entity->level->getBlock($this->doorPosition);
				$this->doorBlock = $this->doorBlock instanceof WoodenDoor ? $this->doorBlock : null;
				return $this->doorBlock != null;
			}else{
				return false;
			}
		}
	}

	public function continueExecuting() : bool{
		return !$this->hasStoppedDoorInteraction;
	}

	public function startExecuting() : void{
		$this->hasStoppedDoorInteraction = false;
		$this->x = ($this->doorPosition->x + 0.5) - $this->entity->x;
		$this->z = ($this->doorPosition->z + 0.5) - $this->entity->z;
	}

	public function updateTask() : void{
		$x = ($this->doorPosition->x + 0.5) - $this->entity->x;
		$z = ($this->doorPosition->z + 0.5) - $this->entity->z;
		$f = $this->x * $x + $this->z * $z;

		if($f < 0.0)
			$this->hasStoppedDoorInteraction = true;
	}
}