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

namespace pocketmine\entity;

use pocketmine\inventory\TradeItems;
use pocketmine\inventory\TradingInventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Villager extends Creature implements NPC, Ageable{
	public const NETWORK_ID = self::VILLAGER;

	public static $names = [
		Villager::PROFESSION_FARMER => [
			Villager::CAREER_FARMER => "entity.villager.farmer",
			Villager::CAREER_FISHERMAN => "entity.villager.fisherman",
			Villager::CAREER_STEPHERD => "entity.villager.shepherd",
			Villager::CAREER_FLETCHER => "entity.villager.fletcher"
		],
		Villager::PROFESSION_LIBRARIAN => [
			Villager::CAREER_LIBRARIAN => "entity.villager.librarian",
			Villager::CAREER_CARTOGRAPHER => "entity.villager.cartographer"
		],
		Villager::PROFESSION_PRIEST => [
			Villager::CAREER_CLERIC => "entity.villager.cleric"
		],
		Villager::PROFESSION_BLACKSMITH => [
			Villager::CAREER_ARMOR => "entity.villager.armor",
			Villager::CAREER_WEAPON => "entity.villager.weapon",
			Villager::CAREER_TOOL => "entity.villager.tool"
		],
		Villager::PROFESSION_BUTCHER => [
			Villager::CAREER_BUTCHER => "entity.villager.butcher",
			Villager::CAREER_LEATHER => "entity.villager.leather"
		]
	];

	public const CAREER_FARMER = 1, CAREER_LIBRARIAN = 1, CAREER_CLERIC= 1, CAREER_ARMOR = 1, CAREER_BUTCHER = 1;
	public const CAREER_FISHERMAN = 2, CAREER_CARTOGRAPHER = 2, CAREER_WEAPON = 2, CAREER_LEATHER = 2;
	public const CAREER_STEPHERD = 3, CAREER_TOOL = 3;
	public const CAREER_FLETCHER = 4;

	public const PROFESSION_FARMER = 0;
	public const PROFESSION_LIBRARIAN = 1;
	public const PROFESSION_PRIEST = 2;
	public const PROFESSION_BLACKSMITH = 3;
	public const PROFESSION_BUTCHER = 4;

	public $width = 0.6;
	public $height = 1.8;

	/** @var int */
	protected $career;
	/** @var int */
	protected $tradeTier;
	/** @var CompoundTag */
	protected $offers = null;
	/** @var TradingInventory */
	protected $inventory;

	public function getName() : string{
		return "Villager";
	}

	protected function initEntity(){
		parent::initEntity();

		/** @var int $profession */
		$profession = $this->namedtag->getInt("Profession", self::PROFESSION_FARMER);

		if($profession > 4 or $profession < 0){
			$profession = self::PROFESSION_FARMER;
		}

		$this->setProfession($profession);

		$this->career = $this->namedtag->getInt("Career", array_rand(self::$names[$this->getProfession()])); // custom
		$this->tradeTier = $this->namedtag->getInt("TradeTier", 0);
		$this->registerTrade();
	}

	public function registerTrade(bool $default = false){
		if($default or !$this->namedtag->hasTag("Offers", CompoundTag::class)) {
			$this->namedtag->setTag(new CompoundTag("Offers", [
				new ListTag("Recipes", TradeItems::getItemsForVillager($this))
			]));
		}

		$this->offers = $this->namedtag->getTag("Offers");
	}

	public function getTradeTier() : int{
		return $this->tradeTier;
	}

	public function getCareer() : int{
		return $this->career;
	}

	public function setOffers(CompoundTag $offers) : void{
		$this->offers = $offers;
	}

	public function getOffers() : CompoundTag{
		return $this->offers;
	}

	public function saveNBT(){
		parent::saveNBT();

		$this->namedtag->setInt("Profession", $this->getProfession());
		$this->namedtag->setInt("Career", $this->career);
		$this->namedtag->setInt("TradeTier", $this->tradeTier);
		$this->namedtag->setTag($this->offers);
	}

	public function setProfession(int $profession){
		$this->propertyManager->setInt(self::DATA_VARIANT, $profession);
	}

	public function getProfession() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function isBaby() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : void{
		if(!$this->isBaby() && $this->offers != null){
			$player->addWindow($this->getInventory());
			$this->propertyManager->setLong(self::DATA_TRADING_PLAYER_EID, $player->getId());
		}
	}

	public function getInventory() : TradingInventory{
		return new TradingInventory($this);
	}

	public function getDisplayName() : string{
		return self::$names[$this->getProfession()][$this->getCareer()] ?? "Villager";
	}

}