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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\command\data\CommandData;
use pocketmine\command\data\CommandEnum;
use pocketmine\command\data\CommandParameter;
use pocketmine\network\mcpe\NetworkSession;

class AvailableCommandsPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;

	/**
	 * @var string[]
	 * A list of every single enum value for every single command in the packet, including alias names.
	 */
	public $enumValues = [];
	/** @var int */
	private $enumValuesCount = 0;

	/**
	 * @var string[]
	 * A list of argument postfixes. Used for the /xp command's <int>L.
	 */
	public $postfixes = [];

	/**
	 * @var CommandEnum[]
	 * List of command enums, from command aliases to argument enums.
	 */
	public $enums = [];
	/**
	 * @var int[] string => int map of enum name to index
	 */
	private $enumMap = [];

	/**
	 * @var CommandData[]
	 * List of command data, including name, description, alias indexes and parameters.
	 */
	public $commandData = [];

	protected function decodePayload(){
		for($i = 0, $this->enumValuesCount = $this->getUnsignedVarInt(); $i < $this->enumValuesCount; ++$i){
			$this->enumValues[] = $this->getString();
		}

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->postfixes[] = $this->getString();
		}

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->enums[] = $this->getEnum();
		}

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$this->commandData[] = $this->getCommandData();
		}
	}

	protected function getEnum() : CommandEnum{
		$retval = new CommandEnum($this->getString());

		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			//Get the enum value from the initial pile of mess
			$retval->enumValues[] = $this->enumValues[$this->getEnumValueIndex()];
		}

		return $retval;
	}

	protected function putEnum(CommandEnum $enum){
		$this->putString($enum->enumName);

		$this->putUnsignedVarInt(count($enum->enumValues));
		foreach($enum->enumValues as $value){
			//Dumb bruteforce search. I hate this packet.
			$index = array_search($value, $this->enumValues, true);
			if($index === false){
				throw new \InvalidStateException("Enum value '$value' not found");
			}
			$this->putEnumValueIndex($index);
		}
	}

	protected function getEnumValueIndex() : int{
		if($this->enumValuesCount < 256){
			return $this->getByte();
		}elseif($this->enumValuesCount < 65536){
			return $this->getLShort();
		}else{
			return $this->getLInt();
		}
	}

	protected function putEnumValueIndex(int $index){
		if($this->enumValuesCount < 256){
			$this->putByte($index);
		}elseif($this->enumValuesCount < 65536){
			$this->putLShort($index);
		}else{
			$this->putLInt($index);
		}
	}

	protected function getCommandData() : CommandData{
		$retval = new CommandData();
		$retval->commandName = $this->getString();
		$retval->commandDescription = $this->getString();
		$retval->flags = $this->getByte();
		$retval->permission = $this->getByte();
		$retval->aliases = $this->enums[$this->getLInt()] ?? null;

		for($overloadIndex = 0, $overloadCount = $this->getUnsignedVarInt(); $overloadIndex < $overloadCount; ++$overloadIndex){
			for($paramIndex = 0, $paramCount = $this->getUnsignedVarInt(); $paramIndex < $paramCount; ++$paramIndex){
				$parameter = new CommandParameter($this->getString(), $this->getLInt(), $this->getBool());

				if($parameter->getParamType() & CommandParameter::ARG_FLAG_ENUM){
					$index = ($parameter->getParamType() & 0xffff);
					$parameter->enum = $this->enums[$index] ?? null;

					assert($parameter->enum !== null, "expected enum at $index, but got none");
				}elseif(($parameter->getParamType() & CommandParameter::ARG_FLAG_VALID) === 0){ //postfix (guessing)
					$index = ($parameter->getParamType() & 0xffff);
					$parameter->postfix = $this->postfixes[$index] ?? null;

					assert($parameter->postfix !== null, "expected postfix at $index, but got none");
				}

				$retval->overloads[$overloadIndex][$paramIndex] = $parameter;
			}
		}

		return $retval;
	}

	protected function putCommandData(CommandData $data){
		$this->putString($data->commandName);
		$this->putString($data->commandDescription);
		$this->putByte($data->flags);
		$this->putByte($data->permission);

		if($data->aliases !== null){
			$this->putLInt($this->enumMap[$data->aliases->enumName] ?? -1);
		}else{
			$this->putLInt(-1);
		}

		$this->putUnsignedVarInt(count($data->overloads));
		foreach($data->overloads as $overload){
			/** @var CommandParameter[] $overload */
			$this->putUnsignedVarInt(count($overload));
			foreach($overload as $parameter){
				$this->putString($parameter->paramName);

				if($parameter->enum !== null){
					$type = CommandParameter::ARG_FLAG_ENUM | CommandParameter::ARG_FLAG_VALID | ($this->enumMap[$parameter->enum->enumName] ?? -1);
				}elseif($parameter->postfix !== null){
					$key = array_search($parameter->postfix, $this->postfixes, true);
					if($key === false){
						throw new \InvalidStateException("Postfix '$parameter->postfix' not in postfixes array");
					}
					$type = $parameter->getParamType() << 24 | $key;
				}else{
					$type = $parameter->getParamType();
				}

				$this->putLInt($type);
				$this->putBool($parameter->isOptional);
			}
		}
	}

	protected function encodePayload(){
		$enumValuesMap = [];
		$postfixesMap = [];
		$enumMap = [];
		foreach($this->commandData as $commandData){
			if($commandData->aliases !== null){
				$enumMap[$commandData->aliases->enumName] = $commandData->aliases;

				foreach($commandData->aliases->enumValues as $str){
					$enumValuesMap[$str] = true;
				}
			}

			foreach($commandData->overloads as $overload){
				/**
				 * @var CommandParameter[] $overload
				 * @var CommandParameter $parameter
				 */
				foreach($overload as $parameter){
					if($parameter->enum !== null){
						$enumMap[$parameter->enum->enumName] = $parameter->enum;
						foreach($parameter->enum->enumValues as $str){
							$enumValuesMap[$str] = true;
						}
					}

					if($parameter->postfix !== null){
						$postfixesMap[$parameter->postfix] = true;
					}
				}
			}
		}

		$this->enumValues = array_map('strval', array_keys($enumValuesMap)); //stupid PHP key casting D:
		$this->putUnsignedVarInt($this->enumValuesCount = count($this->enumValues));
		foreach($this->enumValues as $enumValue){
			$this->putString($enumValue);
		}

		$this->postfixes = array_map('strval', array_keys($postfixesMap));
		$this->putUnsignedVarInt(count($this->postfixes));
		foreach($this->postfixes as $postfix){
			$this->putString($postfix);
		}

		$this->enums = array_values($enumMap);
		$this->enumMap = array_flip(array_keys($enumMap));
		$this->putUnsignedVarInt(count($this->enums));
		foreach($this->enums as $enum){
			$this->putEnum($enum);
		}

		$this->putUnsignedVarInt(count($this->commandData));
		foreach($this->commandData as $data){
			$this->putCommandData($data);
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAvailableCommands($this);
	}

}
