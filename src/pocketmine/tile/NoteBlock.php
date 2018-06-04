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
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class NoteBlock extends Spawnable{
	
	public const TAG_NOTE = "note";
	public const TAG_POWERED = "powered";

	/** @var int */
	protected $note = 0;
	protected $powered = 0;

	public function __construct(Level $level, CompoundTag $nbt){
		if($nbt->hasTag(self::TAG_NOTE, IntTag::class)){
			$this->note = $nbt->getInt(self::TAG_NOTE);
		}
		if($nbt->hasTag(self::TAG_POWERED, ByteTag::class)){
			$this->powered = $nbt->getByte(self::TAG_POWERED);
		}

		parent::__construct($level, $nbt);
	}

	public function setNote(int $note) : void{
		$this->note = $note;
	}

	public function getNote() : int{
		return $this->note;
	}
	
	public function setPowered(bool $value) : void{
		$this->powered = (int) $value;
	}

	public function isPowered() : bool{
		return (bool) $this->powered;
	}

	public function getDefaultName() : string{
		return "NoteBlock";
	}

	public function saveNBT() : void{
		parent::saveNBT();
		
		$this->namedtag->setInt(self::TAG_NOTE, $this->note);
		$this->namedtag->setByte(self::TAG_POWERED, $this->powered);
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->namedtag->hasTag(self::TAG_NOTE, IntTag::class)){
			$nbt->setTag($this->namedtag->getTag(self::TAG_NOTE));
		}
		if($this->namedtag->hasTag(self::TAG_POWERED, ByteTag::class)){
			$nbt->setTag($this->namedtag->getTag(self::TAG_POWERED));
		}
	}
}