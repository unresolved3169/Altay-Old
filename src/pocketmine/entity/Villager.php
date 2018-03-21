<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\TradingInventory;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\village\MerchantRecipe;
use pocketmine\village\MerchantRecipeList;
use pocketmine\Player;

class Villager extends Creature implements NPC, Ageable{

	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	public const NETWORK_ID = self::VILLAGER;

	public $width = 0.6;
	public $height = 1.8;

	public $careerId;
	public $careerLevel;

	public $buyingList = null;

	public function getName() : string{
		return "Villager";
	}

	protected function initEntity(){
		parent::initEntity();
		$this->registerTrade();

		$this->rand = new Random();

		/** @var int $profession */
		$profession = $this->namedtag->getInt("Profession", self::PROFESSION_FARMER);

		if($profession > 4 or $profession < 0){
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);

		$this->careerId = $this->namedtag->getInt("Career", 0);
		$this->careerLevel = $this->namedtag->getInt("CareerLevel", 0);

		$this->populateBuyingList();
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->setInt("Profession", $this->getProfession());
		$this->namedtag->setInt("Career", $this->careerId);
		$this->namedtag->setInt("CareerLevel", $this->careerLevel);
		if($this->buyingList !== null){
			$this->namedtag->setTag("Offers", $this->buyingList->writeToTags());
		}
	}

	public function getRecipes(Player $player) : MerchantRecipeList{
		if($this->buyingList === null){
			$this->populateBuyingList();
		}

		return $this->buyingList;
	}

	private function populateBuyingList(){
		$itradelist = $this->DEFAULT_TRADE_LIST_MAP[$this->getProfession()];

		$this->careerId = mt_rand(1, count($itradelist));
		$this->careerLevel = 1;

		if($this->buyingList === null){
			$this->buyingList = new MerchantRecipeList();
		}

		$i = $this->careerId - 1;
		$j = $this->careerLevel - 1;
		$itradelist1 = $itradelist[$i];

		if($j >= 0 && $j < count($itradelist)){
			$itradelist2 = $itradelist1[$j];

			foreach($itradelist2 as $trade){
				$trade->modifyMerchantRecipeList($this->buyingList, $this->rand);
			}
		}
	}

	/**
	 * Sets the villager profession
	 *
	 * @param int $profession
	 */
	public function setProfession(int $profession){
		$this->propertyManager->setInt(self::DATA_VARIANT, $profession);
	}

	public function getProfession() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function isBaby() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickVector, array $actions = []){
		$player->addWindow($this->getInventory());
	}

	public function getInventory() : Inventory{
		return new TradingInventory($this);
	}

	public function getDisplayName() : string{
		$s1 = "";
		switch($this->getProfession()){
			case 0:
				if($this->careerId == 1){
					$s1 = "farmer";
				}else if($this->careerId == 2){
					$s1 = "fisherman";
				}else if($this->careerId == 3){
					$s1 = "shepherd";
				}else if($this->careerId == 4){
					$s1 = "fletcher";
				}

				break;
			case 1:
				$s1 = "librarian";
				break;
			case 2:
				$s1 = "cleric";
				break;
			case 3:
				if($this->careerId == 1){
					$s1 = "armor";
				}else if($this->careerId == 2){
					$s1 = "weapon";
				}else if($this->careerId == 3){
					$s1 = "tool";
				}

				break;
			case 4:
				if($this->careerId == 1){
					$s1 = "butcher";
				}else if($this->careerId == 2){
					$s1 = "leather";
				}
		}

		return $s1;
	}

	public $DEFAULT_TRADE_LIST_MAP = [];

	public function registerTrade(){
		$this->DEFAULT_TRADE_LIST_MAP = [
					[
						[
							[
								new EmeraldForItems(Item::get(Item::WHEAT, 0, 1), new PriceInfo(18, 22)),
								new EmeraldForItems(Item::get(Item::POTATO, 0, 1), new PriceInfo(15, 19)),
								new EmeraldForItems(Item::get(Item::CARROT, 0, 1), new PriceInfo(15, 19)),
								new ListItemForEmeralds(Item::get(Item::BREAD, 0, 1), new PriceInfo(-4, -2))
							],
							[
								new EmeraldForItems(Item::get(Item::PUMPKIN, 0, 1), new PriceInfo(8, 13)),
								new ListItemForEmeralds(Item::get(Item::PUMPKIN_PIE, 0, 1), new PriceInfo(-3, -2))
							],
							[
								new EmeraldForItems(Item::get(Item::MELON_BLOCK, 0, 1), new PriceInfo(7, 12)),
								new ListItemForEmeralds(Item::get(Item::APPLE, 0, 1), new PriceInfo(-5, -7))
							],
							[
								new ListItemForEmeralds(Item::get(Item::COOKIE, 0, 1), new PriceInfo(-6, -10)),
								new ListItemForEmeralds(Item::get(Item::CAKE, 0, 1), new PriceInfo(1, 1))
							]
						],
						[
							[
								new EmeraldForItems(Item::get(Item::STRING, 0, 1), new PriceInfo(15, 20)),
								new EmeraldForItems(Item::get(Item::COAL, 0, 1), new PriceInfo(16, 24)),
								new ItemAndEmeraldToItem(Item::get(Item::FISH, 0, 1), new PriceInfo(6, 6), Item::get(Item::COOKED_FISH, 0, 1), new PriceInfo(6, 6))
							],
							[
								new ListEnchantedItemForEmeralds(Item::get(Item::FISHING_ROD, 0, 1), new PriceInfo(7, 8))
							]
						],
						[
							[
								new EmeraldForItems(Item::get(Item::WOOL, 0, 1), new PriceInfo(16, 22)),
								new ListItemForEmeralds(Item::get(Item::SHEARS, 0, 1), new PriceInfo(3, 4))
							],
							[
								new ListItemForEmeralds(Item::get(Item::WOOL, 0, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 1, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 2, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 3, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 4, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 5, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 6, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 7, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 8, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 9, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 10, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 11, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 12, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 13, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 14, 1), new PriceInfo(1, 2)),
								new ListItemForEmeralds(Item::get(Item::WOOL, 15, 1), new PriceInfo(1, 2))
							]
						],
						[
							[
								new EmeraldForItems(Item::get(Item::STRING, 0, 1), new PriceInfo(15, 20)),
								new ListItemForEmeralds(Item::get(Item::ARROW, 0, 1), new PriceInfo(-12, -8))
							],
							[
								new ListItemForEmeralds(Item::get(Item::BOW, 0, 1), new PriceInfo(2, 3)),
								new ItemAndEmeraldToItem(Item::get(Item::GRAVEL, 0, 1), new PriceInfo(10, 10), Item::get(Item::FLINT, 0, 1), new PriceInfo(6, 10))
							]
						]
					],
					[
						[
							[
								new EmeraldForItems(Item::get(Item::PAPER, 0, 1), new PriceInfo(24, 36)),
								new ListEnchantedBookForEmeralds()
							],
							[
								new EmeraldForItems(Item::get(Item::BOOK, 0, 1), new PriceInfo(8, 10)),
								new ListItemForEmeralds(Item::get(Item::COMPASS, 0, 1), new PriceInfo(10, 12)),
								new ListItemForEmeralds(Item::get(Item::BOOKSHELF, 0, 1), new PriceInfo(3, 4))
							],
							[
								new EmeraldForItems(Item::get(Item::WRITTEN_BOOK, 0, 1), new PriceInfo(2, 2)),
								new ListItemForEmeralds(Item::get(Item::CLOCK, 0, 1), new PriceInfo(10, 12)),
								new ListItemForEmeralds(Item::get(Item::GLASS, 0, 1), new PriceInfo(-5, -3))
							],
							[
								new ListEnchantedBookForEmeralds()
							],
							[
								new ListEnchantedBookForEmeralds()
							],
							[
								new ListItemForEmeralds(Item::get(Item::NAME_TAG, 0, 1), new PriceInfo(20, 22))
							]
						]
					],
					[
						[
							[
								new EmeraldForItems(Item::get(Item::ROTTEN_FLESH, 0, 1), new PriceInfo(36, 40)),
								new EmeraldForItems(Item::get(Item::GOLD_INGOT, 0, 1), new PriceInfo(8, 10))
							],
							[
								new ListItemForEmeralds(Item::get(Item::REDSTONE, 0, 1), new PriceInfo(-4, -1)),
								new ListItemForEmeralds(Item::get(Item::DYE, 4, 1), new PriceInfo(-2, -1))
							],
							[
								new ListItemForEmeralds(Item::get(Item::ENDER_EYE, 0, 1), new PriceInfo(7, 11)),
								new ListItemForEmeralds(Item::get(Item::GLOWSTONE, 0, 1), new PriceInfo(-3, -1))
							],
							[
								new ListItemForEmeralds(Item::get(Item::EXPERIENCE_BOTTLE, 0, 1), new PriceInfo(3, 11))
							]
						]
					],
					[
						[
							[
								new EmeraldForItems(Item::get(Item::COAL, 0, 1), new PriceInfo(16, 24)),
								new ListItemForEmeralds(Item::get(Item::IRON_HELMET, 0, 1), new PriceInfo(4, 6))
							],
							[
								new EmeraldForItems(Item::get(Item::IRON_INGOT, 0, 1), new PriceInfo(7, 9)),
								new ListItemForEmeralds(Item::get(Item::IRON_CHESTPLATE, 0, 1), new PriceInfo(10, 14))
							],
							[
								new EmeraldForItems(Item::get(Item::DIAMOND, 0, 1), new PriceInfo(3, 4)),
								new ListEnchantedItemForEmeralds(Item::get(Item::DIAMOND_CHESTPLATE, 0, 1), new PriceInfo(16, 19))
							],
							[
								new ListItemForEmeralds(Item::get(Item::CHAINMAIL_BOOTS, 0, 1), new PriceInfo(5, 7)),
								new ListItemForEmeralds(Item::get(Item::CHAINMAIL_LEGGINGS, 0, 1), new PriceInfo(9, 11)),
								new ListItemForEmeralds(Item::get(Item::CHAINMAIL_HELMET, 0, 1), new PriceInfo(5, 7)),
								new ListItemForEmeralds(Item::get(Item::CHAINMAIL_CHESTPLATE, 0, 1), new PriceInfo(11, 15))
							]
						],
						[
							[
								new EmeraldForItems(Item::get(Item::COAL, 0, 1), new PriceInfo(16, 24)),
								new ListItemForEmeralds(Item::get(Item::IRON_AXE, 0, 1), new PriceInfo(6, 8))
							],
							[
								new EmeraldForItems(Item::get(Item::IRON_INGOT, 0, 1), new PriceInfo(7, 9)),
								new ListEnchantedItemForEmeralds(Item::get(Item::IRON_SWORD, 0, 1), new PriceInfo(9, 10))
							],
							[
								new EmeraldForItems(Item::get(Item::DIAMOND, 0, 1), new PriceInfo(3, 4)),
								new ListEnchantedItemForEmeralds(Item::get(Item::DIAMOND_SWORD, 0, 1), new PriceInfo(12, 15)), 
								new ListEnchantedItemForEmeralds(Item::get(Item::DIAMOND_AXE, 0, 1), new PriceInfo(9, 12))
							]
						],
						[
							[
								new EmeraldForItems(Item::get(Item::COAL, 0, 1), new PriceInfo(16, 24)),
								new ListEnchantedItemForEmeralds(Item::get(Item::IRON_SHOVEL, 0, 1), new PriceInfo(5, 7))
							],
							[
								new EmeraldForItems(Item::get(Item::IRON_INGOT, 0, 1), new PriceInfo(7, 9)),
								new ListEnchantedItemForEmeralds(Item::get(Item::IRON_PICKAXE, 0, 1), new PriceInfo(9, 11))
							],
							[
								new EmeraldForItems(Item::get(Item::DIAMOND, 0, 1), new PriceInfo(3, 4)),
								new ListEnchantedItemForEmeralds(Item::get(Item::DIAMOND_PICKAXE, 0, 1), new PriceInfo(12, 15))
							]
						]
					],
					[
						[
							[
								new EmeraldForItems(Item::get(Item::PORKCHOP, 0, 1), new PriceInfo(14, 18)),
								new EmeraldForItems(Item::get(Item::CHICKEN, 0, 1), new PriceInfo(14, 18))
							],
							[
								new EmeraldForItems(Item::get(Item::COAL, 0, 1), new PriceInfo(16, 24)),
								new ListItemForEmeralds(Item::get(Item::COOKED_PORKCHOP, 0, 1), new PriceInfo(-7, -5)),
								new ListItemForEmeralds(Item::get(Item::COOKED_CHICKEN, 0, 1), new PriceInfo(-8, -6))
							]
						],
						[
							[
								new EmeraldForItems(Item::get(Item::LEATHER, 0, 1), new PriceInfo(9, 12)),
								new ListItemForEmeralds(Item::get(Item::LEATHER_LEGGINGS, 0, 1), new PriceInfo(2, 4))
							],
							[
								new ListEnchantedItemForEmeralds(Item::get(Item::LEATHER_CHESTPLATE, 0, 1), new PriceInfo(7, 12))
							],
							[
								new ListItemForEmeralds(Item::get(Item::SADDLE, 0, 1), new PriceInfo(8, 10))
							]
						]
					]
				];
	}

}
interface ITradeList{
	function modifyMerchantRecipeList(MerchantRecipeList $recipeList, Random $random) : void;
}
class EmeraldForItems implements ITradeList{

	public $sellItem;
	public $price;

	function __construct(Item $itemIn, PriceInfo $priceIn){
		$this->sellItem = $itemIn;
		$this->price = $priceIn;
	}

	function modifyMerchantRecipeList(MerchantRecipeList $recipeList, Random $random) : void{
		$i = 1;

		if($this->price !== null){
			$i = $this->price->getPrice($random);
		}

		$recipeList->add(new MerchantRecipe(Item::get($this->sellItem->getId(), 0, $i), Item::get(Item::AIR), Item::get(Item::EMERALD, 0, 1)));
	}
}
class ItemAndEmeraldToItem implements ITradeList{

	public $item1;
	public $price1;
	public $item2;
	public $price2;

	function __construct(Item $item1, PriceInfo $price1, Item $item2, PriceInfo $price2){
		$this->item1 = $item1;
		$this->price1 = $price1;
		$this->item2 = $item2;
		$this->price2 = $price2;
	}

	function modifyMerchantRecipeList(MerchantRecipeList $recipeList, Random $random) : void{
		$i = 1;

		if($this->price1 !== null){
			$i = $this->price1->getPrice($random);
		}

		$j = 1;

		if($this->price2 !== null){
			$j = $this->price2->getPrice($random);
		}

		$recipeList->add(new MerchantRecipe(Item::get($this->item1->getId(), $this->item1->getDamage(), $i), Item::get(Item::EMERALD, 0, 1), Item::get($this->item2->getId(), $this->item2->getDamage(), $j)));
	}
}
class ListEnchantedBookForEmeralds implements ITradeList{

	function modifyMerchantRecipeList(MerchantRecipeList $recipeList, Random $random) : void{
		$enchId = mt_rand(0, 25);
		$ench = Enchantment::getEnchantment($enchId);
		$i = mt_rand(1, $ench->getMaxLevel());
		$enchantment = new EnchantInstance($ench, $i);
		$item = Item::get(Item::ENCHANTED_BOOK, 0, 1);
		$item->addEnchantment($enchantment);
		$j = 2 + $random->nextRange(0, 5 + $i + 10) + 3 * $i;

		if($j > 64){
			$j = 64;
		}

		$recipeList->add(new MerchantRecipe(Item::get(Item::BOOK, 0, 1), Item::get(Item::EMERALD, 0, $j), $item));
	}
}
class ListEnchantedItemForEmeralds implements ITradeList{

	public $item1;
	public $price1;

	function __construct(Item $item1, PriceInfo $price1){
		$this->item1 = $item1;
		$this->price1 = $price1;
	}

	function modifyMerchantRecipeList(MerchantRecipeList $recipeList, Random $random) : void{
		$i = 1;

		if($this->price1 !== null){
			$i = $this->price1->ferPrice($random);
		}

		$item = Item::get(Item::EMERALD, 0, $i);
		$item1 = Item::get($this->item1->getId(). $this->item1->getDamage(), 1);
		//TODO addEnchantment
		$reciprList->add(new MerchantRecipe($item, Item::get(Item::AIR), $item1));
	}
}
class ListItemForEmeralds implements ITradeList{

	public $item;
	public $priceInfo;

	function __construct(Item $item, PriceInfo $priceInfo){
		$this->item = $item;
		$this->priceInfo = $priceInfo;
	}

	function modifyMerchantRecipeList(MerchantRecipeList $recipeList, Random $random) : void{
		$i = 1;

		if($this->priceInfo !== null){
			$i = $this->priceInfo->getPrice($random);
		}

		if($i < 0){
			$item = Item::get(Item::EMERALD, 0, 1);
			$item1 = Item::get($this->item->getId(), $this->item->getDamage(), -$i);
		}else{
			$item = Item::get(Item::EMERALD, 0, $i);
			$item1 = Item::get($this->item->getId(), $this->item->getDamage(), 1);
		}

		$recipeList->add(new MerchantRecipe($item, Item::get(Item::AIR), $item1));
	}
}
class PriceInfo{

	public $first;
	public $second;

	function __construct(int $first, int $second){
		$this->first = $first;
		$this->second = $second;
	}

	function getPrice(Random $rand) : int{
		return $this->first >= $this->second ? $this->first : $this->first + $rand->nextRange(0, $this->second - $this->first + 1);
	}
}