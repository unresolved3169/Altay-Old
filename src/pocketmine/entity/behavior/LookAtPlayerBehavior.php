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

class LookAtPlayerBehavior extends Behavior{

    /** @var float */
	protected $lookDistance = 6.0;
	/** @var Player */
	protected $player;
	/** @var int */
	protected $duration = 0;
	
	public function __construct(Living $mob, float $lookDistance = 6.0){
        parent::__construct($mob);

        $this->lookDistance = $lookDistance;
    }
	
	public function canStart() : bool{
        if(rand(0, 50) === 0){
            $player = $this->mob->level->getNearestEntity($this->mob->asVector3(), $this->lookDistance, Player::class);

            if($player instanceof Player){
                $this->player = $player;
                $this->duration = 40 + rand(0, 40);

                return true;
            }
        }

        return false;
    }
	
	public function canContinue() : bool{
        return $this->duration-- > 0;
    }
	
	public function onTick(int $tick) : void{
        if($this->player instanceof Player){
            $this->mob->lookAt($this->player);
        }
    }
	
	public function onEnd() : void{
        $this->mob->pitch = 0;
        $this->player = null;
    }
}