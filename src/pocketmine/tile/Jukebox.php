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

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\Record;
use pocketmine\network\mcpe\protocol\{
	PlaySoundPacket, StopSoundPacket, TextPacket
};

class Jukebox extends Spawnable{
	public const TAG_RECORD_ITEM = "RecordItem";

	/** @var Record|null */
	protected $recordItem = null;

	public function __construct(Level $level, CompoundTag $nbt){
		if($nbt->hasTag(self::TAG_RECORD_ITEM)){
			$this->recordItem = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_RECORD_ITEM));
		}

		parent::__construct($level, $nbt);
	}

	public function setRecordItem(?Record $item) : void{
		$this->recordItem = $item;
	}

	public function getRecordItem() : ?Record{
		return $this->recordItem;
	}

	public function playDisc(Player $player) : void{
		if($this->getRecordItem() instanceof Record){
			$pk = new PlaySoundPacket();
			$pk->soundName = $this->getRecordItem()->getSoundId();
			$pk->pitch = $pk->volume = 1.0;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$this->level->addChunkPacket($this->getFloorX() >> 4, $this->getFloorZ() >> 4, $pk);

			$pk = new TextPacket();
			$pk->type = TextPacket::TYPE_JUKEBOX_POPUP;
			$pk->needsTranslation = true;
			$pk->message = "record.nowPlaying";
			$pk->parameters = ["item.".str_replace(".", "_", $this->getRecordItem()->getSoundId()).".desc"]; // TODO
			$player->dataPacket($pk);
		}
	}

	public function stopDisc() : void{
		if($this->getRecordItem() instanceof Record){
			$pk = new StopSoundPacket();
			$pk->soundName = $this->getRecordItem()->getSoundId();
			$pk->stopAll = false;
			$this->level->addChunkPacket($this->getFloorX() >> 4, $this->getFloorZ() >> 4, $pk);
		}
	}

	public function dropDisc() : void{
		if($this->getRecordItem() instanceof Record){
			$this->stopDisc();
			$this->level->dropItem($this->add(0.5,1,0.5), $this->getRecordItem());
			$this->setRecordItem(null);
		}
	}

	public function hasRecordItem() : bool{
		return $this->recordItem instanceof Record;
	}

	public function getDefaultName() : string{
		return "Jukebox";
	}

	public function saveNBT() : void{
		parent::saveNBT();

		if($this->recordItem !== null){
			$this->namedtag->setTag($this->recordItem->nbtSerialize(-1, self::TAG_RECORD_ITEM));
		}else{
			$this->namedtag->removeTag(self::TAG_RECORD_ITEM);
		}
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->namedtag->hasTag(self::TAG_RECORD_ITEM, CompoundTag::class)){
			$nbt->setTag($this->namedtag->getTag(self::TAG_RECORD_ITEM));
		}
	}
}