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

namespace pocketmine\command\overload;

class CommandParameter{

	public const ARG_FLAG_VALID   = 0x100000;
	public const ARG_FLAG_ENUM    = 0x200000;
	public const ARG_FLAG_POSTFIX = 0x1000000;

	public const ARG_TYPE_INT             = 0x01;
	public const ARG_TYPE_FLOAT           = 0x02;
	public const ARG_TYPE_VALUE           = 0x03;
	public const ARG_TYPE_WILDCARD_INT    = 0x04;
	public const ARG_TYPE_TARGET          = 0x05;
	public const ARG_TYPE_WILDCARD_TARGET = 0x06;
	public const ARG_TYPE_STRING          = 0x0f;
	public const ARG_TYPE_POSITION        = 0x10;
	public const ARG_TYPE_MESSAGE         = 0x13;
	public const ARG_TYPE_RAWTEXT         = 0x15;
	public const ARG_TYPE_JSON            = 0x18;
	public const ARG_TYPE_COMMAND         = 0x1f;

	/** @var string */
	public $paramName;
	/** @var int */
	public $paramType;
	/** @var bool */
	public $isOptional;
	/** @var int */
	public $flag;
	/** @var CommandEnum|null */
	public $enum;
	/** @var string|null */
	public $postfix;

	/**
	 * CommandParameter constructor.
	 * @param string             $paramName
	 * @param int                $paramType
	 * @param bool               $optional
	 * @param CommandEnum|string $extraData for CommandEnum and Postfixes
	 */
	public function __construct(string $paramName, int $paramType, bool $optional = true, $extraData = null){
		if($extraData === null){
			$flag = self::ARG_FLAG_VALID;
		}elseif($extraData instanceof CommandEnum){
			$flag = self::ARG_FLAG_ENUM;
			$this->enum = $extraData;
		}elseif(is_string($extraData)){
			$flag = self::ARG_FLAG_POSTFIX;
			$this->postfix =  $extraData;
		}else{
			throw new \InvalidArgumentException("Wrong extraData for $paramName param");
		}

		$this->paramName = $paramName;
		$this->paramType = $paramType;
		$this->isOptional = $optional;
		$this->flag = $flag;
	}

	public function setName(string $paramName) : CommandParameter{
		$this->paramName = $paramName;

		return $this;
	}

	public function setType(int $paramType) : CommandParameter{
		$this->paramType = $paramType;

		return $this;
	}

	/**
	 * @param int $flag
	 * @return CommandParameter
	 */
	public function setFlag(int $flag) : CommandParameter{
		$this->flag = $flag;

		return $this;
	}

	public function setEnum(?CommandEnum $enum) : CommandParameter{
		$this->enum = $enum;

		return $this;
	}

	public function setOptional(bool $isOptional): CommandParameter{
		$this->isOptional = $isOptional;

		return $this;
	}

	public static function convertString(string $string){
		switch($string){
			case "string":
				return self::ARG_TYPE_STRING;
			case "int":
				return self::ARG_TYPE_INT;
			case "float":
				return self::ARG_TYPE_FLOAT;
			case "value":
				return self::ARG_TYPE_VALUE;
			case "target":
			case "player":
				return self::ARG_TYPE_TARGET;
			case "position":
				return self::ARG_TYPE_POSITION;
			case "message":
				return self::ARG_TYPE_MESSAGE;
			case "json":
				return self::ARG_TYPE_JSON;
			case "text":
				return self::ARG_TYPE_RAWTEXT;
			case "command":
				return self::ARG_TYPE_COMMAND;
			// flags
			case "enum":
				return self::ARG_FLAG_ENUM;
			case "valid":
				return self::ARG_FLAG_VALID;
			case "postfix":
				return self::ARG_FLAG_POSTFIX;

			default:
				return 0;
		}
	}
}