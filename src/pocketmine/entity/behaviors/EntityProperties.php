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

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\utils\MainLogger;

class EntityProperties{

    protected static $behaviors = [];
    protected static $lootTables = [];

    /** @var string */
    private $behavior = "empty";
    /** @var array */
    private $behaviorFile;
    /** @var null|Mob */
    private $entity;
    /** @var array */
    private $components = [];
    /** @var array */
    private $componentGroups = [];

    public function __construct(string $behavior, ?Mob $mob = null){
        $this->behavior = $behavior;
        $this->behaviorFile = self::$behaviors[$this->behavior];
        $this->entity = $mob;

        foreach($this->getBehaviorComponents() as $name => $data){
            $this->applyComponent($name, $data);
        }
    }

    public function getBehaviorComponents(){
        return $this->behaviorFile["minecraft:entity"]["components"];
    }

    public function setComponent(string $component, array $value){
        $this->components[$component] = $value;
    }

    public function getBehaviorComponentGroups(){
        return $this->behaviorFile["minecraft:entity"]["component_groups"];
    }

    public function getBehaviorComponentGroup(string $groupName){
        return $this->getBehaviorComponentGroups()[$groupName] ?? null;
    }

    public function getBehaviorEvents(){
        return $this->behaviorFile["minecraft:entity"]["events"];
    }

    public function getActiveComponentGroups(){
        return $this->componentGroups;
    }

    public function addActiveComponentGroup(string $groupName){
        if(!is_null(($group = $this->getBehaviorComponentGroup($groupName)))){
            $this->componentGroups[$groupName] = $group;
            foreach($group as $name => $data){
                $this->applyComponent($name, $data);
            }
        }
    }

    public function removeActiveComponentGroup(string $groupName){
        if(!is_null(($group = $this->getbehaviorComponentGroup($groupName)))){
            $this->componentGroups[$groupName] = $group;
            //TODO set the groups properties
            unset($this->componentGroups[$groupName]);
        }
    }

    public function applyComponent(string $name, array $data) : void{
        $this->setComponent($name, $data);
        switch($name){
            case "minecraft:loot":
                if(isset($data["table"]))
                    $this->entity->setLootGenerator(new LootGenerator($data["table"], $this->entity));
                break;
            case "minecraft:collision_box":
                $this->entity->setWidthandHeight(floatval($data["width"]), floatval($data["height"]));
                break;
            case "minecraft:scale":
                $this->entity->setScale(floatval($data["value"]));
                break;
            case "minecraft:is_baby":
                $this->entity->setGenericFlag(Entity::DATA_FLAG_BABY, true);//TODO set falso on removal
                break;
            case "minecraft:is_tamed":
                //TODO set falso on removal
                $this->entity->setGenericFlag(Entity::DATA_FLAG_TAMED, true);
                break;
            case "minecraft:can_climb":
                $this->entity->setCanClimb(true);
                break;
            case "minecraft:breathable":
                //TODO add other breathable tags
                if (isset($data["totalSupply"]))
                    $this->entity->setMaxAirSupplyTicks(intval($data["totalSupply"]));
                if(isset($data["breathesWater"]) && $data["breathesWater"] == true){
                    $this->entity->setMaxAirSupplyTicks(10000);//todo
                }
                break;

            case "minecraft:health":
                if(isset($data["max"]))
                    $this->entity->setMaxHealth(intval($data["max"]));
                if(isset($data["value"]) && $this->entity->ticksLived < 1){
                    if(is_array($data["value"])){
                        $this->entity->setHealth(floatval(mt_rand(($data["value"]["range_min"] ?? 1) * 10, ($data["value"]["range_max"] ?? 1) * 10) / 10));
                    }else{
                        $this->entity->setHealth(floatval($data["value"]));
                    }
                }

                break;
            case "minecraft:horse.jump_strength":
                // TODO : Optimize
                if(isset($data["max"]))
                    $this->entity->getAttributeMap()->getAttribute(Attribute::HORSE_JUMP_STRENGTH)->setMaxValue(floatval($data["max"]));
                if(isset($data["value"]) && $this->entity->ticksLived < 1){
                    if(is_array($data["value"])){
                        $this->entity->getAttributeMap()->getAttribute(Attribute::HORSE_JUMP_STRENGTH)->setValue(floatval(mt_rand(($data["value"]["range_min"] ?? 1) * 10, ($data["value"]["range_max"] ?? 1) * 10) / 10));
                    }else{
                        $this->entity->getAttributeMap()->getAttribute(Attribute::HORSE_JUMP_STRENGTH)->setValue(floatval($data["value"]));
                    }
                }
                break;

            case "minecraft:movement":
                if(isset($data["value"]) && $this->entity->ticksLived < 1){
                    if(is_array($data["value"])){
                        $this->entity->setDefaultMovementSpeed(floatval(mt_rand(($data["value"]["range_min"] ?? 1) * 10, ($data["value"]["range_max"] ?? 1) * 10) / 10));
                    }else{
                        $this->entity->setDefaultMovementSpeed(floatval($data["value"]));
                    }
                }
                break;

            case "minecraft:attack":
                if($this->entity->ticksLived < 1){
                    $this->entity->setDefaultAttackDamage(floatval($data["damage"]));
                }
                break;
            case "minecraft:rideable":
                if(isset($data["seat_count"]))
                    $this->entity->setSeatCount(intval($data["seat_count"]));
                if(isset($data["seats"]))
                    $this->entity->setSeats([$data["seats"]]); //TODO validate seatcount === count of "seats"
                break;
            case "minecraft:inventory":
                // TODO
                break;
            default:
                MainLogger::getLogger()->debug("Yapılandırılamadı : $name");
        }
    }

    public function applyEvent($behaviorEvent_data){
        var_dump("============ EVENT DATA ============");
        var_dump($behaviorEvent_data);
        foreach ($behaviorEvent_data as $function => $function_properties){
            var_dump("============ EVENT ============");
            var_dump($function);
            switch ($function){
                case "randomize": {
                    $array = [];
                    foreach ($function_properties as $index => $property){
                        $array[] = $property["weight"] ?? 1;
                    }
                    //TODO temp fix, remove when fixed
                    $subEvents = $function_properties[$this->getRandomWeightedElement($array)];
                    $this->applyEvent($subEvents);
                    break;
                }
                case "add": {
                    foreach ($function_properties as $function_property => $function_property_data){
                        var_dump($function_property);
                        switch ($function_property){
                            case "component_groups": {
                                foreach ($function_property_data as $componentgroup){
                                    $this->addActiveComponentGroup($componentgroup);
                                }
                                break;
                            }
                            default: {
                                $this->entity->getLevel()->getServer()->getLogger()->notice("Function \"" . $function_property . "\" for add component events is not coded into the plugin yet");
                            }
                        }
                    }
                    break;
                }
                case "remove": {
                    foreach ($function_properties as $function_property => $function_property_data){
                        var_dump($function_property);
                        switch ($function_property){
                            case "component_groups": {
                                foreach ($function_property_data as $componentgroup){
                                    $this->removeActiveComponentGroup($componentgroup);
                                }
                                break;
                            }
                            default: {
                                $this->entity->getLevel()->getServer()->getLogger()->notice("Function \"" . $function_property . "\" for remove component events is not coded into the plugin yet");
                            }
                        }
                    }
                    break;
                }
                case "weight": {
                    //just a property, ignore it
                    break;
                }
                default: {
                    $this->entity->getLevel()->getServer()->getLogger()->notice("Function \"" . $function . "\" for behavior events is not coded into the plugin yet");
                }
            }
        }
    }

    public static function init() : void{
        $path = \pocketmine\RESOURCE_PATH . "behaviors" . DIRECTORY_SEPARATOR;

        $behaviorsPath = $path . "entities" . DIRECTORY_SEPARATOR;
        self::saveToArray($behaviorsPath, self::$behaviors);

        $lootTablePath = $path . "loot_tables" . DIRECTORY_SEPARATOR;
        self::saveToArray($lootTablePath, self::$lootTables);
    }

    public static function saveToArray(string $path, array &$output, string $includeFolderName = ""){
        foreach(scandir($path) as $f){
            if($f == "." or $f == "..") continue;
            $file = $path . $f;

            if(is_dir($file)){
                self::saveToArray($file . DIRECTORY_SEPARATOR, $output, $f . DIRECTORY_SEPARATOR);
            }else{
                $index = substr($f, 0, -5);
                $index = $includeFolderName !== "" ? $includeFolderName . $index : $index;
                $output[$index] = json_decode(file_get_contents($file), true);
            }
        }
    }

    public static function getBehaviors(): array{
        return self::$behaviors;
    }

    public static function getLootTables(): array{
        return self::$lootTables;
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