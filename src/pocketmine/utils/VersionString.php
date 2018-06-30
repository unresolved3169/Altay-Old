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

namespace pocketmine\utils;

/**
 * Manages Altay version strings, and compares them
 */
class VersionString{
	/** @var string */
	private $baseVersion;
	/** @var string */
	private $suffix;

	/** @var int */
	private $major;
	/** @var int */
	private $minor;
	/** @var int */
	private $patch;

	/** @var int */
	private $build;
	/** @var bool */
	private $development = false;

	/**
	 * @param string $baseVersion
	 * @param bool   $isDevBuild
	 * @param int    $buildNumber
	 */
	public function __construct(string $baseVersion, int $buildNumber = 0){
		$this->baseVersion = $baseVersion;
		$this->build = $buildNumber;

		preg_match('/([0-9]+)\.([0-9]+)\.([0-9]+)(?:-(.*))?$/', $this->baseVersion, $matches);
		if(count($matches) < 4){
			throw new \InvalidArgumentException("Invalid base version \"$baseVersion\", should contain at least 3 version digits");
		}

		$this->major = (int) $matches[1];
		$this->minor = (int) $matches[2];
		$this->patch = (int) $matches[3];
		$this->suffix = $matches[4] ?? "";
	}

	public function getNumber() : int{
		return (($this->major << 9) + ($this->minor << 5) + $this->patch);
	}

	public function getBaseVersion() : string{
		return $this->baseVersion;
	}

	public function getFullVersion() : string{
		return $this->baseVersion;
	}

	public function getMajor() : int{
		return $this->major;
	}

	public function getMinor() : int{
		return $this->minor;
	}

	public function getPatch() : int{
		return $this->patch;
	}

	public function getSuffix() : string{
		return $this->suffix;
	}

	public function getBuild() : int{
		return $this->build;
	}

	public function __toString() : string{
		return $this->getFullVersion();
	}

	/**
	 * @param VersionString $target
	 * @param bool          $diff
	 *
	 * @return int
	 */
	public function compare(VersionString $target, bool $diff = false) : int{
		$number = $this->getNumber();
		$tNumber = $target->getNumber();
		if($diff){
			return $tNumber - $number;
		}
		if($number > $tNumber){
			return -1; //Target is older
		}elseif($number < $tNumber){
			return 1; //Target is newer
		}elseif($target->getBuild() > $this->getBuild()){
			return 1;
		}elseif($target->getBuild() < $this->getBuild()){
			return -1;
		}else{
			return 0; //Same version
		}
	}
}