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

class EntityProperties{

    protected static $behaviors = [];
    protected static $lootTables = [];

    public static function init() : void{
        $path = \pocketmine\RESOURCE_PATH . "behaviors" . DIRECTORY_SEPARATOR;
        var_dump($path);

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
                self::saveToArray($file . DIRECTORY_SEPARATOR,$output, $f . DIRECTORY_SEPARATOR);
            }else{
                $index = substr($f, 0, -5);
                $index = $includeFolderName !== "" ? $includeFolderName . $index : $index;
                $output[$index] = json_decode(file_get_contents($file), true);
            }
        }
    }
}