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

class SetActorDataPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::SET_ACTOR_DATA_PACKET;

	/** @var int */
	public $entityRuntimeId;
	/** @var array */
	public $metadata;

	protected function decodePayload(){
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->metadata = $this->getEntityMetadata();
	}

	protected function encodePayload(){
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putEntityMetadata($this->metadata);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleSetActorData($this);
	}
}
