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

namespace pocketmine\village;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class MerchantRecipeList{

	public $recipes = [];

	public function __construct(){
	}

	public function add(MerchantRecipe $recipe){
		$this->recipes[] = $recipe;
	}

	public function writeToTags() : CompoundTag{
		$tag = new CompoundTag("", [
			new ListTag("Recipes")
		]);
		foreach($this->recipes as $recipe){
			$nbt = $recipe->writeToTags();
			$tag->offsetGet("Recipes")->push($nbt);
		}

		return $tag;
	}
}