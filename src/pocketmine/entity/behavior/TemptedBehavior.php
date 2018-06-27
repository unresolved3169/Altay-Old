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

use pocketmine\entity\Mob;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\entity\pathfinder\Path;

class TemptedBehavior extends Behavior{
    
	/** @var float */
	protected $speedMultiplier;
	/** @var int[] */
	protected $temptItems;
	/** @var int */
	protected $coolDown = 0;
	/** @var Player */
	protected $temptingPlayer;
	/** @var Vector3 */
	protected $lastPlayerPos;
	/** @var Vector3 */
	protected $originalPos;

	/**
	 * TemptedBehavior constructor.
	 * @param Mob    $mob
	 * @param int[]  $temptItemIds
	 * @param float  $lookDistance
	 * @param float  $speedMultiplier
	 */
	public function __construct(Mob $mob, array $temptItemIds, float $speedMultiplier){
		parent::__construct($mob);

		$this->temptItems = $temptItemIds;
		$this->speedMultiplier = $speedMultiplier;
	}

	public function canStart() : bool{
		if($this->coolDown > 0){
			$this->coolDown--;
			return false;
		}

		/** @var Player|null $player */
		$player = $this->mob->level->getNearestEntity($this->mob, $this->mob->getFollowRange(), Player::class);
		if($player === null) return false;
		$player = $this->containsTempItems($player) ? $player : null;

		if($player === null) return false;

		if($player !== $this->temptingPlayer){
			$this->temptingPlayer = $player;
			$this->lastPlayerPos = $player->asVector3();
			$this->originalPos = $this->mob->asVector3();
		}

		return true;
	}

	public function containsTempItems(Player $player) : bool{
		$handItem = $player->getInventory()->getItemInHand();
		foreach($this->temptItems as $temptItem){
			if($temptItem == $handItem->getId()){
				return true;
			}
		}

		return false;
	}

	public function canContinue() : bool{
		if(abs($this->originalPos->y - $this->mob->y) < 0.5)
			return true;

		return false;
	}

	public function onTick(): void{
		if($this->temptingPlayer === null) return;
		$distanceToPlayer = $this->mob->distance($this->temptingPlayer);

		if($distanceToPlayer < 1.75){
			$this->mob->resetMotion();
			$this->mob->setLookPosition($this->temptingPlayer);

			$this->mob->getNavigator()->clearPath();

			return;
		}
		
		$deltaDistance = $this->lastPlayerPos->distance($this->temptingPlayer);
		if(!$this->mob->getNavigator()->havePath() || $deltaDistance > 1){
		    $m = 2 - $distanceToPlayer;
		    $m = ($m <= 0) ? 1 : $m / 2.0;
		    $this->mob->getNavigator()->tryMoveTo($this->temptingPlayer, $this->speedMultiplier * $m);
		    $this->lastPlayerPos = $this->temptingPlayer->asVector3();
		}

		$this->mob->setLookPosition($this->temptingPlayer);
	}

	public function onEnd(): void{
		$this->coolDown = 100;
		$this->mob->resetMotion();
		$this->temptingPlayer = null;
		$this->mob->pitch = 0;
		$this->mob->getNavigator()->clearPath();
	}
}