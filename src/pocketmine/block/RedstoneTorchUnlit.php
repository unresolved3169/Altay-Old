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

class RedstoneTorchUnlit extends Torch{

	protected $id = self::UNLIT_REDSTONE_TORCH;

	public function getName() : string{
		return "Unlit Redstone Torch";
	}

	public function getLightLevel() : int{
		return 0;
	}

	public function onRedstoneUpdate(int $power) : void{
		if($power <= 0){
			$this->level->setBlock($this, BlockFactory::get(Block::REDSTONE_TORCH));
		}
	}
}