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

namespace pocketmine\entity\object;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\Player;

class ExperienceOrb extends Entity{
	public const NETWORK_ID = self::XP_ORB;

	public const TAG_VALUE_PC = "Value"; //short
	public const TAG_VALUE_PE = "experience value"; //int (WTF?)

	/**
	 * Max distance an orb will follow a player across.
	 */
	public const MAX_TARGET_DISTANCE = 8.0;

	/**
	 * Split sizes used for dropping experience orbs.
	 */
	public const ORB_SPLIT_SIZES = [2477, 1237, 617, 307, 149, 73, 37, 17, 7, 3, 1]; //This is indexed biggest to smallest so that we can return as soon as we found the biggest value.

	/**
	 * Returns the largest size of normal XP orb that will be spawned for the specified amount of XP. Used to split XP
	 * up into multiple orbs when an amount of XP is dropped.
	 *
	 * @param int $amount
	 *
	 * @return int
	 */
	public static function getMaxOrbSize(int $amount) : int{
		foreach(self::ORB_SPLIT_SIZES as $split){
			if($amount >= $split){
				return $split;
			}
		}

		return 1;
	}

	/**
	 * Splits the specified amount of XP into an array of acceptable XP orb sizes.
	 *
	 * @param int $amount
	 *
	 * @return int[]
	 */
	public static function splitIntoOrbSizes(int $amount) : array{
		$result = [];

		while($amount > 0){
			$size = self::getMaxOrbSize($amount);
			$result[] = $size;
			$amount -= $size;
		}

		return $result;
	}

	public $height = 0.25;
	public $width = 0.25;

	public $gravity = 0.04;
	public $drag = 0.02;

	/**
	 * @var int
	 * Ticker used for determining interval in which to look for new target players.
	 */
	protected $lookForTargetTime = 0;

	/**
	 * @var int|null
	 * Runtime entity ID of the player this XP orb is targeting.
	 */
	protected $targetPlayerRuntimeId = null;

	protected function initEntity() : void{
		parent::initEntity();

		$this->age = $this->namedtag->getShort("Age", 0);

		$value = 0;
		if($this->namedtag->hasTag(self::TAG_VALUE_PC, ShortTag::class)){ //PC
			$value = $this->namedtag->getShort(self::TAG_VALUE_PC);
		}elseif($this->namedtag->hasTag(self::TAG_VALUE_PE, IntTag::class)){ //PE save format
			$value = $this->namedtag->getInt(self::TAG_VALUE_PE);
		}

		$this->setXpValue($value);
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setShort("Age", $this->age);

		$this->namedtag->setShort(self::TAG_VALUE_PC, $this->getXpValue());
		$this->namedtag->setInt(self::TAG_VALUE_PE, $this->getXpValue());
	}

	public function getXpValue() : int{
		return $this->propertyManager->getInt(self::DATA_EXPERIENCE_VALUE) ?? 0;
	}

	public function setXpValue(int $amount) : void{
		if($amount <= 0){
			throw new \InvalidArgumentException("XP amount must be greater than 0, got $amount");
		}
		$this->propertyManager->setInt(self::DATA_EXPERIENCE_VALUE, $amount);
	}

	public function hasTargetPlayer() : bool{
		return $this->targetPlayerRuntimeId !== null;
	}

	public function getTargetPlayer() : ?Human{
		if($this->targetPlayerRuntimeId === null){
			return null;
		}

		$entity = $this->server->findEntity($this->targetPlayerRuntimeId, $this->level);
		if($entity instanceof Human){
			return $entity;
		}

		return null;
	}

	public function setTargetPlayer(?Human $player) : void{
		$this->targetPlayerRuntimeId = $player ? $player->getId() : null;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->age > 6000){
			$this->flagForDespawn();
			return true;
		}

		$currentTarget = $this->getTargetPlayer();
		if($currentTarget !== null and $currentTarget->distanceSquared($this) > self::MAX_TARGET_DISTANCE ** 2){
		    $currentTarget = null;
		}

		if($this->lookForTargetTime >= 20){
			if($currentTarget === null){
				$this->setTargetPlayer(null);

				$newTarget = $this->level->getNearestEntity($this, self::MAX_TARGET_DISTANCE, Human::class);

				if($newTarget instanceof Human and !($newTarget instanceof Player and $newTarget->isSpectator())){
					$currentTarget = $newTarget;
				}
			}

			$this->lookForTargetTime = 0;
		}else{
			$this->lookForTargetTime += $tickDiff;
		}

		$this->setTargetPlayer($currentTarget);

		if($currentTarget !== null){
			$vector = $currentTarget->subtract($this)->add(0, $currentTarget->getEyeHeight() / 2, 0)->divide(self::MAX_TARGET_DISTANCE);

			$distance = $vector->length();
			$oneMinusDistance = (1 - $distance) ** 2;

			if($oneMinusDistance > 0){
				$this->motion->x += $vector->x / $distance * $oneMinusDistance * 0.2;
				$this->motion->y += $vector->y / $distance * $oneMinusDistance * 0.2;
				$this->motion->z += $vector->z / $distance * $oneMinusDistance * 0.2;
			}

			if($currentTarget->canPickupXp() and $this->boundingBox->intersectsWith($currentTarget->getBoundingBox())){
				$this->flagForDespawn();

				$currentTarget->addXp($this->getXpValue());
				$currentTarget->resetXpCooldown();

				//TODO: check Mending enchantment
			}
		}

		return $hasUpdate;
	}

	protected function tryChangeMovement() : void{
		$this->checkObstruction($this->x, $this->y, $this->z);
		parent::tryChangeMovement();
	}

	public function canBeCollidedWith(): bool{
		return false;
	}
}