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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\biome\Biome;
use pocketmine\Player;
use pocketmine\Server;

class NetherPortal extends Flowable{
	protected $id = self::PORTAL;

	public const MAX_PORTAL_SIZE = 23;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Nether Portal";
	}

	public function getHardness() : float{
		return -1;
	}

	public function getBlastResistance() : float{
		return 0;
	}

	public function getLightLevel() : int{
		return 11;
	}

	public function canPassThrough() : bool{
		return true;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		$server = Server::getInstance();
		if($server->allowNether){
			$biome = $entity->getLevel()->getBiomeId($entity->x, $entity->z);
			$level = $biome !== Biome::HELL ? $server->getNetherLevel() : $server->getDefaultLevel();
			$entity->teleport($level->getSafeSpawn($entity));
		}
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		$result = parent::onBreak($item, $player);

		for($i=0; $i<=5; $i++){
			$b = $this->getSide($i);
			if($b instanceof NetherPortal){
				$b->onBreak($item, $player);
			}
		}

		return $result;
	}
}