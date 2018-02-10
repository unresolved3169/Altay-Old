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

/**
 * Command handling related classes
 */
namespace pocketmine\command;

use pocketmine\command\overload\CommandData;
use pocketmine\command\overload\CommandOverload;
use pocketmine\lang\TextContainer;
use pocketmine\event\TimingsHandler;
use pocketmine\lang\TranslationContainer;
use pocketmine\command\overload\CommandParameter;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

abstract class Command{

	/** @var string */
	private $name;

	/** @var string */
	private $nextLabel;

	/** @var string */
	private $label;

	/** @var string[] */
	private $aliases = [];

	/**
	 * @var string[]
	 */
	private $activeAliases = [];

	/** @var CommandMap */
	private $commandMap = null;

	/** @var string */
	protected $description = "";

	/** @var string */
	protected $usageMessage;

	/** @var string|null */
	private $permission = null;

	/** @var string */
	private $permissionMessage = null;

	/** @var TimingsHandler */
	public $timings;

	/** @var array */
	private $overloads = [];

	private $permissionLevel = AdventureSettingsPacket::PERMISSION_NORMAL;

    /**
     * @param string $name
     * @param string $description
     * @param string $usageMessage
     * @param string[] $aliases
     * @param array|null $parameters
     */
	public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [], array $parameters = null){
		$this->name = $name;
		$this->setLabel($name);
		$this->setDescription($description);
		$this->usageMessage = $usageMessage ?? ("/" . $name);
		$this->setAliases($aliases);
		$this->addOverload(new CommandOverload("default", $parameters ?? [new CommandParameter("args", CommandParameter::ARG_TYPE_STRING)]));
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 *
	 * @return mixed
	 */
	abstract public function execute(CommandSender $sender, string $commandLabel, array $args);

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string|null
	 */
	public function getPermission(){
		return $this->permission;
	}


	/**
	 * @param string|null $permission
	 */
	public function setPermission(string $permission = null){
		$this->permission = $permission;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function testPermission(CommandSender $target) : bool{
		if($this->testPermissionSilent($target)){
		    if($target instanceof Player){
		        if($target->getCommandPermission() >= $this->getPermissionLevel()){
                    return true;
                }
            }else{
                return true;
            }
		}

		if($this->permissionMessage === null){
			$target->sendMessage($target->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
		}elseif($this->permissionMessage !== ""){
			$target->sendMessage(str_replace("<permission>", $this->getPermission(), $this->permissionMessage));
		}

		return false;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function testPermissionSilent(CommandSender $target) : bool{
		if(($perm = $this->getPermission()) === null or $perm === ""){
			return true;
		}

		foreach(explode(";", $perm) as $permission){
			if($target->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getLabel() : string{
		return $this->label;
	}

	public function setLabel(string $name) : bool{
		$this->nextLabel = $name;
		if(!$this->isRegistered()){
			if($this->timings instanceof TimingsHandler){
				$this->timings->remove();
			}
			$this->timings = new TimingsHandler("** Command: " . $name);
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 *
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function register(CommandMap $commandMap) : bool{
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function unregister(CommandMap $commandMap) : bool{
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = null;
			$this->activeAliases = $this->aliases;
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	private function allowChangesFrom(CommandMap $commandMap) : bool{
		return $this->commandMap === null or $this->commandMap === $commandMap;
	}

	/**
	 * @return bool
	 */
	public function isRegistered() : bool{
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array{
		return $this->activeAliases;
	}

	/**
	 * @return string
	 */
	public function getPermissionMessage() : string{
		return $this->permissionMessage;
	}

	/**
	 * @return string
	 */
	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getUsage() : string{
		return $this->usageMessage;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases){
		$this->aliases = $aliases;
		if(!$this->isRegistered()){
			$this->activeAliases = $aliases;
		}
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description){
        if(strlen($description) > 0 and $description{0} == '%'){
            $description = Server::getInstance()->getLanguage()->translateString($description);
        }
		$this->description = $description;
	}

	/**
	 * @param string $permissionMessage
	 */
	public function setPermissionMessage(string $permissionMessage){
		$this->permissionMessage = $permissionMessage;
	}

	/**
	 * @param string $usage
	 */
	public function setUsage(string $usage){
		$this->usageMessage = $usage;
	}

	/**
	 * @param CommandSender        $source
	 * @param TextContainer|string $message
	 * @param bool                 $sendToSource
	 */
	public static function broadcastCommandMessage(CommandSender $source, $message, bool $sendToSource = true){
		if($message instanceof TextContainer){
			$m = clone $message;
			$result = "[" . $source->getName() . ": " . ($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() . "]";

			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		}else{
			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = new TranslationContainer(TextFormat::GRAY . TextFormat::ITALIC . "%chat.type.admin", [$source->getName(), $message]);
		}

		if($sendToSource === true and !($source instanceof ConsoleCommandSender)){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof CommandSender){
				if($user instanceof ConsoleCommandSender){
					$user->sendMessage($result);
				}elseif($user !== $source){
					$user->sendMessage($colored);
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString() : string{
		return $this->name;
	}

	public function getCommandData() : CommandData{
	    return new CommandData($this);
    }

    /**
     * @return array
     */
    public function getOverloads(): array{
        return $this->overloads;
    }

    public function addOverload(CommandOverload $overload){
        $this->overloads[$overload->getName()] = $overload;
    }

    public function removeAllOverload(){
        $this->overloads = [];
    }

    public function getOverload(string $overloadName) : ?CommandOverload{
        return $this->overloads[$overloadName] ?? null;
    }

    public function setOverloads(array $overloads): void{
        $this->overloads = $overloads;
    }

    public function getPermissionLevel(): int{
        return $this->permissionLevel;
    }

    public function setPermissionLevel(int $permissionLevel): void{
        $this->permissionLevel = $permissionLevel;
    }
}
