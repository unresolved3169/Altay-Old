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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\projectile\FireworksRocket;
use pocketmine\entity\utils\FireworksUtils;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

class Fireworks extends Item{
	/** @var float */
	public const SPEED = 1.25;
	/** @var float */
	public const SPREAD = 6.0;

	public function __construct(int $meta = 0){
		parent::__construct(self::FIREWORKS, $meta, "Firework Rocket");
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool{
		$random = new Random();
		$nbt = FireworksUtils::createEntityNBT($blockReplace, null, $this, self::SPREAD, $random);
		$fireworkRocket = new FireworksRocket($player->level, $nbt, clone $this, $player, $random);
		$fireworkRocket->spawnToAll();
		$this->count--;

		return true;
	}

	public function onClickAir(Player $player, Vector3 $directionVector): bool{
		if($player->isGliding()){ // elytra booster
			$random = new Random();
			$motion = $player->getDirectionVector()->multiply(self::SPEED);
			$nbt = FireworksUtils::createEntityNBT($player, $motion, $this, self::SPREAD, $random, $player->getYaw(), $player->getPitch());
			$fireworkRocket = new FireworksRocket($player->level, $nbt, clone $this, $player, $random);
			$fireworkRocket->spawnToAll();
			$this->count--;
			$player->setMotion($motion);
			$fireworkRocket->setMotion($motion->multiply(2.5));
		}

		return false;
	}
}
