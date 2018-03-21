<?php

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