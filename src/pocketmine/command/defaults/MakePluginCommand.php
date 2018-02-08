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

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\FolderPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class MakePluginCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "Creates a Phar plugin from a unarchived",
            '/makeplugin <pluginName>',
            ["mp"]
        );

        $this->setPermission("altay.command.makeplugin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) < 1){
            throw new InvalidCommandSyntaxException();
        }

        $pluginName = trim(implode(" ", $args));
        if($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof Plugin)){
            $sender->sendMessage(TextFormat::RED . "Invalid plugin name, check the name case.");
            return true;
        }
        $description = $plugin->getDescription();

        if(!($plugin->getPluginLoader() instanceof FolderPluginLoader)){
            $sender->sendMessage(TextFormat::RED . "Plugin " . $description->getName() . " is not in folder structure.");
            return true;
        }

        $pharPath  = Server::getInstance()->getPluginPath() . "Altay" . DIRECTORY_SEPARATOR . $description->getFullName() . ".phar";
        if(file_exists($pharPath)){
            $sender->sendMessage("Phar plugin already exists, deleting...");
            unlink($pharPath);
        }

        $phar = new \Phar($pharPath);
        $phar->setMetadata([
            "name" => $description->getName(),
            "version" => $description->getVersion(),
            "main" => $description->getMain(),
            "api" => $description->getCompatibleApis(),
            "depend" => $description->getDepend(),
            "description" => $description->getDescription(),
            "authors" => $description->getAuthors(),
            "website" => $description->getWebsite(),
            "creationDate" => time()
        ]);

        $phar->setStub('<?php echo "Altay plugin ' . $description->getFullName() . '\nThis file has been generated using Turanic at ' . date("r") . '.\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $reflection = new \ReflectionClass("pocketmine\\plugin\\PluginBase");
        $file = $reflection->getProperty("file");
        $file->setAccessible(true);
        $folderPath = str_replace("\\", "/", rtrim($file->getValue($plugin), "\\/").DIRECTORY_SEPARATOR);

        $phar->startBuffering();

        /** @var \SplFileInfo $file */
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath)) as $file){
            $path = ltrim(str_replace(["\\", $pharPath], ["/", ""], $file), "/");
            if($path{0} === "." or strpos($path, "/.") !== false){
                continue;
            }
            $phar->addFile($file->getPathName(), $path);
            $sender->sendMessage("[".\pocketmine\NAME."] Added ".str_replace($folderPath, "", $path));
        }

        foreach($phar as $file => $finfo){
            /** @var \PharFileInfo $finfo */
            if($finfo->getSize() > (1024 * 512)){
                $finfo->compress(\Phar::GZ);
            }
        }
        $phar->stopBuffering();

        $sender->sendMessage("Phar plugin " . $description->getFullName() . " has been created on " . $pharPath);

        return true;
    }
}