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

namespace pocketmine\entity\utils;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{
	AddEntityPacket, BossEventPacket, RemoveEntityPacket, SetEntityDataPacket, UpdateAttributesPacket
};
use pocketmine\Player;

/*
 * This a Helper class for create a simple Bossbar
 * Note: This not a entity
 */
class Bossbar extends Vector3{

	/** @var float */
	protected $healthPercent = 0, $maxHealthPercent = 1;
	/** @var int */
	protected $entityId;
	/** @var array */
	protected $metadata = [];
	/** @var array */
	protected $viewers = [];

	public function __construct(string $title = "Altay Bossbar API", float $hp = 1, float $maxHp = 1, ?int $entityId = null){
		parent::__construct(0,0,0);

		$flags = (
			(1 << Entity::DATA_FLAG_INVISIBLE) |
			(1 << Entity::DATA_FLAG_IMMOBILE)
		);
		$this->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]
		];

		$this->entityId = $entityId ?? Entity::$entityCount++;

		$this->setHealthPercent($hp, $maxHp);
	}

	public function setTitle(string $t, bool $update = true){
		$this->setMetadata(Entity::DATA_NAMETAG, Entity::DATA_TYPE_STRING, $t);
		if($update){
			$this->updateForAll();
		}
	}

	public function getTitle() : string{
		return $this->getMetadata(Entity::DATA_NAMETAG);
	}

	public function setHealthPercent(?float $hp = null, ?float $maxHp = null, bool $update = true){
		if($maxHp !== null){
			$this->maxHealthPercent = $maxHp;
		}

		if($hp !== null){
			if($hp > $this->maxHealthPercent){
				$this->maxHealthPercent = $hp;
			}

			$this->healthPercent = $hp;
		}

		if($update){
			$this->updateForAll();
		}
	}

	public function getHealthPercent() : float{
		return $this->healthPercent;
	}

	public function getMaxHealthPercent() : float{
		return $this->maxHealthPercent;
	}

	public function showTo(Player $player, bool $isViewer = true){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->entityId;
		$pk->type = EntityIds::SHULKER;
		$pk->metadata = $this->metadata;
		$pk->position = $this;

		$player->dataPacket($pk);
		$player->dataPacket($this->getHealthPacket());

		$pk2 = new BossEventPacket();
		$pk2->bossEid = $this->entityId;
		$pk2->eventType = BossEventPacket::TYPE_SHOW;
		$pk2->title = $this->getTitle();
		$pk2->healthPercent = $this->healthPercent;
		$pk2->color = 0;
		$pk2->overlay = 0;
		$pk2->unknownShort = 0;

		$player->dataPacket($pk2);

		if($isViewer){
			$this->viewers[$player->getLoaderId()] = $player;
		}
	}

	public function hideFrom(Player $player){
		$pk = new BossEventPacket();
		$pk->bossEid = $this->entityId;
		$pk->eventType = BossEventPacket::TYPE_HIDE;

		$player->dataPacket($pk);

		$pk2 = new RemoveEntityPacket();
		$pk2->entityUniqueId = $this->entityId;

		$player->dataPacket($pk2);

		if(isset($this->viewers[$player->getLoaderId()])){
			unset($this->viewers[$player->getLoaderId()]);
		}
	}

	public function updateFor(Player $player){
		$pk = new BossEventPacket();
		$pk->bossEid = $this->entityId;
		$pk->eventType = BossEventPacket::TYPE_TITLE;
		$pk->healthPercent = $this->getHealthPercent();
		$pk->title = $this->getTitle();

		$pk2 = clone $pk;
		$pk2->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;

		$player->dataPacket($pk);
		$player->dataPacket($pk2);
		$player->dataPacket($this->getHealthPacket());

		$mpk = new SetEntityDataPacket();
		$mpk->entityRuntimeId = $this->entityId;
		$mpk->metadata = $this->metadata;

		$player->dataPacket($mpk);
	}

	public function updateForAll() : void{
		foreach($this->viewers as $player){
			$this->updateFor($player);
		}
	}

	protected function getHealthPacket() : UpdateAttributesPacket{
		$attr = Attribute::getAttribute(Attribute::HEALTH);
		$attr->setMaxValue($this->maxHealthPercent);
		$attr->setValue($this->healthPercent);

		$pk = new UpdateAttributesPacket();
		$pk->entityRuntimeId = $this->entityId;
		$pk->entries = [$attr];

		return $pk;
	}

	public function setMetadata(int $key, int $dtype, $value){
		$this->metadata[$key] = [$dtype, $value];
	}

	/**
	 * @param int $key
	 * @return mixed
	 */
	public function getMetadata(int $key){
		return isset($this->metadata[$key]) ? $this->metadata[$key][1] : null;
	}

	public function getViewers() : array{
		return $this->viewers;
	}

	public function getEntityId() : int{
		return $this->entityId;
	}
}