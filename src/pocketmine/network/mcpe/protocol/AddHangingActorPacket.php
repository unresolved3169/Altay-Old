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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;

class AddHangingActorPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::ADD_HANGING_ACTOR_PACKET;

	/** @var int|null */
	public $entityUniqueId = null;
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $direction;

	protected function decodePayload(){
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->getBlockPosition($this->x, $this->y, $this->z);
		$this->direction = $this->getVarInt();
	}

	protected function encodePayload(){
		$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putBlockPosition($this->x, $this->y, $this->z);
		$this->putVarInt($this->direction);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAddHangingActor($this);
	}

}
