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

/**
 * All the Item classes
 */
namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;

class Item implements ItemIds, \JsonSerializable{
	public const TAG_ENCH = "ench";
	public const TAG_DISPLAY = "display";
	public const TAG_BLOCK_ENTITY_TAG = "BlockEntityTag";

	public const TAG_DISPLAY_NAME = "Name";
	public const TAG_DISPLAY_LORE = "Lore";


	/** @var LittleEndianNBTStream */
	private static $cachedParser = null;

	private static function parseCompoundTag(string $tag) : CompoundTag{
		if($tag === ""){
			throw new \InvalidArgumentException("No NBT data found in supplied string");
		}

		if(self::$cachedParser === null){
			self::$cachedParser = new LittleEndianNBTStream();
		}

		self::$cachedParser->read($tag);
		$data = self::$cachedParser->getData();

		if(!($data instanceof CompoundTag)){
			throw new \InvalidArgumentException("Invalid item NBT string given, it could not be deserialized");
		}

		return $data;
	}

	private static function writeCompoundTag(CompoundTag $tag) : string{
		if(self::$cachedParser === null){
			self::$cachedParser = new LittleEndianNBTStream();
		}

		self::$cachedParser->setData($tag);
		return self::$cachedParser->write();
	}

	/**
	 * Returns a new Item instance with the specified ID, damage, count and NBT.
	 *
	 * This function redirects to {@link ItemFactory#get}.
	 *
	 * @param int                $id
	 * @param int                $meta
	 * @param int                $count
	 * @param CompoundTag|string $tags
	 *
	 * @return Item
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = "") : Item{
		return ItemFactory::get($id, $meta, $count, $tags);
	}

	/**
	 * Tries to parse the specified string into Item ID/meta identifiers, and returns Item instances it created.
	 *
	 * This function redirects to {@link ItemFactory#fromString}.
	 *
	 * @param string $str
	 * @param bool   $multiple
	 *
	 * @return Item[]|Item
	 */
	public static function fromString(string $str, bool $multiple = false){
		return ItemFactory::fromString($str, $multiple);
	}


	/** @var Item[] */
	private static $creative = [];

	public static function initCreativeItems(){
		self::clearCreativeItems();

		$creativeItems = new Config(\pocketmine\RESOURCE_PATH . "creativeitems.json", Config::JSON, []);

		foreach($creativeItems->getAll() as $data){
			$item = Item::jsonDeserialize($data);
			if($item->getName() === "Unknown"){
				continue;
			}
			self::addCreativeItem($item);
		}
	}

	public static function clearCreativeItems(){
		Item::$creative = [];
	}

	public static function getCreativeItems() : array{
		return Item::$creative;
	}

	public static function addCreativeItem(Item $item){
		Item::$creative[] = clone $item;
	}

	public static function removeCreativeItem(Item $item){
		$index = self::getCreativeItemIndex($item);
		if($index !== -1){
			unset(Item::$creative[$index]);
		}
	}

	public static function isCreativeItem(Item $item) : bool{
		return Item::getCreativeItemIndex($item) !== -1;
	}

	/**
	 * @param $index
	 *
	 * @return Item|null
	 */
	public static function getCreativeItem(int $index){
		return Item::$creative[$index] ?? null;
	}

	public static function getCreativeItemIndex(Item $item) : int{
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return $i;
			}
		}

		return -1;
	}

	/** @var Block|null */
	protected $block;
	/** @var int */
	protected $id;
	/** @var int */
	protected $meta;
	/** @var string */
	private $tags = "";
	/** @var CompoundTag|null */
	private $cachedNBT = null;
	/** @var int */
	public $count = 1;
	/** @var string */
	protected $name;

	/**
	 * Constructs a new Item type. This constructor should ONLY be used when constructing a new item TYPE to register
	 * into the index.
	 *
	 * NOTE: This should NOT BE USED for creating items to set into an inventory. Use {@link ItemFactory#get} for that
	 * purpose.
	 *
	 * @param int    $id
	 * @param int    $meta
	 * @param string $name
	 */
	public function __construct(int $id, int $meta = 0, string $name = "Unknown"){
		$this->id = $id & 0xffff;
		$this->setDamage($meta);
		$this->name = $name;
		if(!isset($this->block) and $this->id <= 0xff){
			$this->block = BlockFactory::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
	}

	/**
	 * Sets the Item's NBT
	 *
	 * @param CompoundTag|string $tags
	 *
	 * @return Item
	 */
	public function setCompoundTag($tags) : Item{
		if($tags instanceof CompoundTag){
			$this->setNamedTag($tags);
		}else{
			$this->tags = (string) $tags;
			$this->cachedNBT = null;
		}

		return $this;
	}

	/**
	 * Returns the serialized NBT of the Item
	 * @return string
	 */
	public function getCompoundTag() : string{
		return $this->tags;
	}

	/**
	 * Returns whether this Item has a non-empty NBT.
	 * @return bool
	 */
	public function hasCompoundTag() : bool{
		return $this->tags !== "";
	}

	/**
	 * @return bool
	 */
	public function hasCustomBlockData() : bool{
		return $this->getNamedTagEntry(self::TAG_BLOCK_ENTITY_TAG) instanceof CompoundTag;
	}

	public function clearCustomBlockData(){
		$this->removeNamedTagEntry(self::TAG_BLOCK_ENTITY_TAG);
		return $this;
	}

	/**
	 * @param CompoundTag $compound
	 *
	 * @return Item
	 */
	public function setCustomBlockData(CompoundTag $compound) : Item{
		$tags = clone $compound;
		$tags->setName(self::TAG_BLOCK_ENTITY_TAG);
		$this->setNamedTagEntry($tags);

		return $this;
	}

	/**
	 * @return CompoundTag|null
	 */
	public function getCustomBlockData() : ?CompoundTag{
		$tag = $this->getNamedTagEntry(self::TAG_BLOCK_ENTITY_TAG);
		return $tag instanceof CompoundTag ? $tag : null;
	}

	/**
	 * @return bool
	 */
	public function hasEnchantments() : bool{
		return $this->getNamedTagEntry(self::TAG_ENCH) instanceof ListTag;
	}

	/**
	 * @param int $id
	 * @param int $level
	 *
	 * @return bool
	 */
	public function hasEnchantment(int $id, int $level = -1) : bool{
		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			return false;
		}

		/** @var CompoundTag $entry */
		foreach($ench as $entry){
			if($entry->getShort("id") === $id and ($level === -1 or $entry->getShort("lvl") === $level)){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $id
	 *
	 * @return EnchantmentInstance|null
	 */
	public function getEnchantment(int $id) : ?EnchantmentInstance{
		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			return null;
		}

		/** @var CompoundTag $entry */
		foreach($ench as $entry){
			if($entry->getShort("id") === $id){
				$e = Enchantment::getEnchantment($entry->getShort("id"));
				if($e !== null){
					return new EnchantmentInstance($e, $entry->getShort("lvl"));
				}
			}
		}

		return null;
	}

	/**
	 * @param int $id
	 * @param int $level
	 */
	public function removeEnchantment(int $id, int $level = -1) : void{
		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			return;
		}

		/** @var CompoundTag $entry */
		foreach($ench as $k => $entry){
			if($entry->getShort("id") === $id and ($level === -1 or $entry->getShort("lvl") === $level)){
                $ench->remove($k);
				break;
			}
		}

		$this->setNamedTagEntry($ench);
	}

	public function removeEnchantments() : void{
		$this->removeNamedTagEntry(self::TAG_ENCH);
	}

	/**
	 * @param EnchantmentInstance $enchantment
	 */
	public function addEnchantment(EnchantmentInstance $enchantment) : void{
		$found = false;

		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			$ench = new ListTag(self::TAG_ENCH, [], NBT::TAG_Compound);
		}else{
			/** @var CompoundTag $entry */
			foreach($ench as $k => $entry){
				if($entry->getShort("id") === $enchantment->getId()){
				    $ench->set($k, new CompoundTag("", [
						new ShortTag("id", $enchantment->getId()),
						new ShortTag("lvl", $enchantment->getLevel())
					]));
					$found = true;
					break;
				}
			}
		}

		if(!$found){
		    $ench->push(new CompoundTag("", [
				new ShortTag("id", $enchantment->getId()),
				new ShortTag("lvl", $enchantment->getLevel())
			]));
		}

		$this->setNamedTagEntry($ench);
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	public function getEnchantments() : array{
		/** @var EnchantmentInstance[] $enchantments */
		$enchantments = [];

		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if($ench instanceof ListTag){
			/** @var CompoundTag $entry */
			foreach($ench as $entry){
				$e = Enchantment::getEnchantment($entry->getShort("id"));
				if($e !== null){
					$enchantments[] = new EnchantmentInstance($e, $entry->getShort("lvl"));
				}
			}
		}

		return $enchantments;
	}

	/**
	 * Returns the level of the enchantment on this item with the specified ID, or 0 if the item does not have the
	 * enchantment.
	 *
	 * @param int $enchantmentId
	 *
	 * @return int
	 */
	public function getEnchantmentLevel(int $enchantmentId) : int{
		$ench = $this->getNamedTag()->getListTag(self::TAG_ENCH);
		if($ench !== null){
			/** @var CompoundTag $entry */
			foreach($ench as $entry){
				if($entry->getShort("id") === $enchantmentId){
					return $entry->getShort("lvl");
				}
			}
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	public function hasCustomName() : bool{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag){
			return $display->hasTag(self::TAG_DISPLAY_NAME);
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getCustomName() : string{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag){
			return $display->getString(self::TAG_DISPLAY_NAME, "");
		}

		return "";
	}

	/**
	 * @param string $name
	 *
	 * @return Item
	 */
	public function setCustomName(string $name) : Item{
		if($name === ""){
			$this->clearCustomName();
		}

		/** @var CompoundTag $display */
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if(!($display instanceof CompoundTag)){
			$display = new CompoundTag(self::TAG_DISPLAY);
		}

		$display->setString(self::TAG_DISPLAY_NAME, $name);
		$this->setNamedTagEntry($display);

		return $this;
	}

	/**
	 * @return Item
	 */
	public function clearCustomName() : Item{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag){
			$display->removeTag(self::TAG_DISPLAY_NAME);

			if($display->getCount() === 0){
				$this->removeNamedTagEntry($display->getName());
			}else{
				$this->setNamedTagEntry($display);
			}
		}

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getLore() : array{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag and ($lore = $display->getListTag(self::TAG_DISPLAY_LORE)) !== null){
			return $lore->getAllValues();
		}

		return [];
	}

	/**
	 * @param string[] $lines
	 *
	 * @return Item
	 */
	public function setLore(array $lines) : Item{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if(!($display instanceof CompoundTag)){
			$display = new CompoundTag(self::TAG_DISPLAY, []);
		}

		$display->setTag(new ListTag(self::TAG_DISPLAY_LORE, array_map(function(string $str) : StringTag{
			return new StringTag("", $str);
		}, $lines), NBT::TAG_String));

		$this->setNamedTagEntry($display);

		return $this;
	}

	/**
	 * @param string $name
	 * @return NamedTag|null
	 */
	public function getNamedTagEntry(string $name) : ?NamedTag{
		return $this->getNamedTag()->getTag($name);
	}

	public function setNamedTagEntry(NamedTag $new) : void{
		$tag = $this->getNamedTag();
		$tag->setTag($new);
		$this->setNamedTag($tag);
	}

	public function removeNamedTagEntry(string $name) : void{
		$tag = $this->getNamedTag();
		$tag->removeTag($name);
		$this->setNamedTag($tag);
	}

	/**
	 * Returns a tree of Tag objects representing the Item's NBT. If the item does not have any NBT, an empty CompoundTag
	 * object is returned to allow the caller to manipulate and apply back to the item.
	 *
	 * @return CompoundTag
	 */
	public function getNamedTag() : CompoundTag{
		if(!$this->hasCompoundTag() and $this->cachedNBT === null){
			$this->cachedNBT = new CompoundTag();
		}

		return $this->cachedNBT ?? ($this->cachedNBT = self::parseCompoundTag($this->tags));
	}

	/**
	 * Sets the Item's NBT from the supplied CompoundTag object.
	 * @param CompoundTag $tag
	 *
	 * @return Item
	 */
	public function setNamedTag(CompoundTag $tag) : Item{
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->cachedNBT = $tag;
		$this->tags = self::writeCompoundTag($tag);

		return $this;
	}

	/**
	 * Removes the Item's NBT.
	 * @return Item
	 */
	public function clearNamedTag() : Item{
		return $this->setCompoundTag("");
	}

	/**
	 * @return int
	 */
	public function getCount() : int{
		return $this->count;
	}

	/**
	 * @param int $count
	 * @return Item
	 */
	public function setCount(int $count) : Item{
		$this->count = $count;

		return $this;
	}

	/**
	 * Pops an item from the stack and returns it, decreasing the stack count of this item stack by one.
	 * @return Item
	 *
	 * @throws \InvalidStateException if the count is less than or equal to zero, or if the stack is air.
	 */
	public function pop() : Item{
		if($this->isNull()){
			throw new \InvalidStateException("Cannot pop an item from a null stack");
		}

		$item = clone $this;
		$item->setCount(1);

		$this->count--;

		return $item;
	}

	public function isNull() : bool{
		return $this->count <= 0 or $this->id === Item::AIR;
	}

	/**
	 * Returns the name of the item, or the custom name if it is set.
	 * @return string
	 */
	final public function getName() : string{
		return $this->hasCustomName() ? $this->getCustomName() : $this->name;
	}

	/**
	 * @return bool
	 */
	final public function canBePlaced() : bool{
		return $this->block !== null and $this->block->canBePlaced();
	}

	/**
	 * Returns the block corresponding to this Item.
	 * @return Block
	 */
	public function getBlock() : Block{
		if($this->block instanceof Block){
			return clone $this->block;
		}else{
			return BlockFactory::get(self::AIR);
		}
	}

	/**
	 * @return int
	 */
	final public function getId() : int{
		return $this->id;
	}

	/**
	 * @return int
	 */
	final public function getDamage() : int{
		return $this->meta;
	}

	/**
	 * @param int $meta
	 * @return Item
	 */
	public function setDamage(int $meta) : Item{
		$this->meta = $meta !== -1 ? $meta & 0x7FFF : -1;

		return $this;
	}

	/**
	 * Returns whether this item can match any item with an equivalent ID with any meta value.
	 * Used in crafting recipes which accept multiple variants of the same item, for example crafting tables recipes.
	 *
	 * @return bool
	 */
	public function hasAnyDamageValue() : bool{
		return $this->meta === -1;
	}

	/**
	 * Returns the highest amount of this item which will fit into one inventory slot.
	 * @return int
	 */
	public function getMaxStackSize() : int{
		return 64;
	}

	/**
	 * Returns the time in ticks which the item will fuel a furnace for.
	 * @return int
	 */
	public function getFuelTime() : int{
		return 0;
	}

	/**
	 * Returns how many points of damage this item will deal to an entity when used as a weapon.
	 * @return int
	 */
	public function getAttackPoints() : int{
		return 1;
	}

	/**
	 * Returns how many armor points can be gained by wearing this item.
	 * @return int
	 */
	public function getDefensePoints() : int{
		return 0;
	}

	/**
	 * @param Entity|Block $object
	 *
	 * @return bool
	 */
	public function useOn($object){
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTool(){
		return false;
	}

	/**
	 * Returns what type of block-breaking tool this is. Blocks requiring the same tool type as the item will break
	 * faster (except for blocks requiring no tool, which break at the same speed regardless of the tool used)
	 *
	 * @return int
	 */
	public function getBlockToolType() : int{
		return BlockToolType::TYPE_NONE;
	}

	/**
	 * Returns the harvesting power that this tool has. This affects what blocks it can mine when the tool type matches
	 * the mined block.
	 * This should return 1 for non-tiered tools, and the tool tier for tiered tools.
	 *
	 * @see Block::getToolHarvestLevel()
	 *
	 * @return int
	 */
	public function getBlockToolHarvestLevel() : int{
		return 0;
	}

	public function isPickaxe(){
		return false;
	}

	public function isAxe(){
		return false;
	}

	public function isSword(){
		return false;
	}

	public function isShovel(){
		return false;
	}

	public function isHoe(){
		return false;
	}

	public function isShears(){
		return false;
	}

	public function getMiningEfficiency(Block $block) : float{
		return 1;
	}

	/**
	 * Called when a player uses this item on a block.
	 *
	 * @param Player  $player
	 * @param Block   $blockReplace
	 * @param Block   $blockClicked
	 * @param int     $face
	 * @param Vector3 $clickVector
	 *
	 * @return bool
	 */
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		return false;
	}

	/**
	 * Called when a player uses the item on air, for example throwing a projectile.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 *
	 * @param Player  $player
	 * @param Vector3 $directionVector
	 *
	 * @return bool
	 */
	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		return false;
	}

	/**
	 * Called when a player is using this item and releases it. Used to handle bow shoot actions.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 *
	 * @param Player $player
	 * @return bool
	 */
	public function onReleaseUsing(Player $player) : bool{
		return false;
	}

	/**
	 * Compares an Item to this Item and check if they match.
	 *
	 * @param Item $item
	 * @param bool $checkDamage Whether to verify that the damage values match.
	 * @param bool $checkCompound Whether to verify that the items' NBT match.
	 *
	 * @return bool
	 */
	final public function equals(Item $item, bool $checkDamage = true, bool $checkCompound = true) : bool{
		if($this->id === $item->getId() and ($checkDamage === false or $this->getDamage() === $item->getDamage())){
			if($checkCompound){
				if($item->getCompoundTag() === $this->getCompoundTag()){
					return true;
				}elseif($this->hasCompoundTag() and $item->hasCompoundTag()){
					//Serialized NBT didn't match, check the cached object tree.
					return $this->getNamedTag()->equals($item->getNamedTag());
				}
			}else{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns whether the specified item stack has the same ID, damage, NBT and count as this item stack.
	 * @param Item $other
	 *
	 * @return bool
	 */
	final public function equalsExact(Item $other) : bool{
		return $this->equals($other, true, true) and $this->count === $other->count;
	}

	/**
	 * @deprecated Use {@link Item#equals} instead, this method will be removed in the future.
	 *
	 * @param Item $item
	 * @param bool $checkDamage
	 * @param bool $checkCompound
	 *
	 * @return bool
	 */
	final public function deepEquals(Item $item, bool $checkDamage = true, bool $checkCompound = true) : bool{
		return $this->equals($item, $checkDamage, $checkCompound);
	}

	/**
	 * @return string
	 */
	final public function __toString() : string{
		return "Item " . $this->name . " (" . $this->id . ":" . ($this->hasAnyDamageValue() ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:0x" . bin2hex($this->getCompoundTag()) : "");
	}

	/**
	 * Returns an array of item stack properties that can be serialized to json.
	 *
	 * @return array
	 */
	final public function jsonSerialize() : array{
		$data = [
			"id" => $this->getId()
		];

		if($this->getDamage() !== 0){
			$data["damage"] = $this->getDamage();
		}

		if($this->getCount() !== 1){
			$data["count"] = $this->getCount();
		}

		if($this->hasCompoundTag()){
			$data["nbt_hex"] = bin2hex($this->getCompoundTag());
		}

		return $data;
	}

	/**
	 * Returns an Item from properties created in an array by {@link Item#jsonSerialize}
	 *
	 * @param array $data
	 * @return Item
	 */
	final public static function jsonDeserialize(array $data) : Item{
		return ItemFactory::get(
			(int) $data["id"],
			(int) ($data["damage"] ?? 0),
			(int) ($data["count"] ?? 1),
			(string) ($data["nbt"] ?? (isset($data["nbt_hex"]) ? hex2bin($data["nbt_hex"]) : "")) //`nbt` key might contain old raw data
		);
	}

	/**
	 * Serializes the item to an NBT CompoundTag
	 *
	 * @param int    $slot optional, the inventory slot of the item
	 * @param string $tagName the name to assign to the CompoundTag object
	 *
	 * @return CompoundTag
	 */
	public function nbtSerialize(int $slot = -1, string $tagName = "") : CompoundTag{
		$result = new CompoundTag($tagName, [
			new ShortTag("id", Binary::signShort($this->id)),
			new ByteTag("Count", Binary::signByte($this->count)),
			new ShortTag("Damage", $this->meta)
		]);

		if($this->hasCompoundTag()){
			$itemNBT = clone $this->getNamedTag();
			$itemNBT->setName("tag");
			$result->setTag($itemNBT);
		}

		if($slot !== -1){
			$result->setByte("Slot", $slot);
		}

		return $result;
	}

	/**
	 * Deserializes an Item from an NBT CompoundTag
	 *
	 * @param CompoundTag $tag
	 *
	 * @return Item
	 */
	public static function nbtDeserialize(CompoundTag $tag) : Item{
		if(!$tag->hasTag("id") or !$tag->hasTag("Count")){
			return ItemFactory::get(0);
		}

		$count = Binary::unsignByte($tag->getByte("Count"));
		$meta = $tag->getShort("Damage", 0);

		$idTag = $tag->getTag("id");
		if($idTag instanceof ShortTag){
			$item = ItemFactory::get(Binary::unsignShort($idTag->getValue()), $meta, $count);
		}elseif($idTag instanceof StringTag){ //PC item save format
			$item = ItemFactory::fromString($idTag->getValue());
			$item->setDamage($meta);
			$item->setCount($count);
		}else{
			throw new \InvalidArgumentException("Item CompoundTag ID must be an instance of StringTag or ShortTag, " . get_class($idTag) . " given");
		}

		$itemNBT = $tag->getCompoundTag("tag");
		if($itemNBT instanceof CompoundTag){
			/** @var CompoundTag $t */
			$t = clone $itemNBT;
			$t->setName("");
			$item->setNamedTag($t);
		}

		return $item;
	}

	public function __clone(){
		if($this->block !== null){
			$this->block = clone $this->block;
		}

		$this->cachedNBT = null;
	}

	// ALTAY

	public function getRepairCost() : int{
        return $this->getNamedTag()->getInt("RepairCost", 0);
    }

    public function setRepairCost(int $repairCost) : void{
	    $this->getNamedTag()->setInt("RepairCost", $repairCost);
    }

}
