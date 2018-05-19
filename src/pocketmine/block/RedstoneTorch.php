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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class RedstoneTorch extends Torch{

	protected $id = self::LIT_REDSTONE_TORCH;

	public function getName() : string{
		return "Redstone Torch";
	}

	public function getLightLevel() : int{
		return 7;
	}

	public function onRedstoneUpdate(int $power) : void{
		if($power > 0){
			$this->level->setBlock($this, BlockFactory::get(Block::UNLIT_REDSTONE_TORCH));
		}
	}

	public function getPower() : int{
		return 15;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool{
		$place =  parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		if($place){
			$this->level->updateRedstone($this, $this->getPower());
		}

		return $place;
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		$break = parent::onBreak($item, $player);
		if($break){
			$this->level->updateRedstone($this, 0);
		}

		return $break;
	}
}