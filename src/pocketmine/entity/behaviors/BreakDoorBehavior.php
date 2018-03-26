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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\utils\Random;

class BreakDoorBehavior extends DoorInteractBehavior{

	/** @var int */
	private $breakingTime;
	/** @var int */
	private $previousBreakProgress = -1;

	public function shouldExecute() : bool{
		// TODO : Gamerule eklendiğinde mobGriefing kapalı ise false döndür
		if(!parent::shouldExecute()){
			return false;
		}else{
			return !$this->doorBlock->isOpen();
		}
	}

	public function startExecuting() : void{
		parent::startExecuting();
		$this->breakingTime = 0;
	}

	public function continueExecuting() : bool{
		$distance = $this->entity->distanceSquared($this->doorPosition);

		if($this->breakingTime <= 240){
			if(!$this->doorBlock->isOpen() && $distance < 4.0){
				return true;
			}
		}

		return false;
	}

	public function resetTask() : void{
		parent::resetTask();
		$this->entity->level->broadcastLevelEvent($this->doorPosition, LevelEventPacket::EVENT_BLOCK_STOP_BREAK);
	}

	public function updateTask() : void{
		parent::updateTask();

		if((new Random())->nextBoundedInt(20) == 0){
			$this->entity->level->broadcastLevelEvent($this->doorPosition, LevelEventPacket::EVENT_SOUND_DOOR_BUMP);
		}

		++$this->breakingTime;
		$i = (int)($this->breakingTime / 240.0 * 10.0);

		if($i != $this->previousBreakProgress){
			$this->entity->level->broadcastLevelEvent($this->doorPosition, LevelEventPacket::EVENT_BLOCK_START_BREAK, 65535 / 240);
			$this->previousBreakProgress = $i;
		}

		if($this->breakingTime == 240 && $this->entity->level->getDifficulty() == Level::DIFFICULTY_HARD){
			$this->entity->level->setBlock($this->doorPosition, BlockFactory::get(Block::AIR));
			$this->entity->level->broadcastLevelEvent($this->doorPosition, LevelEventPacket::EVENT_SOUND_DOOR_CRASH);
			$this->entity->level->broadcastLevelEvent($this->doorPosition, LevelEventPacket::EVENT_PARTICLE_DESTROY, $this->doorBlock->getId());
		}
	}
}