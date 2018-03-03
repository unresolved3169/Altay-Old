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

use pocketmine\entity\Living;
use pocketmine\Player;

class FindAttackableTargetBehavior extends Behavior{

    /** @var float */
	protected $targetDistance = 16.0;
	/** @var int */
	protected $targetUnseenTicks = 0;
	
	public function __construct(Living $mob, float $targetDistance = 16.0){
		parent::__construct($mob);
		
		$this->targetDistance = $targetDistance;
	}
	
	public function canStart() : bool{
		if(rand(0,10) === 0){
		    /** @var Player $player */
			$player = null;
			foreach($this->mob->level->getPlayers() as $p){
				if($p->isAlive() and $p->isSurvival(true) and $this->mob->distance($p) < $this->getTargetDistance($p)){
					if($player === null or $p->distance($this->mob) < $player->distance($this->mob)){
						$player = $p;
					}
				}
			}
			
			$this->mob->setTargetEntity($player);
			
			if($player instanceof Player){
				return true;
			}
		}
		return false;
	}
	
	public function getTargetDistance(Player $p){
		$dist = $this->targetDistance;
		if($p->isSneaking()){
			$dist *= 0.8;
		}
		return $dist;
	}
	
	public function onStart() : void{
		$this->targetUnseenTicks = 0;
	}
	
	public function canContinue() : bool{
		$target = $this->mob->getTargetEntity();
		
		if($target === null or !$target->isAlive()) return false;
		
		if($target instanceof Player){
			if($this->mob->distance($target) > $this->getTargetDistance($target)) return false;

			// TODO : Emre canSee fonksiyonu yok
			if($this->mob->canSee($target)){
				$this->targetUnseenTicks = 0;
			}elseif($this->targetUnseenTicks++ > 60){
				return false;
			}
			$this->mob->setTargetEntity($target);
		}else{
			if($this->mob->distance($target) > $this->targetDistance){
				return false;
			}
		}
		
		return true;
	}
	
	public function onTick(int $tick) : void{
	}
	
	public function onEnd() : void{
		$this->mob->setTargetEntity(null);
	}
}