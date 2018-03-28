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

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\MainLogger;

class FollowOwnerBehavior extends Behavior{

	/** @var float */
	protected $lookDistance;
	/** @var float */
	protected $speedMultiplier;
	/** @var Path */
	protected $currentPath;

	// TODO : Mob change to Wolf
	public function __construct(Mob $mob, float $lookDistance, float $speedMultiplier){
		parent::__construct($mob);

		$this->lookDistance = $lookDistance;
		$this->speedMultiplier = $speedMultiplier;
	}

	public  function canStart(): bool{
		if(!$this->mob->getGenericFlag(Entity::DATA_FLAG_TAMED)) return false;
		if($this->mob->getOwningEntity() === null) return false;

		return true;
	}

	public function onTick(int $tick) : void{
		/** @var Player $owner */
		$owner = $this->mob->getOwningEntity();
		if ($owner == null) return;

		$distanceToPlayer = $this->mob->distance($owner);

		if($distanceToPlayer < 1.75){
			$this->mob->resetMotion();
			$this->mob->lookAt($owner);
			return;
		}

		if($this->currentPath == null || !$this->currentPath->havePath()){
			MainLogger::getLogger()->debug("Search new solution");
			$this->currentPath = $this->currentPath->findPath($this->mob, $owner);
		}

		if($this->currentPath->havePath()){
			$next = $this->currentPath->getNextTile($this->mob);
			if ($next === null) return;

			$this->mob->lookAt(new Vector3($next->x + 0.5, $this->mob->y, $next->y + 0.5));

			if($distanceToPlayer < 1.75){
				$this->mob->resetMotion();
				$this->currentPath = null;
			}else{

				$m = 2 - $distanceToPlayer;
				$m = ($m <= 0) ? 1 : $m / 2.0;

				$this->mob->moveForward($this->speedMultiplier * $m);
			}
		}else{
			MainLogger::getLogger()->debug("Found no path solution");
			$this->mob->resetMotion();
			$this->currentPath = null;
		}

		$this->mob->lookAt($owner);
	}

	public function onEnd(): void{
		$this->mob->resetMotion();
		$this->mob->pitch = 0;
		$this->currentPath = null;
	}
}