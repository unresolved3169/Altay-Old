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

namespace pocketmine\entity\behaviors;

use pocketmine\entity\Mob;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Tool;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\MainLogger;

class LootGenerator{

    /** @var string */
    private $lootName = "empty";
    /** @var Mob|null */
    private $entity;
    /** @var array */
    private $lootJson = [];

    public function __construct($lootName = "empty", ?Mob $entity = null){
        $lootName = str_replace(["loot_tables/", ".json"], "", $lootName);
        $this->lootName = $lootName;
        $this->lootJson = EntityProperties::getLootTables()[$lootName];
        $this->entity = $entity;
    }

    public function getRandomloot(){
        $items = [];
        if (!isset($this->lootJson["pools"])){
            return $items;
        }
        foreach ($this->lootJson["pools"] as $rolls){//TODO sub-pools, see armor chain etc
            //TODO roll conditions.. :(
            //TODO i saw "tiers" and have no idea what these do
            $array = [];
            $maxrolls = $rolls["rolls"];//TODO: $rolls["conditions"]
            while ($maxrolls > 0){
                $maxrolls--;
                //TODO debug this roll condition check
                if (isset($rolls["conditions"])){
                    if (!self::checkConditions($this->entity, $rolls["conditions"])) continue;
                }
                //
                foreach ($rolls["entries"] as $index => $entries){
                    $array[] = $entries["weight"] ?? 1;
                }
            }
            if (count($array) > 1)
                $val = $rolls["entries"][$this->getRandomWeightedElement($array)] ?? [];
            else
                $val = $rolls["entries"][0] ?? [];
            //typecheck
            if ($val["type"] == "loot_table"){
                $loottable = new LootGenerator($val["name"], $this->entity);
                $items = array_merge($items, $loottable->getRandomLoot());
                unset($loottable);
            } elseif ($val["type"] == "item"){
                print $val["name"] . PHP_EOL;
                //name fix
                if ($val["name"] == "minecraft:fish" || $val["name"] == "fish") $val["name"] = "raw_fish";//TODO proper name fixes via API
                $item = ItemFactory::fromString($val["name"]);
                if (isset($val["functions"])){
                    foreach ($val["functions"] as $function){
                        switch ($functionname = str_replace("minecraft:", "", $function["function"])){
                            case "set_damage": {
                                if ($item instanceof Tool) $item->setDamage(mt_rand($function["damage"]["min"] * $item->getMaxDurability(), $function["damage"]["max"] * $item->getMaxDurability()));
                                else $item->setDamage(mt_rand($function["damage"]["min"], $function["damage"]["max"]));
                                break;
                            }
                            case "set_data": {
                                //fish fix, blame mojang
                                switch ($item->getId()){
                                    case Item::RAW_FISH: {
                                        switch ($function["data"]){
                                            case 1:
                                                $item = Item::get(Item::RAW_SALMON, $item->getDamage(), $item->getCount(), $item->getCompoundTag());
                                                break;
                                            case 2:
                                                $item = Item::get(Item::CLOWNFISH, $item->getDamage(), $item->getCount(), $item->getCompoundTag());
                                                break;
                                            case 3:
                                                $item = Item::get(Item::PUFFERFISH, $item->getDamage(), $item->getCount(), $item->getCompoundTag());
                                                break;
                                            default:
                                                break;
                                        }
                                        break;
                                    }
                                    default: {
                                        $item->setDamage($function["data"]);
                                    }
                                }
                                break;
                            }
                            case "set_count": {
                                $item->setCount(mt_rand($function["count"]["min"], $function["count"]["max"]));
                                break;
                            }
                            case "furnace_smelt": {
                                if (isset($function["conditions"])){
                                    if (!self::checkConditions($this->entity, $function["conditions"])) break;
                                }
                                // todo foreach condition API::checkConditions
                                if ((!is_null($this->entity) && $this->entity->isOnFire()) || is_null($this->entity))
                                    $item = Server::getInstance()->getCraftingManager()->matchFurnaceRecipe($item)->getResult();
                                break;
                            }
                            case "enchant_randomly": {
                                //TODO
                                break;
                            }
                            case "enchant_with_levels": {
                                /*
                            "function": "enchant_with_levels",
                            "levels": 30,
                            "treasure": true
                                 */
                                //TODO
                                break;
                            }
                            case "looting_enchant": {
                                $item->setCount($item->getCount() + mt_rand($function["count"]["min"], $function["count"]["max"]));
                                break;
                            }
                            case "enchant_random_gear": {
                                break;
                            }
                            case "set_data_from_color_index": {
                                //TODO maybe use ColorBlockMetaHelper::getColorFromMeta();
                                break;
                            }
                            default:
                                assert("Unknown looting table function $functionname, skipping");
                        }
                    }
                }
                $items[] = $item;
            } elseif ($val['type'] === "empty"){

            }
        }
        return $items;
    }

    public static function checkConditions(Mob $entity, array $conditions){
        $target = null;
        foreach ($conditions as $value){
            switch ($value["condition"]){
                case "entity_properties": {//function condition
                    switch ($value["entity"]){
                        case "this": {
                            $target = $entity;
                            break;
                        }
                        default: {
                            MainLogger::getLogger()->debug("(Yet) Unknown target type: " . $value["entity"]);
                            return false;
                        }
                    }
                    foreach ($value["properties"] as $property => $propertyValue){
                        switch ($property){
                            case "on_fire": {
                                if (!$target->isOnFire()) return false;
                                break;
                            }
                            default: {
                                MainLogger::getLogger()->debug("(Yet) Unknown entity property: " . $property);
                                return false;
                            }
                        }
                    }
                    break;
                }
                case "killed_by_player": {//roll condition
                    // TODO recode/recheck/recode etc
                    if (($event = $entity->getLastDamageCause()) instanceof EntityDamageEvent and $event instanceof EntityDamageByEntityEvent){//TODO fix getLastDamageCause on null
                        if (!$event->getDamager() instanceof Player) return false;
                    }
                    break;
                }
                case "killed_by_entity": {//roll condition
                    // TODO recode/recheck/recode etc
                    if (($event = $entity->getLastDamageCause()) instanceof EntityDamageEvent and $event instanceof EntityDamageByEntityEvent){//TODO fix getLastDamageCause on null
                        $damager = $event->getDamager();
                        if ($event instanceof EntityDamageByChildEntityEvent){
                            $damager = $event->getChild()->getOwningEntity();
                        }

                        MainLogger::getLogger()->debug("Save ID of damager ".$damager->getSaveId().", searched for ".$value["entity_type"]);
                        if ($event->getDamager()->getSaveId() !== $value["entity_type"]) return false;
                    }
                    break;
                }
                case "random_chance_with_looting": {//roll condition
                    MainLogger::getLogger()->debug("Chance: ". $value["chance"] . " Looting Multiplier: ". $value["looting_multiplier"]);
                    break;
                }
                case "random_difficulty_chance": {//loot condition //return nothing yet, those are roll-repeats or so
                    MainLogger::getLogger()->debug("Chance: ".$value["default_chance"]);
                    foreach ($value as $difficultyString => $chance){
                        var_dump($difficultyString . " => " . $chance);
                        if ($entity->getLevel()->getDifficulty() === Level::getDifficultyFromString($difficultyString)){
                            MainLogger::getLogger()->debug("Chance: ".$chance);
                        }
                    }
                    break;
                }
                case "random_regional_difficulty_chance": {//roll condition
                    //TODO
                    //no break, send default message
                }
                default: {
                    MainLogger::getLogger()->debug("(Yet) Unknown condition: " . $value["condition"]);
                }
            }
        }
        return true;
    }

    public static function getRandomWeightedElement(array $weightedValues){
        if (empty($weightedValues)){
            throw new \InvalidArgumentException("Config error! No sets exist in the config - don't you want to give the players anything?");
        }
        $rand = mt_rand(1, (int)array_sum($weightedValues));

        foreach ($weightedValues as $key => $value){
            $rand -= $value;
            if ($rand <= 0){
                return $key;
            }
        }
        return -1;
    }

}