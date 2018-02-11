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

class StrollBehavior extends Behavior{
	
	protected $speedMultiplier = 1.0;
	protected $chance = 120;
	protected $timeLeft = 0;
	
	public function __construct(Living $mob, float $speedMultiplier = 1.0, int $chance = 120){
		parent::__construct($mob);
		
		$this->speedMultiplier = $speedMultiplier;
		$this->chance = $chance;
	}
	
	public function canStart() : bool{
		if(rand(0,$this->chance) === 0){
			$this->timeLeft = rand(50,80);
			
			return true;
		}
		return false;
	}
	
	public function canContinue() : bool{
		return $this->timeLeft-- > 0;
	}
	
	public function onTick(int $tick) : void{
		if($this->mob->motionY < 0){
			$this->timeLeft = 0;
			return;
		}
		
		$this->moveForward($this->speedMultiplier);
	}
	
	public function onEnd() : void{
		$this->mob->motionX = 0; $this->mob->motionZ = 0;
		$this->timeLeft = 0;
	}
}