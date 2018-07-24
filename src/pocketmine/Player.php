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

namespace pocketmine;

use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\entity\utils\Bossbar;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerEditBookEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleGlideEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerToggleSwimmingEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\event\player\PlayerInteractEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\form\Form;
use pocketmine\form\ServerSettingsForm;
use pocketmine\inventory\ContainerInventory;
use pocketmine\inventory\CraftingGrid;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\Inventory;
use pocketmine\item\Consumable;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\item\WritableBook;
use pocketmine\item\WrittenBook;
use pocketmine\item\Item;
use pocketmine\lang\TextContainer;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\MoveEntityAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\VerifyLoginTask;
use pocketmine\network\NetworkInterface;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\plugin\Plugin;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\tile\ItemFrame;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
use pocketmine\network\mcpe\protocol\types\CommandOriginData;

/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, ChunkLoader, IPlayer{

    public const OS_ANDROID = 1;
    public const OS_IOS = 2;
    public const OS_MAC = 3;
    public const OS_FIREOS = 4;
    public const OS_GEARVR = 5;
    public const OS_HOLOLENS = 6;
    public const OS_WINDOWS = 7;
    public const OS_WIN32 = 8;
    public const OS_DEDICATED = 9;
    public const OS_ORBIS = 10;
    public const OS_NX = 11;

    public const SURVIVAL = 0;
    public const CREATIVE = 1;
    public const ADVENTURE = 2;
    public const SPECTATOR = 3;
    public const VIEW = Player::SPECTATOR;

    /**
     * Checks a supplied username and checks it is valid.
     * @param string $name
     *
     * @return bool
     */
    public static function isValidUserName(?string $name) : bool{
        if($name === null){
            return false;
        }

        $lname = strtolower($name);
        $len = strlen($name);
        return $lname !== "rcon" and $lname !== "console" and $len >= 1 and $len <= 16 and preg_match("/[^A-Za-z0-9_ ]/", $name) === 0;
    }

    /** @var NetworkSession */
    protected $networkSession;

    /** @var string */
    protected $ip;
    /** @var int */
    protected $port;

    /**
     * @var int
     * Last measurement of player's latency in milliseconds.
     */
    protected $lastPingMeasure = 1;

    /** @var float */
    public $creationTime = 0;

    /** @var bool */
    public $loggedIn = false;

    /** @var bool */
    public $spawned = false;

    /** @var string */
    protected $username = "";
    /** @var string */
    protected $iusername = "";
    /** @var string */
    protected $displayName = "";
    /** @var int */
    protected $randomClientId;
    /** @var string */
    protected $xuid = "";

    /** @var string */
    protected $deviceModel;
    /** @var int */
    protected $deviceOS;

    protected $windowCnt = 2;
    /** @var int[] */
    protected $windows = [];
    /** @var Inventory[] */
    protected $windowIndex = [];
    /** @var bool[] */
    protected $permanentWindows = [];
    /** @var PlayerCursorInventory */
    protected $cursorInventory;
    /** @var PlayerOffHandInventory */
    protected $offHandInventory;
    /** @var CraftingGrid */
    protected $craftingGrid = null;
    /** @var CraftingTransaction|null */
    protected $craftingTransaction = null;

    /** @var int */
    protected $messageCounter = 2;
    /** @var bool */
    protected $removeFormat = true;

    /** @var bool[] name of achievement => bool */
    protected $achievements = [];
    /** @var bool */
    protected $playedBefore;
    /** @var int */
    protected $gamemode;

    /** @var int */
    private $loaderId = 0;
    /** @var bool[] chunkHash => bool (true = sent, false = needs sending) */
    public $usedChunks = [];
    /** @var bool[] chunkHash => dummy */
    protected $loadQueue = [];
    /** @var int */
    protected $nextChunkOrderRun = 5;

    /** @var int */
    protected $viewDistance = -1;
    /** @var int */
    protected $spawnThreshold;
    /** @var int */
    protected $chunkLoadCount = 0;
    /** @var int */
    protected $chunksPerTick;

    /** @var bool[] map: raw UUID (string) => bool */
    protected $hiddenPlayers = [];

    /** @var Vector3|null */
    protected $newPosition;
    /** @var Vector3|null */
    public $speed = null;
    /** @var bool */
    protected $isTeleporting = false;
    /** @var int */
    protected $inAirTicks = 0;
    /** @var int */
    protected $startAirTicks = 5;
    /** @var float */
    protected $stepHeight = 0.6;
    /** @var bool */
    protected $allowMovementCheats = false;

    /** @var Vector3|null */
    protected $sleeping = null;
    /** @var Position|null */
    private $spawnPosition = null;

    //TODO: Abilities
    /** @var bool */
    protected $autoJump = true;
    /** @var bool */
    protected $allowFlight = false;
    /** @var bool */
    protected $flying = false;

    /** @var PermissibleBase */
    private $perm = null;

    /** @var int|null */
    protected $lineHeight = null;
    /** @var string */
    protected $locale = "en_US";

    /** @var int */
    protected $startAction = -1;
    /** @var int[] ID => ticks map */
    protected $usedItemsCooldown = [];

    /** @var int */
    protected $formIdCounter = 0;
    /** @var Form[] */
    protected $formQueue = [];
    /** @var ServerSettingsForm */
    protected $serverSettingsForm = null;

    /** @var int */
    private $bossbarIdCounter = 0;
    /** @var Bossbar[] */
    private $bossbars = [];

    /** @var int */
    protected $commandPermission = AdventureSettingsPacket::PERMISSION_NORMAL;

    /** @var bool */
    protected $keepExperience = false;

    /**
     * @return TranslationContainer|string
     */
    public function getLeaveMessage(){
        if($this->spawned){
            return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
                $this->getDisplayName()
            ]);
        }

        return "";
    }

    /**
     * This might disappear in the future. Please use getUniqueId() instead.
     * @deprecated
     *
     * @return int
     */
    public function getClientId(){
        return $this->randomClientId;
    }

    public function isBanned() : bool{
        return $this->server->getNameBans()->isBanned($this->iusername);
    }

    public function setBanned(bool $value){
        if($value){
            $this->server->getNameBans()->addBan($this->getName(), null, null, null);
            $this->kick("You have been banned");
        }else{
            $this->server->getNameBans()->remove($this->getName());
        }
    }

    public function isWhitelisted() : bool{
        return $this->server->isWhitelisted($this->iusername);
    }

    public function setWhitelisted(bool $value){
        if($value){
            $this->server->addWhitelist($this->iusername);
        }else{
            $this->server->removeWhitelist($this->iusername);
        }
    }

    public function isAuthenticated() : bool{
        return $this->xuid !== "";
    }

  public function canBePushed() : bool{
   return true;
  }

	/**
	 * If the player is logged into Xbox Live, returns their Xbox user ID (XUID) as a string. Returns an empty string if
	 * the player is not logged into Xbox Live.
	 *
	 * @return string
	 */
	public function getXuid() : string{
		return $this->xuid;
	}

    /**
     * Returns the player's UUID. This should be preferred over their Xbox user ID (XUID) because UUID is a standard
     * format which will never change, and all players will have one regardless of whether they are logged into Xbox
     * Live.
     *
     * The UUID is comprised of:
     * - when logged into XBL: a hash of their XUID (and as such will not change for the lifetime of the XBL account)
     * - when NOT logged into XBL: a hash of their name + clientID + secret device ID.
     *
     * WARNING: UUIDs of players **not logged into Xbox Live** CAN BE FAKED and SHOULD NOT be trusted!
     *
     * (In the olden days this method used to return a fake UUID computed by the server, which was used by plugins such
     * as SimpleAuth for authentication. This is NOT SAFE anymore as this UUID is now what was given by the client, NOT
     * a server-computed UUID.)
     *
     * @return UUID|null
     */
    public function getUniqueId() : ?UUID{
        return parent::getUniqueId();
    }

    public function getPlayer(){
        return $this;
    }

    public function getFirstPlayed(){
        return $this->namedtag instanceof CompoundTag ? $this->namedtag->getLong("firstPlayed", 0, true) : null;
    }

    public function getLastPlayed(){
        return $this->namedtag instanceof CompoundTag ? $this->namedtag->getLong("lastPlayed", 0, true) : null;
    }

    public function hasPlayedBefore() : bool{
        return $this->playedBefore;
    }

    public function setAllowFlight(bool $value){
        $this->allowFlight = $value;
        $this->sendSettings();
    }

    public function getAllowFlight() : bool{
        return $this->allowFlight;
    }

    public function setFlying(bool $value){
        $this->flying = $value;
        $this->sendSettings();
    }

    public function isFlying() : bool{
        return $this->flying;
    }

    public function setAutoJump(bool $value){
        $this->autoJump = $value;
        $this->sendSettings();
    }

    public function hasAutoJump() : bool{
        return $this->autoJump;
    }

    public function allowMovementCheats() : bool{
        return $this->allowMovementCheats;
    }

    public function setAllowMovementCheats(bool $value = true){
        $this->allowMovementCheats = $value;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player) : void{
        if($this->spawned and $player->spawned and $this->isAlive() and $player->isAlive() and $player->getLevel() === $this->level and $player->canSee($this) and !$this->isSpectator()){
            parent::spawnTo($player);
        }
    }

    /**
     * @return Server
     */
    public function getServer(){
        return $this->server;
    }

    /**
     * @return bool
     */
    public function getRemoveFormat() : bool{
        return $this->removeFormat;
    }

    /**
     * @param bool $remove
     */
    public function setRemoveFormat(bool $remove = true){
        $this->removeFormat = $remove;
    }

    public function getScreenLineHeight() : int{
        return $this->lineHeight ?? 7;
    }

    public function setScreenLineHeight(int $height = null){
        if($height !== null and $height < 1){
            throw new \InvalidArgumentException("Line height must be at least 1");
        }
        $this->lineHeight = $height;
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    public function canSee(Player $player) : bool{
        return !isset($this->hiddenPlayers[$player->getRawUniqueId()]);
    }

    /**
     * @param Player $player
     */
    public function hidePlayer(Player $player){
        if($player === $this){
            return;
        }
        $this->hiddenPlayers[$player->getRawUniqueId()] = true;
        $player->despawnFrom($this);
    }

    /**
     * @param Player $player
     */
    public function showPlayer(Player $player){
        if($player === $this){
            return;
        }
        unset($this->hiddenPlayers[$player->getRawUniqueId()]);
        if($player->isOnline()){
            $player->spawnTo($this);
        }
    }

    public function canCollideWith(Entity $entity) : bool{
        return false;
    }

    public function canBeCollidedWith() : bool{
        return !$this->isSpectator() and parent::canBeCollidedWith();
    }

    public function resetFallDistance() : void{
        parent::resetFallDistance();
        if($this->inAirTicks !== 0){
            $this->startAirTicks = 5;
        }
        $this->inAirTicks = 0;
    }

    public function getViewDistance() : int{
        return $this->viewDistance;
    }

    public function setViewDistance(int $distance){
        $this->viewDistance = $this->server->getAllowedViewDistance($distance);

        $this->spawnThreshold = (int) (min($this->viewDistance, $this->server->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

        $this->nextChunkOrderRun = 0;

        $pk = new ChunkRadiusUpdatedPacket();
        $pk->radius = $this->viewDistance;
        $this->dataPacket($pk);

        $this->server->getLogger()->debug("Setting view distance for " . $this->getName() . " to " . $this->viewDistance . " (requested " . $distance . ")");
    }

    /**
     * @return bool
     */
    public function isOnline() : bool{
        return $this->isConnected() and $this->loggedIn;
    }

    /**
     * @return bool
     */
    public function isOp() : bool{
        return $this->server->isOp($this->getName());
    }

    /**
     * @param bool $value
     */
    public function setOp(bool $value){
        if($value === $this->isOp()){
            return;
        }

        if($value){
            $this->server->addOp($this->getName());
        }else{
            $this->server->removeOp($this->getName());
        }

        $this->sendSettings();
    }

    /**
     * @param permission\Permission|string $name
     *
     * @return bool
     */
    public function isPermissionSet($name) : bool{
        return $this->perm->isPermissionSet($name);
    }

    /**
     * @param permission\Permission|string $name
     *
     * @return bool
     *
     * @throws \InvalidStateException if the player is closed
     */
    public function hasPermission($name) : bool{
        if($this->closed){
            throw new \InvalidStateException("Trying to get permissions of closed player");
        }
        return $this->perm->hasPermission($name);
    }

    /**
     * @param Plugin $plugin
     * @param string $name
     * @param bool $value
     *
     * @return PermissionAttachment
     */
    public function addAttachment(Plugin $plugin, string $name = null, bool $value = null) : PermissionAttachment{
        return $this->perm->addAttachment($plugin, $name, $value);
    }

    /**
     * @param PermissionAttachment $attachment
     */
    public function removeAttachment(PermissionAttachment $attachment){
        $this->perm->removeAttachment($attachment);
    }

    public function recalculatePermissions(){
        $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
        $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

        if($this->perm === null){
            return;
        }

        $this->perm->recalculatePermissions();

        if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
        }
        if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
        }

        if($this->spawned){
            $this->sendCommandData();
        }
    }

    /**
     * @return PermissionAttachmentInfo[]
     */
    public function getEffectivePermissions() : array{
        return $this->perm->getEffectivePermissions();
    }

    public function sendCommandData(){
        $pk = new AvailableCommandsPacket();
        foreach($this->server->getCommandMap()->getCommands() as $command){
            if(!$command->testPermissionSilent($this) or isset($pk->commandData[$command->getName()])){
                continue;
            }

            $data = $command->getData();
            if($data->aliases !== null){
                //work around a client bug which makes the original name not show when aliases are used
                $data->aliases->enumValues[] = $data->commandName;
            }

            $pk->commandData[$data->commandName] = $data;
        }

        $this->dataPacket($pk);
    }

    /**
     * @param NetworkInterface $interface
     * @param string          $ip
     * @param int             $port
     */
    public function __construct(NetworkInterface $interface, string $ip, int $port){
        $this->perm = new PermissibleBase($this);
        $this->server = Server::getInstance();
        $this->ip = $ip;
        $this->port = $port;
        $this->loaderId = Level::generateChunkLoaderId($this);
        $this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
        $this->spawnThreshold = (int) (($this->server->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);

        $this->creationTime = microtime(true);

        $this->allowMovementCheats = (bool) $this->server->getProperty("player.anti-cheat.allow-movement-cheats", false);

        $this->networkSession = new NetworkSession($this->server, $this, $interface, $ip, $port);
    }

    /**
     * @return bool
     */
    public function isConnected() : bool{
        return $this->networkSession !== null;
    }

    /**
     * @return NetworkSession
     */
    public function getNetworkSession() : NetworkSession{
        return $this->networkSession;
    }

    /**
     * Gets the username
     * @return string
     */
    public function getName() : string{
        return $this->username;
    }

    /**
     * @return string
     */
    public function getLowerCaseName() : string{
        return $this->iusername;
    }

    /**
     * Gets the "friendly" name to display of this player to use in the chat.
     *
     * @return string
     */
    public function getDisplayName() : string{
        return $this->displayName;
    }

    /**
     * @param string $name
     */
    public function setDisplayName(string $name){
        $this->displayName = $name;
        if($this->spawned){
            $this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->getSkin(), $this->getXuid());
        }
    }

    /**
     * Returns the player's locale, e.g. en_US.
     * @return string
     */
    public function getLocale() : string{
        return $this->locale;
    }

    /**
     * Called when a player changes their skin.
     * Plugin developers should not use this, use setSkin() and sendSkin() instead.
     *
     * @param Skin   $skin
     * @param string $newSkinName
     * @param string $oldSkinName
     *
     * @return bool
     */
    public function changeSkin(Skin $skin, string $newSkinName, string $oldSkinName) : bool{
        if(!$skin->isValid()){
            return false;
        }

        $ev = new PlayerChangeSkinEvent($this, $this->getSkin(), $skin);
        $this->server->getPluginManager()->callEvent($ev);

        if($ev->isCancelled()){
            $this->sendSkin([$this]);
            return true;
        }

        $this->setSkin($ev->getNewSkin());
        $this->sendSkin($this->server->getOnlinePlayers());
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * If null is given, will additionally send the skin to the player itself as well as its viewers.
     */
    public function sendSkin(?array $targets = null) : void{
        parent::sendSkin($targets ?? $this->server->getOnlinePlayers());
    }

    /**
     * Gets the player IP address
     *
     * @return string
     */
    public function getAddress() : string{
        return $this->networkSession->getIp();
    }

    /**
     * @return int
     */
    public function getPort() : int{
        return $this->networkSession->getPort();
    }

    /**
     * Returns the last measured latency for this player, in milliseconds. This is measured automatically and reported
     * back by the network interface.
     *
     * @return int
     */
    public function getPing() : int{
        return $this->lastPingMeasure;
    }

    /**
     * Updates the player's last ping measurement.
     *
     * @internal Plugins should not use this method.
     *
     * @param int $pingMS
     */
    public function updatePing(int $pingMS){
        $this->lastPingMeasure = $pingMS;
    }

    /**
     * @return Position
     */
    public function getNextPosition() : Position{
        return $this->newPosition !== null ? Position::fromObject($this->newPosition, $this->level) : $this->getPosition();
    }

    public function getInAirTicks() : int{
        return $this->inAirTicks;
    }

    /**
     * Returns whether the player is currently using an item (right-click and hold).
     * @return bool
     */
    public function isUsingItem() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_ACTION) and $this->startAction > -1;
    }

    public function setUsingItem(bool $value){
        $this->startAction = $value ? $this->server->getTick() : -1;
        $this->setGenericFlag(self::DATA_FLAG_ACTION, $value);
    }

    /**
     * Returns how long the player has been using their currently-held item for. Used for determining arrow shoot force
     * for bows.
     *
     * @return int
     */
    public function getItemUseDuration() : int{
        return $this->startAction === -1 ? -1 : ($this->server->getTick() - $this->startAction);
    }

    /**
     * Returns whether the player has a cooldown period left before it can use the given item again.
     *
     * @param Item $item
     *
     * @return bool
     */
    public function hasItemCooldown(Item $item) : bool{
        $this->checkItemCooldowns();
        return isset($this->usedItemsCooldown[$item->getId()]);
    }

    /**
     * Resets the player's cooldown time for the given item back to the maximum.
     *
     * @param Item $item
     */
    public function resetItemCooldown(Item $item) : void{
        $ticks = $item->getCooldownTicks();
        if($ticks > 0){
            $this->usedItemsCooldown[$item->getId()] = $this->server->getTick() + $ticks;
        }
    }

    protected function checkItemCooldowns() : void{
        $serverTick = $this->server->getTick();
        foreach($this->usedItemsCooldown as $itemId => $cooldownUntil){
            if($cooldownUntil <= $serverTick){
                unset($this->usedItemsCooldown[$itemId]);
            }
        }
    }

    public function getCommandPermission() : int{
        return $this->commandPermission;
    }

    public function setCommandPermission(int $commandPermission) : void{
        $this->commandPermission = $commandPermission;
    }

    public function changeDimension(int $dimension, Vector3 $position = null, bool $respawn = false){
        $pk = new ChangeDimensionPacket();
        $pk->dimension = $dimension;
        $pk->position = $position ?? $this;
        $pk->respawn = $respawn;
        $this->dataPacket($pk);
    }

    protected function switchLevel(Level $targetLevel) : bool{
        $oldLevel = $this->level;
        if(parent::switchLevel($targetLevel)){
            if($oldLevel !== null){
                foreach($this->usedChunks as $index => $d){
                    Level::getXZ($index, $X, $Z);
                    $this->unloadChunk($X, $Z, $oldLevel);
                }
            }

            $this->usedChunks = [];
            $this->loadQueue = [];
            $this->level->sendTime($this);
            $this->level->sendDifficulty($this);

            if($oldLevel->getDimension() !== $targetLevel->getDimension()){
                $this->changeDimension($targetLevel->getDimension(), $this, !$this->isAlive());
            }

            return true;
        }

        return false;
    }

    protected function unloadChunk(int $x, int $z, Level $level = null){
        $level = $level ?? $this->level;
        $index = Level::chunkHash($x, $z);
        if(isset($this->usedChunks[$index])){
            foreach($level->getChunkEntities($x, $z) as $entity){
                if($entity !== $this){
                    $entity->despawnFrom($this);
                }
            }

            unset($this->usedChunks[$index]);
        }
        $level->unregisterChunkLoader($this, $x, $z);
        unset($this->loadQueue[$index]);
    }

    public function sendChunk(int $x, int $z, string $payload){
        if(!$this->isConnected()){
            return;
        }

        $this->usedChunks[Level::chunkHash($x, $z)] = true;
        $this->chunkLoadCount++;

        $this->networkSession->getInterface()->putPacket($this, $payload);

        if($this->spawned){
            foreach($this->level->getChunkEntities($x, $z) as $entity){
                if($entity !== $this and !$entity->isClosed() and $entity->isAlive()){
                    $entity->spawnTo($this);
                }
            }
        }

        if($this->chunkLoadCount >= $this->spawnThreshold and !$this->spawned){
            $this->doFirstSpawn();
        }
    }

    protected function sendNextChunk(){
        if(!$this->isConnected()){
            return;
        }

        Timings::$playerChunkSendTimer->startTiming();

        $count = 0;
        foreach($this->loadQueue as $index => $distance){
            if($count >= $this->chunksPerTick){
                break;
            }

            $X = null;
            $Z = null;
            Level::getXZ($index, $X, $Z);
            assert(is_int($X) and is_int($Z));

            ++$count;

            $this->usedChunks[$index] = false;
            $this->level->registerChunkLoader($this, $X, $Z, false);

            if(!$this->level->populateChunk($X, $Z)){
                continue;
            }

            unset($this->loadQueue[$index]);
            $this->level->requestChunk($X, $Z, $this);
        }

        Timings::$playerChunkSendTimer->stopTiming();
    }

    protected function doFirstSpawn(){
        $this->spawned = true;

        $this->networkSession->onSpawn();

        if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
        }
        if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerJoinEvent($this,
            new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.joined", [
                $this->getDisplayName()
            ])
        ));
        if(strlen(trim((string) $ev->getJoinMessage())) > 0){
            $this->server->broadcastMessage($ev->getJoinMessage());
        }

        $this->noDamageTicks = 60;

        foreach($this->usedChunks as $index => $c){
            Level::getXZ($index, $chunkX, $chunkZ);
            foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
                if($entity !== $this and !$entity->isClosed() and $entity->isAlive() and !$entity->isFlaggedForDespawn()){
                    $entity->spawnTo($this);
                }
            }
        }

        $this->spawnToAll();

        if($this->getHealth() <= 0){
            $this->respawn();
        }
    }

    protected function orderChunks() : void{
        if(!$this->isConnected() or $this->viewDistance === -1){
            return;
        }

        Timings::$playerChunkOrderTimer->startTiming();

        $this->nextChunkOrderRun = 200;

        $radius = $this->server->getAllowedViewDistance($this->viewDistance);
        $radiusSquared = $radius ** 2;

        $newOrder = [];
        $unloadChunks = $this->usedChunks;

        $centerX = $this->getFloorX() >> 4;
        $centerZ = $this->getFloorZ() >> 4;

        for($x = 0; $x < $radius; ++$x){
            for($z = 0; $z <= $x; ++$z){
                if(($x ** 2 + $z ** 2) > $radiusSquared){
                    break; //skip to next band
                }

                //If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

                /* Top right quadrant */
                if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ + $z)]) or $this->usedChunks[$index] === false){
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);

                /* Top left quadrant */
                if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ + $z)]) or $this->usedChunks[$index] === false){
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);

                /* Bottom right quadrant */
                if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false){
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);


                /* Bottom left quadrant */
                if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false){
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);

                if($x !== $z){
                    /* Top right quadrant mirror */
                    if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ + $x)]) or $this->usedChunks[$index] === false){
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);

                    /* Top left quadrant mirror */
                    if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ + $x)]) or $this->usedChunks[$index] === false){
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);

                    /* Bottom right quadrant mirror */
                    if(!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false){
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);

                    /* Bottom left quadrant mirror */
                    if(!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false){
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);
                }
            }
        }

        foreach($unloadChunks as $index => $bool){
            Level::getXZ($index, $X, $Z);
            $this->unloadChunk($X, $Z);
        }

        $this->loadQueue = $newOrder;

        Timings::$playerChunkOrderTimer->stopTiming();
    }

    public function doChunkRequests(){
        if(!$this->isOnline()){
            return;
        }

        if($this->nextChunkOrderRun-- <= 0){
            $this->orderChunks();
        }

        if(count($this->loadQueue) > 0){
            $this->sendNextChunk();
        }
    }

    /**
     * @return Position
     */
    public function getSpawn(){
        if($this->hasValidSpawnPosition()){
            return $this->spawnPosition;
        }else{
            $level = $this->server->getDefaultLevel();

            return $level->getSafeSpawn();
        }
    }

    /**
     * @return bool
     */
    public function hasValidSpawnPosition() : bool{
        return $this->spawnPosition != null and $this->spawnPosition->isValid();
    }

    /**
     * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a
     * Position object
     *
     * @param Vector3|Position $pos
     */
    public function setSpawn(Vector3 $pos){
        if($pos instanceof Position){
            $this->spawnPosition = $pos->asPosition();
        }else{
            $this->spawnPosition = Position::fromObject($pos, $this->level);
        }

        $pk = new SetSpawnPositionPacket();
        $pk->x = $this->spawnPosition->getFloorX();
        $pk->y = $this->spawnPosition->getFloorY();
        $pk->z = $this->spawnPosition->getFloorZ();
        $pk->spawnType = SetSpawnPositionPacket::TYPE_PLAYER_SPAWN;
        $pk->spawnForced = false;
        $this->dataPacket($pk);
    }

    /**
     * @return bool
     */
    public function isSleeping() : bool{
        return $this->sleeping !== null;
    }

    /**
     * @param Vector3 $pos
     *
     * @return bool
     */
    public function sleepOn(Vector3 $pos) : bool{
        if(!$this->isOnline()){
            return false;
        }

        $pos = $pos->floor();
        $b = $this->level->getBlock($pos);

        $this->server->getPluginManager()->callEvent($ev = new PlayerBedEnterEvent($this, $b));
        if($ev->isCancelled()){
            return false;
        }

        if($b instanceof Bed){
            $b->setOccupied();
        }

        $this->sleeping = clone $pos;

        $this->propertyManager->setBlockPos(self::DATA_PLAYER_BED_POSITION, $pos);
        $this->setPlayerFlag(self::DATA_PLAYER_FLAG_SLEEP, true);

        $this->setSpawn($pos);

        $this->level->setSleepTicks(60);

        return true;
    }

    public function stopSleep(){
        if($this->sleeping instanceof Vector3){
            $b = $this->level->getBlock($this->sleeping);
            if($b instanceof Bed){
                $b->setOccupied(false);
            }
            $this->server->getPluginManager()->callEvent($ev = new PlayerBedLeaveEvent($this, $b));

            $this->sleeping = null;
            $this->propertyManager->setBlockPos(self::DATA_PLAYER_BED_POSITION, null);
            $this->setPlayerFlag(self::DATA_PLAYER_FLAG_SLEEP, false);

            $this->level->setSleepTicks(0);

            $pk = new AnimatePacket();
            $pk->entityRuntimeId = $this->id;
            $pk->action = AnimatePacket::ACTION_STOP_SLEEP;
            $this->dataPacket($pk);
        }
    }

    /**
     * @param string $achievementId
     *
     * @return bool
     */
    public function hasAchievement(string $achievementId) : bool{
        if(!isset(Achievement::$list[$achievementId])){
            return false;
        }

        return $this->achievements[$achievementId] ?? false;
    }

    /**
     * @param string $achievementId
     *
     * @return bool
     */
    public function awardAchievement(string $achievementId) : bool{
        if(isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)){
            foreach(Achievement::$list[$achievementId]["requires"] as $requirementId){
                if(!$this->hasAchievement($requirementId)){
                    return false;
                }
            }
            $this->server->getPluginManager()->callEvent($ev = new PlayerAchievementAwardedEvent($this, $achievementId));
            if(!$ev->isCancelled()){
                $this->achievements[$achievementId] = true;
                Achievement::broadcast($this, $achievementId);

                return true;
            }else{
                return false;
            }
        }

        return false;
    }

    /**
     * @param string $achievementId
     */
    public function removeAchievement(string $achievementId){
        if($this->hasAchievement($achievementId)){
            $this->achievements[$achievementId] = false;
        }
    }

    /**
     * @return int
     */
    public function getGamemode() : int{
        return $this->gamemode;
    }

    /**
     * @internal
     *
     * Returns a client-friendly gamemode of the specified real gamemode
     * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
     *
     * TODO: remove this when Spectator Mode gets added properly to MCPE
     *
     * @param int $gamemode
     * @return int
     */
    public static function getClientFriendlyGamemode(int $gamemode) : int{
        $gamemode &= 0x03;
        if($gamemode === Player::SPECTATOR){
            return Player::CREATIVE;
        }

        return $gamemode;
    }

    /**
     * Sets the gamemode, and if needed, kicks the Player.
     *
     * @param int  $gm
     * @param bool $client if the client made this change in their GUI
     *
     * @return bool
     */
    public function setGamemode(int $gm, bool $client = false) : bool{
        if($gm < 0 or $gm > 3 or $this->gamemode === $gm){
            return false;
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerGameModeChangeEvent($this, $gm));
        if($ev->isCancelled()){
            if($client){ //gamemode change by client in the GUI
                $this->sendGamemode();
            }
            return false;
        }

        $this->gamemode = $gm;

        $this->allowFlight = $this->isCreative();
        if($this->isSpectator()){
            $this->flying = true;
            $this->keepMovement = true;
            $this->despawnFromAll();
        }else{
            $this->keepMovement = $this->allowMovementCheats;
            if($this->isSurvival()){
                $this->flying = false;
            }
            $this->spawnToAll();
        }

        $this->resetFallDistance();

        $this->namedtag->setInt("playerGameType", $this->gamemode);
        if(!$client){ //Gamemode changed by server, do not send for client changes
            $this->sendGamemode();
        }else{
            Command::broadcastCommandMessage($this, new TranslationContainer("commands.gamemode.success.self", [Server::getGamemodeString($gm)]));
        }

        $this->sendSettings();
        $this->inventory->sendCreativeContents();

        return true;
    }

    /**
     * @internal
     * Sends the player's gamemode to the client.
     */
    public function sendGamemode(){
        $pk = new SetPlayerGameTypePacket();
        $pk->gamemode = Player::getClientFriendlyGamemode($this->gamemode);
        $this->dataPacket($pk);
    }

    /**
     * Sends all the option flags
     */
    public function sendSettings(){
        $pk = new AdventureSettingsPacket();

        $pk->setFlag(AdventureSettingsPacket::WORLD_IMMUTABLE, $this->isSpectator());
        $pk->setFlag(AdventureSettingsPacket::NO_PVP, $this->isSpectator());
        $pk->setFlag(AdventureSettingsPacket::AUTO_JUMP, $this->autoJump);
        $pk->setFlag(AdventureSettingsPacket::ALLOW_FLIGHT, $this->allowFlight);
        $pk->setFlag(AdventureSettingsPacket::NO_CLIP, $this->isSpectator());
        $pk->setFlag(AdventureSettingsPacket::FLYING, $this->flying);

        $pk->commandPermission = ($this->isOp() ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
        $this->commandPermission = $pk->commandPermission;
        $pk->playerPermission = ($this->isOp() ? PlayerPermissions::OPERATOR : PlayerPermissions::MEMBER);
        $pk->entityUniqueId = $this->getId();

        $this->dataPacket($pk);
    }

    /**
     * NOTE: Because Survival and Adventure Mode share some similar behaviour, this method will also return true if the player is
     * in Adventure Mode. Supply the $literal parameter as true to force a literal Survival Mode check.
     *
     * @param bool $literal whether a literal check should be performed
     *
     * @return bool
     */
    public function isSurvival(bool $literal = false) : bool{
        if($literal){
            return $this->gamemode === Player::SURVIVAL;
        }else{
            return ($this->gamemode & 0x01) === 0;
        }
    }

    /**
     * NOTE: Because Creative and Spectator Mode share some similar behaviour, this method will also return true if the player is
     * in Spectator Mode. Supply the $literal parameter as true to force a literal Creative Mode check.
     *
     * @param bool $literal whether a literal check should be performed
     *
     * @return bool
     */
    public function isCreative(bool $literal = false) : bool{
        if($literal){
            return $this->gamemode === Player::CREATIVE;
        }else{
            return ($this->gamemode & 0x01) === 1;
        }
    }

    /**
     * NOTE: Because Adventure and Spectator Mode share some similar behaviour, this method will also return true if the player is
     * in Spectator Mode. Supply the $literal parameter as true to force a literal Adventure Mode check.
     *
     * @param bool $literal whether a literal check should be performed
     *
     * @return bool
     */
    public function isAdventure(bool $literal = false) : bool{
        if($literal){
            return $this->gamemode === Player::ADVENTURE;
        }else{
            return ($this->gamemode & 0x02) > 0;
        }
    }

    /**
     * @return bool
     */
    public function isSpectator() : bool{
        return $this->gamemode === Player::SPECTATOR;
    }

    public function isFireProof() : bool{
        return $this->isCreative();
    }

    public function getDrops() : array{
        if(!$this->isCreative()){
            return parent::getDrops();
        }

        return [];
    }

    public function getXpDropAmount() : int{
        if(!$this->server->keepExperience && !$this->isCreative() and !$this->keepExperience){
            return parent::getXpDropAmount();
        }

        return 0;
    }

    protected function checkGroundState(float $movX, float $movY, float $movZ, float $dx, float $dy, float $dz) : void{
        $bb = clone $this->boundingBox;
        $bb->minY = $this->y - 0.2;
        $bb->maxY = $this->y + 0.2;

        $this->onGround = $this->isCollided = count($this->level->getCollisionBlocks($bb, true)) > 0;
    }

    public function canBeMovedByCurrents() : bool{
        return false; //currently has no server-side movement
    }

    protected function checkNearEntities(){
        foreach($this->level->getNearbyEntities($this->boundingBox->expandedCopy(1, 0.5, 1), $this) as $entity){
            $entity->scheduleUpdate();

            if(!$entity->isAlive() or $entity->isFlaggedForDespawn()){
                continue;
            }

            $entity->onCollideWithPlayer($this);
        }
    }

    protected function processMovement(int $tickDiff){
        if($this->newPosition === null or $this->isSleeping()){
            return;
        }

        assert($this->x !== null and $this->y !== null and $this->z !== null);
        assert($this->newPosition->x !== null and $this->newPosition->y !== null and $this->newPosition->z !== null);

        $newPos = $this->newPosition;
        $distanceSquared = $newPos->distanceSquared($this);

        $revert = false;

        $chunkX = $newPos->getFloorX() >> 4;
        $chunkZ = $newPos->getFloorZ() >> 4;

        if(!$this->level->isChunkLoaded($chunkX, $chunkZ) or !$this->level->isChunkGenerated($chunkX, $chunkZ)){
            $revert = true;
            $this->nextChunkOrderRun = 0;
        }

        if(!$revert and $distanceSquared != 0){
            $dx = $newPos->x - $this->x;
            $dy = $newPos->y - $this->y;
            $dz = $newPos->z - $this->z;

            $this->move($dx, $dy, $dz);

            $diff = $this->distanceSquared($newPos) / $tickDiff ** 2;

            if($this->isSurvival() and !$revert and $diff > 0.0625){
                $ev = new PlayerIllegalMoveEvent($this, $newPos, new Vector3($this->lastX, $this->lastY, $this->lastZ));
                $ev->setCancelled($this->allowMovementCheats);

                $this->server->getPluginManager()->callEvent($ev);

                if(!$ev->isCancelled()){
                    $revert = true;
                    $this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidMove", [$this->getName()]));
                    $this->server->getLogger()->debug("Old position: " . $this->asVector3() . ", new position: " . $this->newPosition);
                }
            }

            if($diff > 0 and !$revert){
                $this->setPosition($newPos);
            }
        }

        $from = new Location($this->lastX, $this->lastY, $this->lastZ, $this->lastYaw, $this->lastPitch, $this->level);
        $to = $this->getLocation();

        $delta = (($this->lastX - $to->x) ** 2) + (($this->lastY - $to->y) ** 2) + (($this->lastZ - $to->z) ** 2);
        $deltaAngle = abs($this->lastYaw - $to->yaw) + abs($this->lastPitch - $to->pitch);

        if(!$revert and ($delta > 0.0001 or $deltaAngle > 1.0)){
            $this->lastX = $to->x;
            $this->lastY = $to->y;
            $this->lastZ = $to->z;

            $this->lastYaw = $to->yaw;
            $this->lastPitch = $to->pitch;

            $ev = new PlayerMoveEvent($this, $from, $to);

            $this->server->getPluginManager()->callEvent($ev);

            if(!($revert = $ev->isCancelled())){ //Yes, this is intended
                if($to->distanceSquared($ev->getTo()) > 0.01){ //If plugins modify the destination
                    $this->teleport($ev->getTo());
                }else{
                    $this->broadcastMovement();

                    $distance = $from->distance($to);
                    //TODO: check swimming (adds 0.015 exhaustion in MCPE)
                    if($this->isSprinting()){
                        $this->exhaust(0.1 * $distance, PlayerExhaustEvent::CAUSE_SPRINTING);
                    }else{
                        $this->exhaust(0.01 * $distance, PlayerExhaustEvent::CAUSE_WALKING);
                    }
                }
            }

            $this->speed = $to->subtract($from)->divide($tickDiff);
        }elseif($distanceSquared == 0){
            $this->speed = new Vector3(0, 0, 0);
        }

        if($revert){

            $this->lastX = $from->x;
            $this->lastY = $from->y;
            $this->lastZ = $from->z;

            $this->lastYaw = $from->yaw;
            $this->lastPitch = $from->pitch;

            $this->setPosition($from);
            $this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
        }else{
            if($distanceSquared != 0 and $this->nextChunkOrderRun > 20){
                $this->nextChunkOrderRun = 20;
            }
        }

        $this->newPosition = null;
    }

    public function jump() : void{
        $this->server->getPluginManager()->callEvent(new PlayerJumpEvent($this));
        parent::jump();
    }

    public function setMotion(Vector3 $motion) : bool{
        if(parent::setMotion($motion)){
            $this->broadcastMotion();

            if($this->motion->y > 0){
                $this->startAirTicks = (-log($this->gravity / ($this->gravity + $this->drag * $this->motion->y)) / $this->drag) * 2 + 5;
            }

            return true;
        }
        return false;
    }

    protected function updateMovement(bool $teleport = false) : void{

    }

    protected function tryChangeMovement() : void{

    }

    public function sendAttributes(bool $sendAll = false){
        $entries = $sendAll ? $this->attributeMap->getAll() : $this->attributeMap->needSend();
        if(count($entries) > 0){
            $pk = new UpdateAttributesPacket();
            $pk->entityRuntimeId = $this->id;
            $pk->entries = $entries;
            $this->dataPacket($pk);
            foreach($entries as $entry){
                $entry->markSynchronized();
            }
        }
    }

    public function onUpdate(int $currentTick) : bool{
        if(!$this->loggedIn){
            return false;
        }

        $tickDiff = $currentTick - $this->lastUpdate;

        if($tickDiff <= 0){
            return true;
        }

        $this->messageCounter = 2;

        $this->lastUpdate = $currentTick;

        $this->sendAttributes();

        if(!$this->isAlive() and $this->spawned){
            $this->onDeathUpdate($tickDiff);
            return true;
        }

        $this->timings->startTiming();

		if($this->spawned){
			$this->processMovement($tickDiff);
			$this->resetMotion(); //TODO: HACK! (Fixes player knockback being messed up)

            Timings::$timerEntityBaseTick->startTiming();
            $this->entityBaseTick($tickDiff);
            Timings::$timerEntityBaseTick->stopTiming();

            if(!$this->isSpectator() and $this->isAlive()){
                Timings::$playerCheckNearEntitiesTimer->startTiming();
                $this->checkNearEntities();
                Timings::$playerCheckNearEntitiesTimer->stopTiming();

                if($this->speed !== null){
                    if($this->onGround){
                        if($this->inAirTicks !== 0){
                            $this->startAirTicks = 5;
                        }
                        $this->inAirTicks = 0;
                    }else{
                        if(!$this->isGliding() and !$this->allowFlight and $this->inAirTicks > 10 and !$this->isSleeping() and !$this->isImmobile()){
                            $expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - $this->startAirTicks));
                            $diff = ($this->speed->y - $expectedVelocity) ** 2;

                            if(!$this->hasEffect(Effect::JUMP) and !$this->hasEffect(Effect::LEVITATION) and $diff > 0.6 and $expectedVelocity < $this->speed->y and !$this->server->getAllowFlight() and !$this->allowMovementCheats){
                                if($this->inAirTicks < 100){
                                    $this->setMotion(new Vector3(0, $expectedVelocity, 0));
                                }elseif($this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.flight"]))){
                                    $this->timings->stopTiming();

                                    return false;
                                }
                            }
                        }

                        $this->inAirTicks += $tickDiff;
                    }
                }
            }
        }

        $this->timings->stopTiming();

        return true;
    }

    protected function doFoodTick(int $tickDiff = 1) : void{
        if($this->isSurvival()){
            parent::doFoodTick($tickDiff);
        }
    }

    public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
        if($this->isSurvival()){
            return parent::exhaust($amount, $cause);
        }

        return 0.0;
    }

    public function isHungry() : bool{
        return $this->isSurvival() and parent::isHungry();
    }

    public function canBreathe() : bool{
        return $this->isCreative() or parent::canBreathe();
    }

    protected function sendEffectAdd(EffectInstance $effect, bool $replacesOldEffect) : void{
        $pk = new MobEffectPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->eventId = $replacesOldEffect ? MobEffectPacket::EVENT_MODIFY : MobEffectPacket::EVENT_ADD;
        $pk->effectId = $effect->getId();
        $pk->amplifier = $effect->getAmplifier();
        $pk->particles = $effect->isVisible();
        $pk->duration = $effect->getDuration();

        $this->dataPacket($pk);
    }

    protected function sendEffectRemove(EffectInstance $effect) : void{
        $pk = new MobEffectPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->eventId = MobEffectPacket::EVENT_REMOVE;
        $pk->effectId = $effect->getId();

        $this->dataPacket($pk);
    }

    /**
     * Returns whether the player can interact with the specified position. This checks distance and direction.
     *
     * @param Vector3 $pos
     * @param float   $maxDistance
     * @param float   $maxDiff defaults to half of the 3D diagonal width of a block
     *
     * @return bool
     */
    public function canInteract(Vector3 $pos, float $maxDistance, float $maxDiff = M_SQRT3 / 2) : bool{
        $eyePos = $this->getPosition()->add(0, $this->getEyeHeight(), 0);
        if($eyePos->distanceSquared($pos) > $maxDistance ** 2){
            return false;
        }

        $dV = $this->getDirectionVector();
        $eyeDot = $dV->dot($eyePos);
        $targetDot = $dV->dot($pos);
        return ($targetDot - $eyeDot) >= -$maxDiff;
    }

    public function handleLogin(LoginPacket $packet) : bool{
        $this->username = TextFormat::clean($packet->username);
        $this->displayName = $this->username;
        $this->iusername = strtolower($this->username);
        $this->locale = $packet->locale;
        $this->randomClientId = $packet->clientId;

        $this->uuid = UUID::fromString($packet->clientUUID);
        $this->rawUUID = $this->uuid->toBinary();

        $this->deviceModel = $packet->clientData["DeviceModel"];
        $this->deviceOS = $packet->clientData["DeviceOS"];

        $this->setSkin($packet->skin);

        $this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "Plugin reason"));
        if($ev->isCancelled()){
            $this->close("", $ev->getKickMessage());

            return true;
        }

        if(count($this->server->getOnlinePlayers()) >= $this->server->getMaxPlayers() and $this->kick("disconnectionScreen.serverFull", false)){
            return true;
        }

        if(!$this->server->isWhitelisted($this->iusername) and $this->kick("Server is white-listed", false)){
            return true;
        }

        if(
            ($this->isBanned() or $this->server->getIPBans()->isBanned($this->getAddress())) and
            $this->kick("You are banned", false)
        ){
            return true;
        }

        if(!$packet->skipVerification){
            $this->server->getAsyncPool()->submitTask(new VerifyLoginTask($this, $packet));
        }else{
            $this->onVerifyCompleted($packet, null, true);
        }

        return true;
    }

    public function onVerifyCompleted(LoginPacket $packet, ?string $error, bool $signedByMojang) : void{
        if($this->closed){
            return;
        }

        if($error !== null){
            $this->close("", $this->server->getLanguage()->translateString("pocketmine.disconnect.invalidSession", [$error]));
            return;
        }

        $xuid = $packet->xuid;

        if(!$signedByMojang and $xuid !== ""){
            $this->server->getLogger()->warning($this->getName() . " has an XUID, but their login keychain is not signed by Mojang");
            $xuid = "";
        }

        if($xuid === "" or !is_string($xuid)){
            if($signedByMojang){
                $this->server->getLogger()->error($this->getName() . " should have an XUID, but none found");
            }

            if($this->server->requiresAuthentication() and $this->kick("disconnectionScreen.notAuthenticated", false)){ //use kick to allow plugins to cancel this
                return;
            }

            $this->server->getLogger()->debug($this->getName() . " is NOT logged into Xbox Live");
        }else{
            $this->server->getLogger()->debug($this->getName() . " is logged into Xbox Live");
            $this->xuid = $xuid;
        }

        //TODO: encryption

        foreach($this->server->getLoggedInPlayers() as $p){
            if($p !== $this and ($p->iusername === $this->iusername or $this->getUniqueId()->equals($p->getUniqueId()))){
                if(!$p->kick("logged in from another location")){
                    $this->close($this->getLeaveMessage(), "Logged in from another location");

                    return;
                }
            }
        }

        $this->loggedIn = true;
        $this->server->onPlayerLogin($this);
        $this->networkSession->onLoginSuccess();
    }

    public function _actuallyConstruct(){
        $namedtag = $this->server->getOfflinePlayerData($this->username); //TODO: make this async

        if(($level = $this->server->getLevelByName($namedtag->getString("Level", "", true))) === null){
            /** @var Level $level */
            $level = $this->server->getDefaultLevel(); //TODO: default level may be null

            $namedtag->setString("Level", $level->getFolderName());
            $spawnLocation = $level->getSafeSpawn();
            $namedtag->setTag(new ListTag("Pos", [
                new DoubleTag("", $spawnLocation->x),
                new DoubleTag("", $spawnLocation->y),
                new DoubleTag("", $spawnLocation->z)
            ]));
        }

        /** @var float[] $pos */
        $pos = $namedtag->getListTag("Pos")->getAllValues();
        $level->registerChunkLoader($this, ((int) floor($pos[0])) >> 4, ((int) floor($pos[2])) >> 4, true);

        parent::__construct($level, $namedtag);

        $this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
        if($ev->isCancelled()){
            $this->close($this->getLeaveMessage(), $ev->getKickMessage());

            return;
        }

        $this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
            TextFormat::AQUA . $this->username . TextFormat::WHITE,
            $this->networkSession->getIp(),
            $this->networkSession->getPort(),
            $this->id,
            $this->level->getName(),
            round($this->x, 4),
            round($this->y, 4),
            round($this->z, 4)
        ]));

        $this->server->addOnlinePlayer($this);
    }

    protected function initHumanData() : void{
        $this->setNameTag($this->username);
    }

    protected function initEntity() : void{
        parent::initEntity();
        $this->addDefaultWindows();

        $this->playedBefore = ($this->getLastPlayed() - $this->getFirstPlayed()) > 1; // microtime(true) - microtime(true) may have less than one millisecond difference

        $this->gamemode = $this->namedtag->getInt("playerGameType", self::SURVIVAL) & 0x03;
        if($this->server->getForceGamemode()){
            $this->gamemode = $this->server->getGamemode();
            $this->namedtag->setInt("playerGameType", $this->gamemode);
        }

        $this->setAllowFlight($this->isCreative());
        $this->keepMovement = $this->isSpectator() || $this->allowMovementCheats();
        if($this->isOp()){
            $this->setRemoveFormat(false);
        }

        $this->setNameTagVisible();
        $this->setNameTagAlwaysVisible();
        $this->setCanClimb();

        $this->achievements = [];
        $achievements = $this->namedtag->getCompoundTag("Achievements") ?? [];
        /** @var ByteTag $achievement */
        foreach($achievements as $achievement){
            $this->achievements[$achievement->getName()] = $achievement->getValue() !== 0;
        }

        if(!$this->hasValidSpawnPosition()){
            if(($level = $this->server->getLevelByName($this->namedtag->getString("SpawnLevel", ""))) instanceof Level){
                $this->spawnPosition = new Position($this->namedtag->getInt("SpawnX"), $this->namedtag->getInt("SpawnY"), $this->namedtag->getInt("SpawnZ"), $level);
            }else{
                $this->spawnPosition = $this->level->getSafeSpawn();
            }
        }
    }

    /**
     * Sends a chat message as this player. If the message begins with a / (forward-slash) it will be treated
     * as a command.
     *
     * @param string $message
     *
     * @return bool
     */
    public function chat(string $message) : bool{
        $this->doCloseInventory();

        $message = TextFormat::clean($message, $this->removeFormat);
        foreach(explode("\n", $message) as $messagePart){
            $messagePart = trim($messagePart);
            if($messagePart !== "" and strlen($messagePart) <= 255 and $this->messageCounter-- > 0){
                $this->server->getPluginManager()->callEvent($ev = new PlayerChatEvent($this, $messagePart));
                if(!$ev->isCancelled()){
                    $this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [$ev->getPlayer()->getDisplayName(), $ev->getMessage()]), $ev->getRecipients());
                }
            }
        }

        return true;
    }

    public function handleMoveEntityAbsolute(MoveEntityAbsolutePacket $packet) : bool{
        $target = $this->level->getEntity($packet->entityRuntimeId);
        if($target === null)
            return false;

        $target->setPositionAndRotation($packet->position, $packet->zRot, $packet->xRot);

        $this->server->broadcastPacket($this->getViewers(), $packet);
        return true;
    }

    public function handleMovePlayer(MovePlayerPacket $packet) : bool{
        $newPos = $packet->position->subtract(0, $this->baseOffset, 0);

        if($this->isTeleporting and $newPos->distanceSquared($this) > 1){  //Tolerate up to 1 block to avoid problems with client-sided physics when spawning in blocks
            $this->sendPosition($this, null, null, MovePlayerPacket::MODE_RESET);
            $this->server->getLogger()->debug("Got outdated pre-teleport movement from " . $this->getName() . ", received " . $newPos . ", expected " . $this->asVector3());
            //Still getting movements from before teleport, ignore them
        }else{
            // Once we get a movement within a reasonable distance, treat it as a teleport ACK and remove position lock
            if($this->isTeleporting){
                $this->isTeleporting = false;
            }

            $packet->ridingEid = $this->ridingEntity !== null ? $this->ridingEntity->getId() : 0;
            $packet->mode = ($packet->ridingEid == 0 ? MovePlayerPacket::MODE_NORMAL : MovePlayerPacket::MODE_PITCH);
            $packet->onGround = !$this->isGliding() && $this->onGround;

            $packet->yaw = fmod($packet->yaw, 360);
            $packet->pitch = fmod($packet->pitch, 360);

            if($packet->yaw < 0){
                $packet->yaw += 360;
            }

            $this->setRotation($packet->yaw, $packet->pitch);
            $this->newPosition = $newPos;
        }

        return true;
    }

    public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool{
        //TODO: add events so plugins can change this
        if($this->chunk !== null){
            $this->getLevel()->addChunkPacket($this->chunk->getX(), $this->chunk->getZ(), $packet);
        }
        return true;
    }

    public function handleEntityEvent(EntityEventPacket $packet) : bool{
        $this->doCloseInventory();

        switch($packet->event){
            case EntityEventPacket::EATING_ITEM:
                if($packet->data === 0){
                    return false;
                }

                $this->dataPacket($packet);
                $this->server->broadcastPacket($this->getViewers(), $packet);
                break;
            case EntityEventPacket::PLAYER_ADD_XP_LEVELS:
                if($packet->data == 0){
                    return false;
                }

                if($this->isSurvival()){
                    $this->addXpLevels($packet->data);
                }
                break;
            case EntityEventPacket::COMPLETE_TRADE:
                // TODO
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Don't expect much from this handler. Most of it is roughly hacked and duct-taped together.
     *
     * @param InventoryTransactionPacket $packet
     * @return bool
     */
    public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool{
        if($this->isSpectator()){
            $this->sendAllInventories();
            return true;
        }

        /** @var InventoryAction[] $actions */
        $actions = [];
        foreach($packet->actions as $networkInventoryAction){
            try{
                $action = $networkInventoryAction->createInventoryAction($this);
                if($action !== null){
                    $actions[] = $action;
                }
            }catch(\Exception $e){
                $this->server->getLogger()->debug("Unhandled inventory action from " . $this->getName() . ": " . $e->getMessage());
                $this->sendAllInventories();
                return false;
            }
        }

        switch($packet->inventoryType){
            case "Crafting":
                if($this->craftingTransaction === null){
                    $this->craftingTransaction = new CraftingTransaction($this, $actions);
                }else{
                    foreach($actions as $action){
                        $this->craftingTransaction->addAction($action);
                    }
                }

                if($packet->isFinalCraftingPart){
                    //we get the actions for this in several packets, so we need to wait until we have all the pieces before
                    //trying to execute it

                    $ret = true;
                    try{
                        $this->craftingTransaction->execute();

                    }catch(TransactionValidationException $e){
                        $this->server->getLogger()->debug("Failed to execute crafting transaction for " . $this->getName() . ": " . $e->getMessage());
                        $ret = false;
                    }

                    $this->craftingTransaction = null;
                    return $ret;
                }

                return true;
            default:
                if($this->craftingTransaction !== null){
                    $this->server->getLogger()->debug("Got unexpected normal inventory action with incomplete crafting transaction from " . $this->getName() . ", refusing to execute crafting");
                    $this->craftingTransaction = null;
                }
                break;
        }

        switch($packet->transactionType){
            case InventoryTransactionPacket::TYPE_NORMAL:
                $this->setUsingItem(false);
                $transaction = new InventoryTransaction($this, $actions);
                try {
                    $transaction->execute();
                } catch (TransactionValidationException $e) {
                    $this->server->getLogger()->debug("Failed to execute inventory transaction from ".$this->getName().": ".$e->getMessage());
                    $this->server->getLogger()->debug("Actions: ".json_encode($packet->actions));
                    return false;
                }

                //TODO: fix achievement for getting iron from furnace

                return true;
            case InventoryTransactionPacket::TYPE_MISMATCH:
                if(count($packet->actions) > 0){
                    $this->server->getLogger()->debug("Expected 0 actions for mismatch, got " . count($packet->actions) . ", " . json_encode($packet->actions));
                }
                $this->setUsingItem(false);
                $this->sendAllInventories();

                return true;
            case InventoryTransactionPacket::TYPE_USE_ITEM:
                $blockVector = new Vector3($packet->trData->x, $packet->trData->y, $packet->trData->z);
                $face = $packet->trData->face;

                $type = $packet->trData->actionType;
                switch($type){
                    case InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_BLOCK:
                        $this->setUsingItem(false);

                        if(!$this->canInteract($blockVector->add(0.5, 0.5, 0.5), 13) or $this->isSpectator()){
                        }elseif($this->isCreative()){
                            $item = $this->inventory->getItemInHand();
                            if($this->level->useItemOn($blockVector, $item, $face, $packet->trData->clickPos, $this, true)){
                                return true;
                            }
                        }elseif(!$this->inventory->getItemInHand()->equals($packet->trData->itemInHand)){
                            $this->inventory->sendHeldItem($this);
                        }else{
                            $item = $this->inventory->getItemInHand();
                            $oldItem = clone $item;
                            if($this->level->useItemOn($blockVector, $item, $face, $packet->trData->clickPos, $this, true)){
                                if(!$item->equalsExact($oldItem)){
                                    $this->inventory->setItemInHand($item);
                                    $this->inventory->sendHeldItem($this->hasSpawned);
                                }

                                return true;
                            }
                        }

                        $this->inventory->sendHeldItem($this);

                        if($blockVector->distanceSquared($this) > 10000){
                            return true;
                        }

                        $target = $this->level->getBlock($blockVector);
                        $block = $target->getSide($face);

                        /** @var Block[] $blocks */
                        $blocks = array_merge($target->getAllSides(), $block->getAllSides()); //getAllSides() on each of these will include $target and $block because they are next to each other

                        $this->level->sendBlocks([$this], $blocks, UpdateBlockPacket::FLAG_ALL_PRIORITY);

                        return true;
                    case InventoryTransactionPacket::USE_ITEM_ACTION_BREAK_BLOCK:
                        $this->doCloseInventory();

                        $item = $this->inventory->getItemInHand();
                        $oldItem = clone $item;

                        if($this->canInteract($blockVector->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 7) and $this->level->useBreakOn($blockVector, $item, $this, true)){
                            if($this->isSurvival()){
                                if(!$item->equalsExact($oldItem)){
                                    $this->inventory->setItemInHand($item);
                                    $this->inventory->sendHeldItem($this->hasSpawned);
                                }

                                $this->exhaust(0.025, PlayerExhaustEvent::CAUSE_MINING);
                            }
                            return true;
                        }

                        $this->inventory->sendContents($this);
                        $this->inventory->sendHeldItem($this);

                        $target = $this->level->getBlock($blockVector);
                        /** @var Block[] $blocks */
                        $blocks = $target->getAllSides();
                        $blocks[] = $target;

                        $this->level->sendBlocks([$this], $blocks, UpdateBlockPacket::FLAG_ALL_PRIORITY);

                        foreach($blocks as $b){
                            $tile = $this->level->getTile($b);
                            if($tile instanceof Spawnable){
                                $tile->spawnTo($this);
                            }
                        }

                        return true;
                    case InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_AIR:
                        $directionVector = $this->getDirectionVector();

                        if($this->isCreative()){
                            $item = $this->inventory->getItemInHand();
                        }elseif(!$this->inventory->getItemInHand()->equals($packet->trData->itemInHand)){
                            $this->inventory->sendHeldItem($this);
                            return true;
                        }else{
                            $item = $this->inventory->getItemInHand();
                        }

                        $ev = new PlayerInteractEvent($this, $item, null, $directionVector, $face, PlayerInteractEvent::RIGHT_CLICK_AIR);
                        if($this->hasItemCooldown($item)){
                            $ev->setCancelled();
                        }

                        $this->server->getPluginManager()->callEvent($ev);

                        if($ev->isCancelled()){
                            $this->inventory->sendHeldItem($this);
                            return true;
                        }

                        if($item->onClickAir($this, $directionVector)){
                            $this->resetItemCooldown($item);
                            if($this->isSurvival()){
                                $this->inventory->setItemInHand($item);
                            }
                        }

                        $this->setUsingItem(true);

                        return true;
                    default:
                        //unknown
                        break;
                }
                break;
            case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY:
                $target = $this->level->getEntity($packet->trData->entityRuntimeId);
                if($target === null){
                    return false;
                }

                $type = $packet->trData->actionType;

                switch($type){
                    case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT:
                        if(!$target->isAlive()){
                            return true;
                        }

                        $item = $this->inventory->getItemInHand();
                        $clickPos = $packet->trData->clickPos;
                        $slot = $packet->trData->hotbarSlot;

                        $ev = new PlayerInteractEntityEvent($this, $target, $item, $clickPos, $slot);

                        if(!$this->canInteract($target, 8)){
                            $ev->setCancelled();
                        }

                        $this->server->getPluginManager()->callEvent($ev);

                        if(!$ev->isCancelled()){
                            $target->onInteract($this, $item, $clickPos, $slot);
                        }

                        return true;
                    case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK:
                        if(!$target->isAlive()){
                            return true;
                        }
                        if($target instanceof ItemEntity or $target instanceof Arrow){
                            $this->kick("Attempting to attack an invalid entity");
                            $this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidEntity", [$this->getName()]));
                            return false;
                        }

                        $cancelled = false;

                        $heldItem = $this->inventory->getItemInHand();

                        if(!$this->canInteract($target, 8)){
                            $cancelled = true;
                        }elseif($target instanceof Player){
                            if(!$this->server->getConfigBool("pvp")){
                                $cancelled = true;
                            }
                        }

                        $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());

                        $meleeEnchantmentDamage = 0;
                        /** @var EnchantmentInstance[] $meleeEnchantments */
                        $meleeEnchantments = [];
                        foreach($heldItem->getEnchantments() as $enchantment){
                            $type = $enchantment->getType();
                            if($type instanceof MeleeWeaponEnchantment and $type->isApplicableTo($target)){
                                $meleeEnchantmentDamage += $type->getDamageBonus($enchantment->getLevel());
                                $meleeEnchantments[] = $enchantment;
                            }
                        }
                        $ev->setModifier($meleeEnchantmentDamage, EntityDamageEvent::MODIFIER_WEAPON_ENCHANTMENTS);

                        if($cancelled){
                            $ev->setCancelled();
                        }

                        if(!$this->isSprinting() and !$this->isFlying() and $this->fallDistance > 0 and !$this->hasEffect(Effect::BLINDNESS) and !$this->isUnderwater()){
                            $ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
                        }

                        $target->attack($ev);

                        if($ev->isCancelled()){
                            if($heldItem instanceof Durable and $this->isSurvival()){
                                $this->inventory->sendContents($this);
                            }
                            return true;
                        }

                        if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0){
                            $pk = new AnimatePacket();
                            $pk->action = AnimatePacket::ACTION_CRITICAL_HIT;
                            $pk->entityRuntimeId = $target->getId();
                            $this->server->broadcastPacket($target->getViewers(), $pk);
                            if($target instanceof Player){
                                $target->dataPacket($pk);
                            }
                        }

                        foreach($meleeEnchantments as $enchantment){
                            $type = $enchantment->getType();
                            assert($type instanceof MeleeWeaponEnchantment);
                            $type->onPostAttack($this, $target, $enchantment->getLevel());
                        }

                        if($this->isAlive()){
                            //reactive damage like thorns might cause us to be killed by attacking another mob, which
                            //would mean we'd already have dropped the inventory by the time we reached here
                            if($heldItem->onAttackEntity($target) and $this->isSurvival()){ //always fire the hook, even if we are survival
                                $this->inventory->setItemInHand($heldItem);
                            }

                            $this->exhaust(0.3, PlayerExhaustEvent::CAUSE_ATTACK);
                        }

                        return true;
                    default:
                        break; //unknown
                }

                break;
            case InventoryTransactionPacket::TYPE_RELEASE_ITEM:
                try{
                    $type = $packet->trData->actionType;
                    switch($type){
                        case InventoryTransactionPacket::RELEASE_ITEM_ACTION_RELEASE:
                            if($this->isUsingItem()){
                                $item = $this->inventory->getItemInHand();
                                if($this->hasItemCooldown($item)){
                                    $this->inventory->sendContents($this);
                                    return false;
                                }
                                if($item->onReleaseUsing($this)){
                                    $this->resetItemCooldown($item);
                                    $this->inventory->setItemInHand($item);
                                }
                            }else{
                                break;
                            }

                            return true;
                        case InventoryTransactionPacket::RELEASE_ITEM_ACTION_CONSUME:
                            $slot = $this->inventory->getItemInHand();

                            if($slot instanceof Consumable){
                                $ev = new PlayerItemConsumeEvent($this, $slot);
                                if($this->hasItemCooldown($slot)){
                                    $ev->setCancelled();
                                }
                                $this->server->getPluginManager()->callEvent($ev);

                                if($ev->isCancelled() or !$this->consumeObject($slot)){
                                    $this->inventory->sendContents($this);
                                    return true;
                                }

                                $this->resetItemCooldown($slot);

                                if($this->isSurvival()){
                                    $slot->pop();
                                    $this->inventory->setItemInHand($slot);
                                    $this->inventory->addItem($slot->getResidue());
                                }

                                return true;
                            }

                            break;
                        default:
                            break;
                    }
                }finally{
                    $this->setUsingItem(false);
                }

                $this->inventory->sendContents($this);
                break;
            default:
                $this->inventory->sendContents($this);
                break;

        }

        return false; //TODO
    }

    public function equipItem(int $hotbarSlot) : bool{
        if(!$this->inventory->isHotbarSlot($hotbarSlot)){
            $this->inventory->sendContents($this);
            return false;
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerItemHeldEvent($this, $this->inventory->getItem($hotbarSlot), $hotbarSlot));
        if($ev->isCancelled()){
            $this->inventory->sendHeldItem($this);
            return false;
        }

        $this->inventory->setHeldItemIndex($hotbarSlot, false);
        $this->setUsingItem(false);

        return true;
    }

    public function handleInteract(InteractPacket $packet) : bool{
        $this->doCloseInventory();

        $target = $this->level->getEntity($packet->target);
        if($target === null){
            return false;
        }

        switch($packet->action){
            case InteractPacket::ACTION_LEAVE_VEHICLE:
                if($this->ridingEntity === $target){
                    $this->dismountEntity();
                }
                break;
            case InteractPacket::ACTION_MOUSEOVER:
                break; //TODO: handle these
            default:
                $this->server->getLogger()->debug("Unhandled/unknown interaction type " . $packet->action . "received from " . $this->getName());

                return false;
        }

        return true;
    }

    public function pickBlock(Vector3 $pos, bool $addTileNBT) : bool{
        $block = $this->level->getBlock($pos);

        $item = $block->getPickedItem();
        if($addTileNBT){
            $tile = $this->getLevel()->getTile($block);
            if($tile instanceof Tile){
                $nbt = $tile->getCleanedNBT();
                if($nbt instanceof CompoundTag){
                    $item->setCustomBlockData($nbt);
                    $item->setLore(["+(DATA)"]);
                }
            }
        }

        $ev = new PlayerBlockPickEvent($this, $block, $item);
        if(!$this->isCreative(true)){
            $this->server->getLogger()->debug("Got block-pick request from " . $this->getName() . " when not in creative mode (gamemode " . $this->getGamemode() . ")");
            $ev->setCancelled();
        }

        $this->server->getPluginManager()->callEvent($ev);
        if(!$ev->isCancelled()){
            $this->inventory->setItemInHand($ev->getResultItem());
        }

        return true;
    }

    public function startBreakBlock(Vector3 $pos, int $face) : bool{
        if($pos->distanceSquared($this) > 10000){
            return false; //TODO: maybe this should throw an exception instead?
        }

        $target = $this->level->getBlock($pos);

        $ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, $target->getId() === 0 ? PlayerInteractEvent::LEFT_CLICK_AIR : PlayerInteractEvent::LEFT_CLICK_BLOCK);
        if($this->level->checkSpawnProtection($this, $target)){
            $ev->setCancelled();
        }

        $this->getServer()->getPluginManager()->callEvent($ev);
        if($ev->isCancelled()){
            $this->inventory->sendHeldItem($this);
            return true;
        }

        $block = $target->getSide($face);
        if($block->getId() === Block::FIRE){
            $this->level->setBlock($block, BlockFactory::get(Block::AIR));
            return true;
        }

        if(!$this->isCreative()){
            //TODO: improve this to take stuff like swimming, ladders, enchanted tools into account, fix wrong tool break time calculations for bad tools (pmmp/PocketMine-MP#211)
            $breakTime = ceil($target->getBreakTime($this->inventory->getItemInHand()) * 20);
            if($breakTime > 0){
                $this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_START_BREAK, (int) (65535 / $breakTime));
            }
        }

        return true;
    }

    public function continueBreakBlock(Vector3 $pos, int $face) : void{
        $block = $this->level->getBlock($pos);
        $this->level->broadcastLevelEvent(
            $pos,
            LevelEventPacket::EVENT_PARTICLE_PUNCH_BLOCK,
            BlockFactory::toStaticRuntimeId($block->getId(), $block->getDamage()) | ($face << 24)
        );

        //TODO: destroy-progress level event
    }

    public function stopBreakBlock(Vector3 $pos) : void{
        $this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_STOP_BREAK);
    }

    public function toggleSprint(bool $sprint) : void{
        $ev = new PlayerToggleSprintEvent($this, $sprint);
        $this->server->getPluginManager()->callEvent($ev);
        if($ev->isCancelled()){
            $this->sendData($this);
        }else{
            $this->setSprinting($sprint);
        }
    }

    public function toggleSneak(bool $sneak) : void{
        $ev = new PlayerToggleSneakEvent($this, $sneak);
        $this->server->getPluginManager()->callEvent($ev);
        if($ev->isCancelled()){
            $this->sendData($this);
        }else{
            $this->setSneaking($sneak);
        }
    }

    public function toggleGlide(bool $glide) : void{
        $ev = new PlayerToggleGlideEvent($this, $glide);
        $this->server->getPluginManager()->callEvent($ev);
        if($ev->isCancelled()){
            $this->sendData($this);
        }else{
            $this->setGliding($glide);
        }
    }

    public function toggleSwimming(bool $swimming) : void{
        $ev = new PlayerToggleSwimmingEvent($this, $swimming);
        $this->server->getPluginManager()->callEvent($ev);
        if($ev->isCancelled()){
            $this->sendData($this);
        }else{
            $this->setSwimming($swimming);
        }
    }

    public function animate(int $action) : bool{
        $this->server->getPluginManager()->callEvent($ev = new PlayerAnimationEvent($this, $action));
        if($ev->isCancelled()){
            return true;
        }

        $pk = new AnimatePacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->action = $ev->getAnimationType();
        $this->server->broadcastPacket($this->getViewers(), $pk);

        return true;
    }

    /**
     * Drops an item on the ground in front of the player. Returns if the item drop was successful.
     *
     * @param Item $item
     * @return bool if the item was dropped or if the item was null
     */
    public function dropItem(Item $item) : bool{
        if($item->isNull()){
            $this->server->getLogger()->debug($this->getName() . " attempted to drop a null item (" . $item . ")");
            return true;
        }

        $motion = $this->getDirectionVector()->multiply(0.4);

        $this->level->dropItem($this->add(0, 1.3, 0), $item, $motion, 40);

        return true;
    }

    public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool{
        if($packet->entityUniqueId !== $this->getId()){
            return false; //TODO
        }

        $handled = false;

        $isFlying = $packet->getFlag(AdventureSettingsPacket::FLYING);
        if($isFlying and !$this->allowFlight and !$this->server->getAllowFlight()){
            $this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.flight"]));
            return true;
        }elseif($isFlying !== $this->isFlying()){
            $this->server->getPluginManager()->callEvent($ev = new PlayerToggleFlightEvent($this, $isFlying));
            if($ev->isCancelled()){
                $this->sendSettings();
            }else{
                $this->flying = $ev->isFlying();
            }

            $handled = true;
        }

        if($packet->getFlag(AdventureSettingsPacket::NO_CLIP) and !$this->allowMovementCheats and !$this->isSpectator()){
            $this->kick($this->server->getLanguage()->translateString("kick.reason.cheat", ["%ability.noclip"]));
            return true;
        }

        //TODO: check other changes

        return $handled;
    }

    public function handleBlockEntityData(BlockEntityDataPacket $packet) : bool{
        $this->doCloseInventory();

        $pos = new Vector3($packet->x, $packet->y, $packet->z);
        if($pos->distanceSquared($this) > 10000 or $this->level->checkSpawnProtection($this, $pos)){
            return true;
        }

        $t = $this->level->getTile($pos);
        if($t instanceof Spawnable){
            $nbt = new NetworkLittleEndianNBTStream();
            $compound = $nbt->read($packet->namedtag);

            if(!($compound instanceof CompoundTag)){
                throw new \InvalidArgumentException("Expected " . CompoundTag::class . " in block entity NBT, got " . (is_object($compound) ? get_class($compound) : gettype($compound)));
            }

            if(!$t->updateCompoundTag($compound, $this)){
                $t->spawnTo($this);
            }
        }

        return true;
    }

    public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool{
        $tile = $this->level->getTileAt($packet->x, $packet->y, $packet->z);
        if($tile instanceof ItemFrame){
            $ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $tile->getBlock(), null, 5 - $tile->getBlock()->getDamage(), PlayerInteractEvent::LEFT_CLICK_BLOCK);

            if($this->isSpectator() or $this->level->checkSpawnProtection($this, $tile)){
                $ev->setCancelled();
            }

            $this->server->getPluginManager()->callEvent($ev);
            if($ev->isCancelled()){
                $tile->spawnTo($this);
                return true;
            }

            if(lcg_value() <= $tile->getItemDropChance()){
                $this->level->dropItem($tile->getBlock(), $tile->getItem());
            }

            $tile->setItem(null);
            $tile->setItemRotation(0);
        }

        return true;
    }

    public function handleCommandRequest(CommandRequestPacket $packet) : bool{
        if($packet->originData->type !== CommandOriginData::ORIGIN_PLAYER) return false;

        $command = $packet->command;
        if($command{0} != "/"){
            return false;
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerCommandPreprocessEvent($this, $command));
        if($ev->isCancelled()){
            return true;
        }

        Timings::$playerCommandTimer->startTiming();
        $this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
        Timings::$playerCommandTimer->stopTiming();

        return true;
    }

    public function handleBookEdit(BookEditPacket $packet) : bool{
        /** @var WritableBook $oldBook */
        $oldBook = $this->inventory->getItem($packet->inventorySlot);
        if($oldBook->getId() !== Item::WRITABLE_BOOK){
            return false;
        }

        $newBook = clone $oldBook;
        $modifiedPages = [];

        switch($packet->type){
            case BookEditPacket::TYPE_REPLACE_PAGE:
                $newBook->setPageText($packet->pageNumber, $packet->text);
                $modifiedPages[] = $packet->pageNumber;
                break;
            case BookEditPacket::TYPE_ADD_PAGE:
                $newBook->insertPage($packet->pageNumber, $packet->text);
                $modifiedPages[] = $packet->pageNumber;
                break;
            case BookEditPacket::TYPE_DELETE_PAGE:
                $newBook->deletePage($packet->pageNumber);
                $modifiedPages[] = $packet->pageNumber;
                break;
            case BookEditPacket::TYPE_SWAP_PAGES:
                $newBook->swapPages($packet->pageNumber, $packet->secondaryPageNumber);
                $modifiedPages = [$packet->pageNumber, $packet->secondaryPageNumber];
                break;
            case BookEditPacket::TYPE_SIGN_BOOK:
                /** @var WrittenBook $newBook */
                $newBook = Item::get(Item::WRITTEN_BOOK, 0, 1, $newBook->getNamedTag());
                $newBook->setAuthor($packet->author);
                $newBook->setTitle($packet->title);
                $newBook->setGeneration(WrittenBook::GENERATION_ORIGINAL);
                break;
            default:
                return false;
        }

        $this->getServer()->getPluginManager()->callEvent($event = new PlayerEditBookEvent($this, $oldBook, $newBook, $packet->type, $modifiedPages));
        if($event->isCancelled()){
            return true;
        }

        $this->getInventory()->setItem($packet->inventorySlot, $event->getNewBook());

        return true;
    }

    /**
     * @param DataPacket $packet
     * @param bool       $immediate
     *
     * @return bool
     */
    public function sendDataPacket(DataPacket $packet, bool $immediate = false) : bool{
        if(!$this->isConnected()){
            return false;
        }

        //Basic safety restriction. TODO: improve this
        if(!$this->loggedIn and !$packet->canBeSentBeforeLogin()){
            throw new \InvalidArgumentException("Attempted to send " . get_class($packet) . " to " . $this->getName() . " too early");
        }

        return $this->networkSession->sendDataPacket($packet, $immediate);
    }

    /**
     * @param DataPacket $packet
     *
     * @return bool
     */
    public function dataPacket(DataPacket $packet) : bool{
        return $this->sendDataPacket($packet, false);
    }

    /**
     * @param DataPacket $packet
     *
     * @return bool
     */
    public function directDataPacket(DataPacket $packet) : bool{
        return $this->sendDataPacket($packet, true);
    }

    /**
     * Transfers a player to another server.
     *
     * @param string $address The IP address or hostname of the destination server
     * @param int    $port    The destination port, defaults to 19132
     * @param string $message Message to show in the console when closing the player
     *
     * @return bool if transfer was successful.
     */
    public function transfer(string $address, int $port = 19132, string $message = "transfer") : bool{
        $this->server->getPluginManager()->callEvent($ev = new PlayerTransferEvent($this, $address, $port, $message));

        if(!$ev->isCancelled()){
            $pk = new TransferPacket();
            $pk->address = $ev->getAddress();
            $pk->port = $ev->getPort();
            $this->directDataPacket($pk);
            $this->close("", $ev->getMessage(), false);

            return true;
        }

        return false;
    }

    /**
     * Kicks a player from the server
     *
     * @param string $reason
     * @param bool   $isAdmin
     * @param TextContainer|string $quitMessage
     *
     * @return bool
     */
    public function kick(string $reason = "", bool $isAdmin = true, $quitMessage = null) : bool{
        $this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage()));
        if(!$ev->isCancelled()){
            $reason = $ev->getReason();
            $message = $reason;
            if($isAdmin){
                if(!$this->isBanned()){
                    $message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
                }
            }else{
                if($reason === ""){
                    $message = "disconnectionScreen.noReason";
                }
            }
            $this->close($ev->getQuitMessage(), $message);

            return true;
        }

        return false;
    }

    /**
     * Returns bossbar with id
     *
     * @param int $id
     * @return null|Bossbar
     */
    public function getBossbar(int $id) : ?Bossbar{
        return $this->bossbars[$id] ?? null;
    }

    /**
     * Removes a bossbar with id
     *
     * @param int $id
     * @return bool
     */
    public function removeBossbar(int $id) : bool{
        if(!isset($this->bossbars[$id])){
            return false;
        }

        $this->bossbars[$id]->hideFrom($this);
        unset($this->bossbars[$id]);

        return true;
    }

    /**
     * Adds a bossbar to the player.
     *
     * @param Bossbar $bossBar
     * @param int|null $id
     * @return int
     */
    public function addBossbar(Bossbar $bossBar, int $id = null) : int{
        if($id !== null and isset($this->bossbars[$id])){
            return $id;
        }

        $bossBar->showTo($this);

        $id = $id ?? $this->bossbarIdCounter++;
        $this->bossbars[$id] = $bossBar;

        return $id;
    }

    /**
     * Adds a title text to the user's screen, with an optional subtitle.
     *
     * @param string $title
     * @param string $subtitle
     * @param int    $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
     * @param int    $stay Duration in ticks to stay on screen for
     * @param int    $fadeOut Duration in ticks for fade-out.
     */
    public function addTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1){
        $this->setTitleDuration($fadeIn, $stay, $fadeOut);
        if($subtitle !== ""){
            $this->addSubTitle($subtitle);
        }
        $this->sendTitleText($title, SetTitlePacket::TYPE_SET_TITLE);
    }

    /**
     * Sets the subtitle message, without sending a title.
     *
     * @param string $subtitle
     */
    public function addSubTitle(string $subtitle){
        $this->sendTitleText($subtitle, SetTitlePacket::TYPE_SET_SUBTITLE);
    }

    /**
     * Adds small text to the user's screen.
     *
     * @param string $message
     */
    public function addActionBarMessage(string $message){
        $this->sendTitleText($message, SetTitlePacket::TYPE_SET_ACTIONBAR_MESSAGE);
    }

    /**
     * Removes the title from the client's screen.
     */
    public function removeTitles(){
        $pk = new SetTitlePacket();
        $pk->type = SetTitlePacket::TYPE_CLEAR_TITLE;
        $this->dataPacket($pk);
    }

    /**
     * Resets the title duration settings.
     */
    public function resetTitles(){
        $pk = new SetTitlePacket();
        $pk->type = SetTitlePacket::TYPE_RESET_TITLE;
        $this->dataPacket($pk);
    }

    /**
     * Sets the title duration.
     *
     * @param int $fadeIn Title fade-in time in ticks.
     * @param int $stay Title stay time in ticks.
     * @param int $fadeOut Title fade-out time in ticks.
     */
    public function setTitleDuration(int $fadeIn, int $stay, int $fadeOut){
        if($fadeIn >= 0 and $stay >= 0 and $fadeOut >= 0){
            $pk = new SetTitlePacket();
            $pk->type = SetTitlePacket::TYPE_SET_ANIMATION_TIMES;
            $pk->fadeInTime = $fadeIn;
            $pk->stayTime = $stay;
            $pk->fadeOutTime = $fadeOut;
            $this->dataPacket($pk);
        }
    }

    /**
     * Internal function used for sending titles.
     *
     * @param string $title
     * @param int $type
     */
    protected function sendTitleText(string $title, int $type){
        $pk = new SetTitlePacket();
        $pk->type = $type;
        $pk->text = $title;
        $this->dataPacket($pk);
    }

    /**
     * Sends a direct chat message to a player
     *
     * @param TextContainer|string $message
     */
    public function sendMessage($message){
        if($message instanceof TextContainer){
            if($message instanceof TranslationContainer){
                $this->sendTranslation($message->getText(), $message->getParameters());
                return;
            }
            $message = $message->getText();
        }

        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_RAW;
        $pk->message = $this->server->getLanguage()->translateString($message);
        $this->dataPacket($pk);
    }

    /**
     * @param string   $message
     * @param string[] $parameters
     */
    public function sendTranslation(string $message, array $parameters = []){
        $pk = new TextPacket();
        if(!$this->server->isLanguageForced()){
            $pk->type = TextPacket::TYPE_TRANSLATION;
            $pk->needsTranslation = true;
            $pk->message = $this->server->getLanguage()->translateString($message, $parameters, "pocketmine.");
            foreach($parameters as $i => $p){
                $parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pocketmine.");
            }
            $pk->parameters = $parameters;
        }else{
            $pk->type = TextPacket::TYPE_RAW;
            $pk->message = $this->server->getLanguage()->translateString($message, $parameters);
        }
        $this->dataPacket($pk);
    }

    /**
     * Sends a popup message to the player
     *
     * TODO: add translation type popups
     *
     * @param string $message
     */
    public function sendPopup(string $message) {
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_POPUP;
        $pk->message = $message;
        $this->dataPacket($pk);
    }

    public function sendTip(string $message){
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_TIP;
        $pk->message = $message;
        $this->dataPacket($pk);
    }

    /**
     * @param string $sender
     * @param string $message
     */
    public function sendWhisper(string $sender, string $message){
        $pk = new TextPacket();
        $pk->type = TextPacket::TYPE_WHISPER;
        $pk->sourceName = $sender;
        $pk->message = $message;
        $this->dataPacket($pk);
    }

    /**
     * Sends a Form to the player, or queue to send it if a form is already open.
     *
     * @param Form     $form
     * @param int|null $id
     */
    public function sendForm(Form $form, ?int $id = null) : void{
        $form->setInUse();

        $id = $id ?? $this->formIdCounter++;
        $this->formQueue[$id] = $form;
        $this->sendFormRequestPacket($form, $id);
    }

    /**
     * @param int   $formId
     * @param mixed $responseData
     *
     * @return bool
     */
    public function onFormSubmit(int $formId, $responseData) : bool{
        if(isset($this->formQueue[$formId])){
            /** @var Form $form */
            $form = $this->formQueue[$formId];

            try{
                $form = $form->handleResponse($this, $responseData);
            }catch(\Throwable $e){
                $this->server->getLogger()->logException($e);
            }

            if($form !== null){
                $this->sendForm($form);
            }
        }else{
            $this->server->getLogger()->debug("Form with id $formId not found");
            return false;
        }

        return true;
    }

    private function sendFormRequestPacket(Form $form, int $id) : void{
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = json_encode($form);
        $this->dataPacket($pk);
    }

    public function sendServerSettings(ServerSettingsForm $form){
        $id = $this->formIdCounter++;
        $pk = new ServerSettingsResponsePacket();
        $pk->formId = $id;
        $pk->formData = json_encode($form);
        $this->dataPacket($pk);
    }

    public function getServerSettingsForm() : ?ServerSettingsForm{
        return $this->serverSettingsForm;
    }

    public function setServerSettingsForm(ServerSettingsForm $form) : void{
        $this->serverSettingsForm = $form;
    }

    /**
     * Note for plugin developers: use kick() with the isAdmin
     * flag set to kick without the "Kicked by admin" part instead of this method.
     *
     * @param TextContainer|string $message Message to be broadcasted
     * @param string               $reason Reason showed in console
     * @param bool                 $notify
     */
    final public function close($message = "", string $reason = "generic reason", bool $notify = true) : void{
        if($this->isConnected() and !$this->closed){

            try{
                $ip = $this->networkSession->getIp();
                $port = $this->networkSession->getPort();
                $this->networkSession->serverDisconnect($reason, $notify);
                $this->networkSession = null;

                $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
                $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

                $this->stopSleep();

                if($this->spawned){
                    $this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message, $reason));
                    if($ev->getQuitMessage() != ""){
                        $this->server->broadcastMessage($ev->getQuitMessage());
                    }

                    try{
                        $this->save();
                    }catch(\Throwable $e){
                        $this->server->getLogger()->critical("Failed to save player data for " . $this->getName());
                        $this->server->getLogger()->logException($e);
                    }
                }

                if($this->isValid()){
                    foreach($this->usedChunks as $index => $d){
                        Level::getXZ($index, $chunkX, $chunkZ);
                        $this->level->unregisterChunkLoader($this, $chunkX, $chunkZ);
                        foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
                            $entity->despawnFrom($this);
                        }
                        unset($this->usedChunks[$index]);
                    }
                }
                $this->usedChunks = [];
                $this->loadQueue = [];

                if($this->loggedIn){
                    $this->server->onPlayerLogout($this);
                    foreach($this->server->getOnlinePlayers() as $player){
                        if(!$player->canSee($this)){
                            $player->showPlayer($this);
                        }
                    }
                    $this->hiddenPlayers = [];
                }

                $this->removeAllWindows(true);
                $this->windows = [];
                $this->windowIndex = [];
                $this->cursorInventory = null;
                $this->craftingGrid = null;

                if($this->constructed){
                    parent::close();
                }
                $this->spawned = false;

                if($this->loggedIn){
                    $this->loggedIn = false;
                    $this->server->removeOnlinePlayer($this);
                }

                $this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logOut", [
                    TextFormat::AQUA . $this->getName() . TextFormat::WHITE,
                    $ip,
                    $port,
                    $this->getServer()->getLanguage()->translateString($reason)
                ]));

                $this->spawnPosition = null;

                if($this->perm !== null){
                    $this->perm->clearPermissions();
                    $this->perm = null;
                }
            }catch(\Throwable $e){
                $this->server->getLogger()->logException($e);
            }finally{
                $this->server->removePlayer($this);
            }
        }
    }

    public function __debugInfo(){
        return [];
    }

    public function canSaveWithChunk() : bool{
        return false;
    }

    public function setCanSaveWithChunk(bool $value) : void{
        throw new \BadMethodCallException("Players can't be saved with chunks");
    }

    /**
     * Handles player data saving
     *
     * @param bool $async
     *
     * @throws \InvalidStateException if the player is closed
     */
    public function save(bool $async = false){
        if($this->closed){
            throw new \InvalidStateException("Tried to save closed player");
        }

        parent::saveNBT();

        if($this->isValid()){
            $this->namedtag->setString("Level", $this->level->getFolderName());
        }

        if($this->hasValidSpawnPosition()){
            $this->namedtag->setString("SpawnLevel", $this->spawnPosition->getLevel()->getFolderName());
            $this->namedtag->setInt("SpawnX", $this->spawnPosition->getFloorX());
            $this->namedtag->setInt("SpawnY", $this->spawnPosition->getFloorY());
            $this->namedtag->setInt("SpawnZ", $this->spawnPosition->getFloorZ());

            if(!$this->isAlive()){
                $this->namedtag->setTag(new ListTag("Pos", [
                    new DoubleTag("", $this->spawnPosition->x),
                    new DoubleTag("", $this->spawnPosition->y),
                    new DoubleTag("", $this->spawnPosition->z)
                ]));
            }
        }

        $achievements = new CompoundTag("Achievements");
        foreach($this->achievements as $achievement => $status){
            $achievements->setByte($achievement, $status === true ? 1 : 0);
        }
        $this->namedtag->setTag($achievements);

        $this->namedtag->setInt("playerGameType", $this->gamemode);
        $this->namedtag->setLong("lastPlayed", (int)floor(microtime(true) * 1000));

        if($this->username != "" and $this->namedtag instanceof CompoundTag){
            $this->server->saveOfflinePlayerData($this->username, $this->namedtag, $async);
        }
    }

    public function kill() : void{
        if(!$this->spawned){
            return;
        }

        parent::kill();

        $this->networkSession->onDeath();
    }

    protected function onDeath() : void{
        $message = "death.attack.generic";

        $params = [
            $this->getDisplayName()
        ];

        $cause = $this->getLastDamageCause();

        switch($cause === null ? EntityDamageEvent::CAUSE_CUSTOM : $cause->getCause()){
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                if($cause instanceof EntityDamageByEntityEvent){
                    $e = $cause->getDamager();
                    if($e instanceof Player){
                        $message = "death.attack.player";
                        $params[] = $e->getDisplayName();
                        break;
                    }elseif($e instanceof Living){
                        $message = "death.attack.mob";
                        $params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
                        break;
                    }else{
                        $params[] = "Unknown";
                    }
                }
                break;
            case EntityDamageEvent::CAUSE_PROJECTILE:
                if($cause instanceof EntityDamageByEntityEvent){
                    $e = $cause->getDamager();
                    if($e instanceof Player){
                        $message = "death.attack.arrow";
                        $params[] = $e->getDisplayName();
                    }elseif($e instanceof Living){
                        $message = "death.attack.arrow";
                        $params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
                        break;
                    }else{
                        $params[] = "Unknown";
                    }
                }
                break;
            case EntityDamageEvent::CAUSE_SUICIDE:
                $message = "death.attack.generic";
                break;
            case EntityDamageEvent::CAUSE_VOID:
                $message = "death.attack.outOfWorld";
                break;
            case EntityDamageEvent::CAUSE_FALL:
                if($cause instanceof EntityDamageEvent){
                    if($cause->getFinalDamage() > 2){
                        $message = "death.fell.accident.generic";
                        break;
                    }
                }
                $message = "death.attack.fall";
                break;

            case EntityDamageEvent::CAUSE_SUFFOCATION:
                $message = "death.attack.inWall";
                break;

            case EntityDamageEvent::CAUSE_LAVA:
                $message = "death.attack.lava";
                break;

            case EntityDamageEvent::CAUSE_FIRE:
                $message = "death.attack.onFire";
                break;

            case EntityDamageEvent::CAUSE_FIRE_TICK:
                $message = "death.attack.inFire";
                break;

            case EntityDamageEvent::CAUSE_DROWNING:
                $message = "death.attack.drown";
                break;

            case EntityDamageEvent::CAUSE_CONTACT:
                if($cause instanceof EntityDamageByBlockEvent){
                    if($cause->getDamager()->getId() === Block::CACTUS){
                        $message = "death.attack.cactus";
                    }
                }
                break;

            case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                if($cause instanceof EntityDamageByEntityEvent){
                    $e = $cause->getDamager();
                    if($e instanceof Player){
                        $message = "death.attack.explosion.player";
                        $params[] = $e->getDisplayName();
                    }elseif($e instanceof Living){
                        $message = "death.attack.explosion.player";
                        $params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
                        break;
                    }
                }else{
                    $message = "death.attack.explosion";
                }
                break;

            case EntityDamageEvent::CAUSE_MAGIC:
                $message = "death.attack.magic";
                break;

            case EntityDamageEvent::CAUSE_CUSTOM:
                break;

            default:
                break;
        }

        //Crafting grid must always be evacuated even if keep-inventory is true. This dumps the contents into the
        //main inventory and drops the rest on the ground.
        $this->doCloseInventory();

        $ev = new PlayerDeathEvent($this, $this->getDrops(), new TranslationContainer($message, $params));
        $ev->setKeepInventory($this->server->keepInventory);
        $ev->setKeepExperience($this->server->keepExperience);
        $this->server->getPluginManager()->callEvent($ev);

        $this->keepExperience = $ev->getKeepExperience();

        if(!$ev->getKeepInventory()){
            foreach($ev->getDrops() as $item){
                $this->level->dropItem($this, $item);
            }

            if($this->inventory !== null){
                $this->inventory->setHeldItemIndex(0, false); //This is already handled when sending contents, don't send it twice
                $this->inventory->clearAll();
            }
            if($this->armorInventory !== null){
                $this->armorInventory->clearAll();
            }
            if($this->cursorInventory !== null){
                $this->cursorInventory->clearAll();
            }
        }

        if($ev->getDeathMessage() != ""){
            $this->server->broadcast($ev->getDeathMessage(), Server::BROADCAST_CHANNEL_USERS);
        }
    }

    protected function onDeathUpdate(int $tickDiff) : bool{
        if(parent::onDeathUpdate($tickDiff)){
            $this->despawnFromAll(); //non-player entities rely on close() to do this for them
        }

        return false; //never flag players for despawn
    }

    public function respawn() : void{
        if($this->server->isHardcore()){
            $this->setBanned(true);
            return;
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->getSpawn()));

        $realSpawn = Position::fromObject($ev->getRespawnPosition()->add(0.5, 0, 0.5), $ev->getRespawnPosition()->getLevel());
        $this->teleport($realSpawn);

        $this->setSprinting(false);
        $this->setSneaking(false);

        $this->extinguish();
        $this->setAirSupplyTicks($this->getMaxAirSupplyTicks());
        $this->deadTicks = 0;
        $this->noDamageTicks = 60;

        $this->removeAllEffects();
        $this->setHealth($this->getMaxHealth());

        foreach($this->attributeMap->getAll() as $attr){
            $attr->resetToDefault();
        }

        $this->sendData($this);
        $this->sendData($this->getViewers());

        $this->sendSettings();
        $this->sendAllInventories();

        $this->spawnToAll();
        $this->scheduleUpdate();

        $this->networkSession->onRespawn();
    }

    protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
        parent::applyPostDamageEffects($source);

        $this->exhaust(0.3, PlayerExhaustEvent::CAUSE_DAMAGE);
    }

    public function attack(EntityDamageEvent $source) : void{
        if(!$this->isAlive()){
            return;
        }

        if($this->isCreative()
            and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
            and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
        ){
            $source->setCancelled();
        }elseif($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL){
            $source->setCancelled();
        }

        parent::attack($source);
    }

    public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void{
        if($this->spawned and $players === null){
            $players = $this->getViewers();
            $players[] = $this;
        }
        parent::broadcastEntityEvent($eventId, $eventData, $players);
    }

    public function getOffsetPosition(Vector3 $vector3) : Vector3{
        $result = parent::getOffsetPosition($vector3);
        $result->y += 0.001; //Hack for MCPE falling underground for no good reason (TODO: find out why it's doing this)
        return $result;
    }

    public function sendPosition(Vector3 $pos, float $yaw = null, float $pitch = null, int $mode = MovePlayerPacket::MODE_NORMAL, array $targets = null){
        $yaw = $yaw ?? $this->yaw;
        $pitch = $pitch ?? $this->pitch;

        $pk = new MovePlayerPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->getOffsetPosition($pos);
        $pk->pitch = $pitch;
        $pk->headYaw = $yaw;
        $pk->yaw = $yaw;
        $pk->mode = $mode;

        if($targets !== null){
            $this->server->broadcastPacket($targets, $pk);
        }else{
            $this->dataPacket($pk);
        }

        $this->newPosition = null;
    }

    /**
     * {@inheritdoc}
     */
    public function teleport(Vector3 $pos, float $yaw = null, float $pitch = null) : bool{
        if(parent::teleport($pos, $yaw, $pitch)){

            $this->removeAllWindows();

            $this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_TELEPORT);
            $this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_TELEPORT, $this->getViewers());

            $this->spawnToAll();

            $this->resetFallDistance();
            $this->nextChunkOrderRun = 0;
            $this->newPosition = null;
            $this->stopSleep();

            $this->isTeleporting = true;

            //TODO: workaround for player last pos not getting updated
            //Entity::updateMovement() normally handles this, but it's overridden with an empty function in Player
            $this->resetLastMovements();

            return true;
        }

        return false;
    }

    protected function addDefaultWindows(){
        $this->addWindow($this->getInventory(), ContainerIds::INVENTORY, true);

        $this->addWindow($this->getArmorInventory(), ContainerIds::ARMOR, true);

        $this->cursorInventory = new PlayerCursorInventory($this);
        $this->addWindow($this->cursorInventory, ContainerIds::CURSOR, true);

        $this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);

        $this->offHandInventory = new PlayerOffHandInventory($this);
        $this->addWindow($this->offHandInventory, ContainerIds::OFFHAND, true);

        //TODO: more windows
    }

    public function getCursorInventory() : PlayerCursorInventory{
        return $this->cursorInventory;
    }

    public function getOffHandInventory() : PlayerOffHandInventory{
        return $this->offHandInventory;
    }

    public function getCraftingGrid() : CraftingGrid{
        return $this->craftingGrid;
    }

    /**
     * @param CraftingGrid $grid
     */
    public function setCraftingGrid(CraftingGrid $grid) : void{
        $this->craftingGrid = $grid;
    }

    /**
     * @internal Called to clean up crafting grid and cursor inventory when it is detected that the player closed their
     * inventory.
     */
    public function doCloseInventory() : void{
        /** @var Inventory[] $inventories */
        $inventories = [$this->craftingGrid, $this->cursorInventory];
        foreach($inventories as $inventory){
            $contents = $inventory->getContents();
            if(count($contents) > 0){
                $drops = $this->inventory->addItem(...$contents);
                foreach($drops as $drop){
                    $this->dropItem($drop);
                }

                $inventory->clearAll();
            }
        }

        if($this->craftingGrid->getGridWidth() > CraftingGrid::SIZE_SMALL){
            $this->craftingGrid = new CraftingGrid($this, CraftingGrid::SIZE_SMALL);
        }
    }

    /**
     * @internal Called by the network session when a player closes a window.
     *
     * @param int $windowId
     *
     * @return bool
     */
    public function doCloseWindow(int $windowId) : bool{
        if($windowId === 0){
            return false;
        }

        $this->doCloseInventory();

        if(isset($this->windowIndex[$windowId])){
            $this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->windowIndex[$windowId], $this));
            $this->removeWindow($this->windowIndex[$windowId]);
            return true;
        }
        if($windowId === 255){
            //Closed a fake window
            return true;
        }

        return false;
    }

    /**
     * Returns the window ID which the inventory has for this player, or -1 if the window is not open to the player.
     *
     * @param Inventory $inventory
     *
     * @return int
     */
    public function getWindowId(Inventory $inventory) : int{
        return $this->windows[spl_object_hash($inventory)] ?? ContainerIds::NONE;
    }

    /**
     * Returns the inventory window open to the player with the specified window ID, or null if no window is open with
     * that ID.
     *
     * @param int $windowId
     *
     * @return Inventory|null
     */
    public function getWindow(int $windowId){
        return $this->windowIndex[$windowId] ?? null;
    }

    /**
     * Returns the opened inventory type that is opened, or null if no window is opened.
     *
     * @param string $class
     * @return null|Inventory
     */
    public function getWindowByType(string $class) : ?Inventory{
        foreach($this->windowIndex as $inventory){
            if($inventory instanceof $class){
                return $inventory;
            }
        }

        return null;
    }

    public function getLastOpenContainerInventory() : ?ContainerInventory{
        $windows = array_filter($this->windowIndex, function($inv) : bool{ return $inv instanceof ContainerInventory; });
        return !empty($windows) ? max($windows) : null;
    }

    /**
     * Opens an inventory window to the player. Returns the ID of the created window, or the existing window ID if the
     * player is already viewing the specified inventory.
     *
     * @param Inventory $inventory
     * @param int|null  $forceId Forces a special ID for the window
     * @param bool      $isPermanent Prevents the window being removed if true.
     *
     * @return int
     */
    public function addWindow(Inventory $inventory, int $forceId = null, bool $isPermanent = false) : int{
        if(($id = $this->getWindowId($inventory)) !== ContainerIds::NONE){
            return $id;
        }

        if($forceId === null){
            $this->windowCnt = $cnt = max(ContainerIds::FIRST, ++$this->windowCnt % ContainerIds::LAST);
        }else{
            $cnt = $forceId;
        }
        $this->windowIndex[$cnt] = $inventory;
        $this->windows[spl_object_hash($inventory)] = $cnt;
        if($inventory->open($this)){
            if($isPermanent){
                $this->permanentWindows[$cnt] = true;
            }
            return $cnt;
        }else{
            $this->removeWindow($inventory);

            return -1;
        }
    }

    /**
     * Removes an inventory window from the player.
     *
     * @param Inventory $inventory
     * @param bool      $force Forces removal of permanent windows such as normal inventory, cursor
     *
     * @throws \BadMethodCallException if trying to remove a fixed inventory window without the `force` parameter as true
     */
    public function removeWindow(Inventory $inventory, bool $force = false){
        $id = $this->windows[$hash = spl_object_hash($inventory)] ?? null;

        if($id !== null and !$force and isset($this->permanentWindows[$id])){
            throw new \BadMethodCallException("Cannot remove fixed window $id (" . get_class($inventory) . ") from " . $this->getName());
        }

        $inventory->close($this);
        if($id !== null){
            unset($this->windows[$hash], $this->windowIndex[$id], $this->permanentWindows[$id]);
        }
    }

    /**
     * Removes all inventory windows from the player. By default this WILL NOT remove permanent windows.
     *
     * @param bool $removePermanentWindows Whether to remove permanent windows.
     */
    public function removeAllWindows(bool $removePermanentWindows = false){
        foreach($this->windowIndex as $id => $window){
            if(!$removePermanentWindows and isset($this->permanentWindows[$id])){
                continue;
            }

            $this->removeWindow($window, $removePermanentWindows);
        }
    }

    public function sendAllInventories(){
        foreach($this->windowIndex as $id => $inventory){
            $inventory->sendContents($this);
        }
    }

    public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
        $this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
    }

    public function getMetadata(string $metadataKey){
        return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
    }

    public function hasMetadata(string $metadataKey) : bool{
        return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
    }

    public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
        $this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
    }

    public function onChunkChanged(Chunk $chunk){
        if(isset($this->usedChunks[$hash = Level::chunkHash($chunk->getX(), $chunk->getZ())])){
            $this->usedChunks[$hash] = false;
            if(!$this->spawned){
                $this->nextChunkOrderRun = 0;
            }
        }
    }

    public function onChunkLoaded(Chunk $chunk){

    }

    public function onChunkPopulated(Chunk $chunk){

    }

    public function onChunkUnloaded(Chunk $chunk){

    }

    public function onBlockChanged(Vector3 $block){

    }

    public function getLoaderId() : int{
        return $this->loaderId;
    }

    public function isLoaderActive() : bool{
        return $this->isConnected();
    }

	public function getDeviceModel() : string{
		return $this->deviceModel;
	}

    public function getDeviceOS() : int{
        return $this->deviceOS;
    }

    public function isTeleporting() : bool{
        return $this->isTeleporting;
    }
}