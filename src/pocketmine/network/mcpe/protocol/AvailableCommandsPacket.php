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

use pocketmine\command\Command;
use pocketmine\command\overload\CommandEnum;
use pocketmine\command\overload\CommandOverload;
use pocketmine\command\overload\CommandParameter;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\NetworkSession;

class AvailableCommandsPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;

	/** @var int */
	private $enumValuesCount = 0;

	/** @var Command[] */
	public $commands = [];

	protected function putEnum(CommandEnum $enum, NetworkBinaryStream $stream){
		$stream->putString($enum->enumName);

		$stream->putUnsignedVarInt(count($enum->enumValues));
		foreach($enum->enumValues as $value){
			$this->putEnumValueIndex($value, $stream);
		}
	}

	protected function putEnumValueIndex(int $index, NetworkBinaryStream $stream){
		if($this->enumValuesCount < 256){
			$stream->putByte($index);
		}elseif($this->enumValuesCount < 65536){
			$stream->putLShort($index);
		}else{
			$stream->putLInt($index);
		}
	}

	public function getPreparedCommand(){
		$extraDataStream = new NetworkBinaryStream;
		$commandStream = new NetworkBinaryStream;

		$enumValues = [];
		$enums = [];
		$postfixes = [];

		$this->enumValuesCount = 0;

		foreach($this->commands as $command){
			if($command->getName() == "help") continue; // temp fix for 1.2

			$commandStream->putString($command->getName());
			$commandStream->putString($command->getDescription());
			$commandStream->putByte($command->getFlags());
			$commandStream->putByte($command->getPermissionLevel());

			$enumIndex = -1;

			if(count($command->getAliases()) > 0){
				$aliases = $command->getAliases();
				$aliases[] = $command->getName();
				// recalculate enum indexs
				$aliasEVC = [];
				foreach($aliases as $alias){
					$enumValues[] = $alias;
					$aliasEVC[] = $this->enumValuesCount;
					$this->enumValuesCount++;
				}
				$enum = new CommandEnum($command->getName() . "CommandAliases", $aliasEVC);
				$enums[] = $enum;
				$enumIndex = count($enums) - 1;
			}

			$commandStream->putLInt($enumIndex);

			$overloads = $command->getOverloads();

			$commandStream->putUnsignedVarInt(count($overloads));
			/** @var CommandOverload $overload */
			foreach($overloads as $overload){
				$params = $overload->getParameters();
				$commandStream->putUnsignedVarInt(count($params));
				/** @var CommandParameter $param */
				foreach($params as $param){
					$commandStream->putString($param->paramName);

					$type = $param->paramType;
					if($param->flag == $param::ARG_FLAG_ENUM and $param->enum != null){
						$enum = $param->enum;
						$realValues = [];
						foreach ($enum->enumValues as $v) {
							$enumValues[] = $v;
							$realValues[] = $this->enumValuesCount;
							$this->enumValuesCount++;
						}
						$enums[] = new CommandEnum($enum->enumName, $realValues);
						$enumIndex = count($enums) - 1;
						$type = $param::ARG_FLAG_ENUM | $param::ARG_FLAG_VALID | $enumIndex;
					}elseif($param->flag == $param::ARG_FLAG_POSTFIX and strlen($param->postfix) > 0){
						$postfixes[] = $param->postfix;
						$type = $type << 24 | count($postfixes) - 1;
					}else{
						$type |= $param::ARG_FLAG_VALID;
					}

					$commandStream->putLInt($type);
					$commandStream->putBool($param->isOptional);
				}
			}
		}

		$extraDataStream->putUnsignedVarInt($this->enumValuesCount);
		foreach($enumValues as $v){
			$extraDataStream->putString($v);
		}

		$extraDataStream->putUnsignedVarInt(count($postfixes));
		foreach($postfixes as $postfix){
			$extraDataStream->putString($postfix);
		}

		$extraDataStream->putUnsignedVarInt(count($enums));
		foreach($enums as $enum){
			$this->putEnum($enum, $extraDataStream);
		}

		$extraDataStream->putUnsignedVarInt(count($this->commands));
		$extraDataStream->put($commandStream->buffer);

		return $extraDataStream->buffer;
	}

	protected function encodePayload(){
		$this->put($this->getPreparedCommand());
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleAvailableCommands($this);
	}

}
