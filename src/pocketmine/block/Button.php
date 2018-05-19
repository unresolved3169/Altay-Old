<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

abstract class Button extends Flowable{

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		//TODO: check valid target block
		$this->meta = $face;

		return $this->level->setBlock($this, $this, true, true);
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if(!$this->isRedstoneSource()){
			$this->updateRedstone();
			$this->level->scheduleDelayedBlockUpdate($this, 30);
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		if($this->isRedstoneSource()){
			$this->updateRedstone();
		}
	}

	private function updateRedstone(){
		$this->meta ^= 0x08;
		$this->level->setBlock($this, $this, true, false);
		$power = $this->getPower();
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_REDSTONE_TRIGGER);
		$this->level->updateRedstone($this, $power);
		$this->level->updateRedstone($this->asVector3()->getSide(Vector3::getOppositeSide($this->isRedstoneSource() ? $this->meta ^ 0x08 : $this->meta)), $power);
	}

	public function getPower() : int{
		return $this->isRedstoneSource() ? 15 : 0;
	}

	public function isRedstoneSource() : bool{
		return (($this->meta & 0x08) === 0x08);
	}
}