<?php

declare(strict_types = 1);

namespace JavierLeon9966\ProperDuels;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use Generator;
use JavierLeon9966\ProperDuels\arena\Arena;
use JavierLeon9966\ProperDuels\arena\ArenaManager;
use JavierLeon9966\ProperDuels\command\arena\ArenaCommand;
use JavierLeon9966\ProperDuels\command\duel\DuelCommand;
use JavierLeon9966\ProperDuels\command\kit\KitCommand;
use JavierLeon9966\ProperDuels\config\Config;
use JavierLeon9966\ProperDuels\config\DatabaseType;
use JavierLeon9966\ProperDuels\game\GameListener;
use JavierLeon9966\ProperDuels\game\GameManager;
use JavierLeon9966\ProperDuels\kit\Kit;
use JavierLeon9966\ProperDuels\kit\KitManager;
use JavierLeon9966\ProperDuels\session\SessionListener;
use JavierLeon9966\ProperDuels\session\SessionManager;
use JavierLeon9966\ProperDuels\utils\ContentsSerializer;
use JsonException;
use JsonMapper;
use JsonMapper_Exception;
use pocketmine\command\CommandSender;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\math\Vector3;
use pocketmine\nbt\NbtDataException;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\plugin\PluginManager;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\ConfigLoadException;
use poggit\libasynql\DataConnector;
use poggit\libasynql\ExtensionMissingException;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;
use RuntimeException;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitGenerator\Loading;
use SOFe\InfoAPI\InfoAPI;
use Symfony\Component\Filesystem\Path;
use Throwable;

final class ProperDuels extends PluginBase{

	/** @var \SOFe\AwaitGenerator\Loading<ArenaManager> */
	private static Loading $arenaManager;

	/** @var \SOFe\AwaitGenerator\Loading<KitManager> */
	private static Loading $kitManager;

	private static GameManager $gameManager;

	/** @var \SOFe\AwaitGenerator\Loading<\JavierLeon9966\ProperDuels\QueueManager> */
	private static Loading $queueManager;

	private static SessionManager $sessionManager;
	private static DataConnector $database;

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, ArenaManager> */
	public static function getArenaManager(): Generator{
		return yield from self::$arenaManager->get();
	}

	public static function getGameManager(): GameManager{
		return self::$gameManager;
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, KitManager> */
	public static function getKitManager(): Generator{
		return yield from self::$kitManager->get();
	}

	/** @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, QueueManager> */
	public static function getQueueManager(): Generator{
		return yield from self::$queueManager->get();
	}

	public static function getSessionManager(): SessionManager{
		return self::$sessionManager;
	}

	/** @throws DisablePluginException */
	public function onEnable(): void{
		if(!PacketHooker::isRegistered()){
			try{
				PacketHooker::register($this);
			}catch(HookAlreadyRegistered $e){
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		$this->setupInfoAPI();

		$unMarshaledConfig = $this->getUnMarshaledConfig();

		$mergedDb = $this->getMergedDb($unMarshaledConfig);

		$server = $this->getServer();
		$pluginManager = $server->getPluginManager();
		$this->setupManagers($mergedDb, $unMarshaledConfig, $pluginManager, $server);

		try{
			$pluginManager->registerEvents(new GameListener($unMarshaledConfig, self::$sessionManager), $this);
			$pluginManager->registerEvents(new SessionListener(self::$sessionManager), $this);
		}catch(PluginException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}

		Await::f2c(function() use ($server, $unMarshaledConfig): Generator{
			/**
			 * @var ArenaManager $arenaManager
			 * @var KitManager $kitManager
			 * @var QueueManager $queueManager
			 */
			[$arenaManager, $kitManager, $queueManager] = yield from Await::all([
				self::$arenaManager->get(),
				self::$kitManager->get(),
				self::$queueManager->get()
			]);

			$server->getCommandMap()->registerAll('properduels', [
				new ArenaCommand(
					$this,
					'arena',
					$arenaManager,
					'Manage arenas for duel matches.'
				),
				new DuelCommand(
					$this,
					'duel',
					$unMarshaledConfig,
					self::$sessionManager,
					self::$gameManager,
					$arenaManager,
					$queueManager,
					$kitManager,
					'Duel players and queue to a match.'
				),
				new KitCommand(
					$this,
					'kit',
					$kitManager,
					'Manage kits for duel matches.'
				)
			]);

			$this->getLogger()->info('Finished registering commands.');
		});
	}

	/** @throws \RuntimeException */
	public function onDisable(): void{
		if(isset(self::$database)){
			self::$database->waitAll();
		}
		if(isset(self::$gameManager)){
			self::$gameManager->close();
		}
		if(isset(self::$sessionManager)){
			self::$sessionManager->close();
		}
	}

	private function setupInfoAPI(): void{
		InfoAPI::addKind($this,
			'properduels/arena',
			static fn(Arena $arena, ?CommandSender $sender): string => $arena->getName(),
			'Arena',
			'A duel arena');
		InfoAPI::addMapping($this,
			['firstSpawnPosition', 'firstSpawnPos'],
			static fn(Arena $arena): Vector3 => $arena->getFirstSpawnPos(),
			help: 'Arena first spawn position');
		InfoAPI::addMapping($this,
			['secondSpawnPosition', 'secondSpawnPos'],
			static fn(Arena $arena): Vector3 => $arena->getSecondSpawnPos(),
			help: 'Arena second spawn position');
		InfoAPI::addMapping($this,
			'kit',
			static fn(Arena $arena): string => $arena->getKit() ?? 'Random',
			help: 'Arena kit');
	}

	/** @throws \pocketmine\plugin\DisablePluginException */
	private function getUnMarshaledConfig(): Config{
		try{
			$config = $this->getConfig();
		}catch(ConfigLoadException $e){
			$this->getLogger()->error($e->getMessage());
			throw new DisablePluginException();
		}
		$config->setDefaults([
			'database' => [
				'type' => 'sqlite3',
				'sqlite' => [
					'file' => 'data.sqlite'
				],
				'mysql' => [
					'host' => '127.0.0.1',
					'username' => 'ProperDuels',
					'password' => 'mypassword123',
					'schema' => 'ProperDuels',
					'port' => 3306
				],
				'worker-limit' => 1
			],
			'match' => [
				'allow-commands' => false,
				'countdown' => [
					'message' => '{aqua}Match starting in {blue}{seconds}',
					'time' => 5
				],
				'failure' => [
					'levelNotFound' => '{red}Couldn\'t start match as no level was found',
					'kitNotFound' => '{red}Couldn\'t start match as no kit was found'
				],
				'finish' => '{green}{winner}{gray} won a match against {red}{defeated}{gray} with type {blue}{arena}',
				'inUse' => '{red}There is currently a match in that arena!',
				'start' => '{green}Duel!'
			],
			'request' => [
				'accept' => [
					'message' => '{player} {green}accepted the Duel request!',
					'playerInDuel' => '{red}You cannot accept this player to a Duel!',
					'success' => '{green}You accepted {white}{player}{green}\'s Duel request!'
				],
				'deny' => [
					'message' => '{player} {red}denied the Duel request!',
					'success' => '{red}You denied {white}{player}{red}\'s Duel request!'
				],
				'expire' => [
					'from' => '{yellow}The Duel request from {white}{player}{yellow} has expired.',
					'to' => '{yellow}The Duel request to {white}{player}{yellow} has expired.',
					'time' => 60
				],
				'invite' => [
					'failure' => '{red}You have already invited to Duel!',
					'message' => '{player}{aqua} has invited you to {arena} Duels! You have {seconds} seconds to accept.',
					'playerInDuel' => '{red}You cannot invite this player to a Duel!',
					'playerNotFound' => '{red}You haven\'t been invited to Duel, or the invitation has expired!',
					'sameTarget' => '{red}You can\'t send a invite to yourself!',
					'success' => '{yellow}You invited {white}{player}{yellow} to {arena} Duels! They have {seconds} seconds to accept.'
				]
			]
		]);
		try{
			if($config->hasChanged()){
				$config->save();
			}
		}catch(JsonException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}

		$mapper = new JsonMapper();
		$mapper->bEnforceMapType = false;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bStrictObjectTypeChecking = false;
		try{
			/** @var Config $unMarshaledConfig */
			$unMarshaledConfig = $mapper->map($config->getAll(), Config::class);
		}catch(JsonMapper_Exception $e){
			$this->getLogger()->error("Configuration error: {$e->getMessage()}");
			throw new DisablePluginException();
		}
		return $unMarshaledConfig;
	}

	/**
	 * @param array{'sqlite': string, 'mysql': string} $statements
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 * @throws \poggit\libasynql\SqlError
	 */
	private function migrateSQLiteArenasDatabase(Config $unMarshaledConfig, array $statements): Generator{
		$arenasDb = libasynql::create(
			$this,
			[
				'type' => $unMarshaledConfig->database->type->value,
				'sqlite' => [
					'file' => 'arenas.sqlite'
				],
				'mysql' => [
					'host' => $unMarshaledConfig->database->mysql->host,
					'username' => $unMarshaledConfig->database->mysql->username,
					'password' => $unMarshaledConfig->database->mysql->password,
					'schema' => $unMarshaledConfig->database->mysql->schema,
					'port' => $unMarshaledConfig->database->mysql->port
				],
				'worker-limit' => $unMarshaledConfig->database->workerLimit
			],
			$statements
		);
		$arenasQueries = new RawQueries($arenasDb);

		$arenas = yield from $arenasQueries->loadArenas();
		$arenasDb->close();
		unlink(Path::join($this->getDataFolder(), 'arenas.sqlite'));
		/** @var list<array{'Arena': string, 'Name'?: string}>|list<array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string}> $arenas */
		if(count($arenas) === 0){
			return;

		}
		$arenasManager = yield from self::$arenaManager->get();
		if(isset($arenas[0]['Arena'])){
			yield from $this->migrateArenas($arenas, $arenasManager);
			return;
		}

		try{
			$gens = [];
			/**
			 * @var string $name
			 * @var string $levelName
			 * @var float $firstSpawnPosX
			 * @var float $firstSpawnPosY
			 * @var float $firstSpawnPosZ
			 * @var float $secondSpawnPosX
			 * @var float $secondSpawnPosY
			 * @var float $secondSpawnPosZ
			 * @var ?string $kit
			 */
			foreach($arenas as ['Name' => $name,
				'LevelName' => $levelName,
				'FirstSpawnPosX' => $firstSpawnPosX,
				'FirstSpawnPosY' => $firstSpawnPosY,
				'FirstSpawnPosZ' => $firstSpawnPosZ,
				'SecondSpawnPosX' => $secondSpawnPosX,
				'SecondSpawnPosY' => $secondSpawnPosY,
				'SecondSpawnPosZ' => $secondSpawnPosZ,
				'Kit' => $kit]){
				$gens[] = $arenasManager->add(new Arena(
					$name,
					$levelName,
					new Vector3($firstSpawnPosX, $firstSpawnPosY, $firstSpawnPosZ),
					new Vector3($secondSpawnPosX, $secondSpawnPosY, $secondSpawnPosZ),
					$kit
				));
			}
		}catch(RuntimeException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
		yield from Await::all($gens);
	}

	/**
	 * @param array{'sqlite': string, 'mysql': string} $statements
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 * @throws \poggit\libasynql\SqlError
	 */
	private function migrateSQLiteKitsDatabase(Config $unMarshaledConfig, array $statements): Generator{
		$kitsDb = libasynql::create(
			$this,
			[
				'type' => $unMarshaledConfig->database->type->value,
				'sqlite' => [
					'file' => 'kits.sqlite'
				],
				'mysql' => [
					'host' => $unMarshaledConfig->database->mysql->host,
					'username' => $unMarshaledConfig->database->mysql->username,
					'password' => $unMarshaledConfig->database->mysql->password,
					'schema' => $unMarshaledConfig->database->mysql->schema,
					'port' => $unMarshaledConfig->database->mysql->port
				],
				'worker-limit' => $unMarshaledConfig->database->workerLimit
			],
			$statements
		);
		$kitsQueries = new RawQueries($kitsDb);

		$kits = yield from $kitsQueries->loadKits();
		$kitsDb->close();
		unlink(Path::join($this->getDataFolder(), 'kits.sqlite'));
		/** @var list<array{Name: string, Kit: string}|array{Name: string, Armor: string, Inventory: string}> $kits */
		if(count($kits) === 0){
			return;
		}
		$kitsManager = yield from self::$kitManager->get();
		if(isset($kits[0]['Kit'])){
			yield from $this->migrateKits($kits, $kitsManager);
			return;
		}
		try{
			$gens = [];
			/**
			 * @var string $name
			 * @var string $serializedArmor
			 * @var string $serializedInventory
			 */
			foreach($kits as ['Name' => $name, 'Armor' => $serializedArmor, 'Inventory' => $serializedInventory]){
				$armorContents = ContentsSerializer::deserializeItemContents($serializedArmor);
				$inventoryContents = ContentsSerializer::deserializeItemContents($serializedInventory);
				$gens[] = $kitsManager->add(new Kit($name, $armorContents, $inventoryContents));
			}
		}catch(NbtDataException|SavedDataLoadingException|ItemTypeDeserializeException|RuntimeException $e){
			throw new AssumptionFailedError('This should never happen', 0, $e);
		}
		yield from Await::all($gens);
	}

	/** @throws \pocketmine\plugin\DisablePluginException */
	private function getMergedDb(Config $unMarshaledConfig): RawQueries{
		try{
			$statements = [
				'sqlite' => Path::join('sqlite', 'stmt.sql'),
				'mysql' => Path::join('mysql', 'stmt.sql')
			];
			$mergedDb = new RawQueries(self::$database = libasynql::create(
				$this,
				[
					'type' => $unMarshaledConfig->database->type->value,
					'sqlite' => [
						'file' => $unMarshaledConfig->database->sqlite->file
					],
					'mysql' => [
						'host' => $unMarshaledConfig->database->mysql->host,
						'username' => $unMarshaledConfig->database->mysql->username,
						'password' => $unMarshaledConfig->database->mysql->password,
						'schema' => $unMarshaledConfig->database->mysql->schema,
						'port' => $unMarshaledConfig->database->mysql->port
					],
					'worker-limit' => $unMarshaledConfig->database->workerLimit
				],
				$statements
			));
			if($unMarshaledConfig->database->type === DatabaseType::Sqlite3){
				$gens = [];
				if(file_exists(Path::join($this->getDataFolder(), 'kits.sqlite'))){
					$gens[] = $this->migrateSQLiteKitsDatabase($unMarshaledConfig, $statements);
				}
				if(file_exists(Path::join($this->getDataFolder(), 'arenas.sqlite'))){
					$gens[] = $this->migrateSQLiteArenasDatabase($unMarshaledConfig, $statements);
				}
				Await::f2c(function() use ($mergedDb, $gens): Generator{
					yield from $mergedDb->initForeignKeys();
					if(count($gens) > 0){
						yield from Await::all($gens);
						$this->getLogger()->notice('Migrated arenas and kits from old database.');
					}
				}, catches: ['' => fn(Throwable $e) => throw $e]);
			}else{
				Await::f2c(function() use ($mergedDb): Generator{
					$migrateKitsIfAny  = function() use ($mergedDb): Generator{
						$kitsManager = yield from self::$kitManager->get();
						$kits = yield from $mergedDb->loadKits();
						/** @var list<array{Name: string, Kit: string}|array{Name: string, Armor: string, Inventory: string}> $kits */
						if(count($kits) === 0){
							return;
						}
						if(!isset($kits[0]['Kit'])){
							return;
						}
						yield from $mergedDb->resetKits();
						yield from $this->migrateKits($kits, $kitsManager);
					};
					$migrateArenasIfAny = function() use ($mergedDb): Generator{
						$kitsManager = yield from self::$arenaManager->get();
						$arenas = yield from $mergedDb->loadArenas();
						/** @var list<array{'Arena': string, 'Name'?: string}>|list<array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string}> $arenas */
						if(count($arenas) === 0){
							return;
						}
						if(!isset($arenas[0]['Arena'])){
							return;
						}
						yield from $mergedDb->resetArenas();
						yield from $this->migrateArenas($arenas, $kitsManager);
					};
					yield from Await::all([$migrateKitsIfAny(), $migrateArenasIfAny()]);
				});
			}
		}catch(ExtensionMissingException|SqlError $e){
			$this->getLogger()->error($e->getMessage());
			throw new DisablePluginException;
		}
		return $mergedDb;
	}

	private function setupManagers(RawQueries $mergedDb,
		Config $unMarshaledConfig,
		PluginManager $pluginManager,
		Server $server): void{
		self::$kitManager = new Loading(function() use ($mergedDb, $unMarshaledConfig): Generator{
			return yield from KitManager::create($mergedDb, $unMarshaledConfig->database->type);
		});
		self::$arenaManager = new Loading(function() use ($mergedDb, $unMarshaledConfig): Generator{
			return yield from ArenaManager::create($mergedDb, $unMarshaledConfig->database->type);
		});
		self::$gameManager = new GameManager();
		self::$sessionManager =  new SessionManager(
				self::$gameManager,
				$unMarshaledConfig,
				$this,
				$pluginManager
			);
		self::$queueManager = new Loading(function() use ($server, $unMarshaledConfig): Generator{
			$kitManager = yield from self::$kitManager->get();
			return new QueueManager(
				self::$gameManager,
				self::$sessionManager,
				$kitManager,
				$server->getWorldManager(),
				$this,
				$unMarshaledConfig
			);
		});
	}

	/**
	 * @param list<array{Name: string, Kit: string}|array{Name: string, Armor: string, Inventory: string}> $kits
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	private function migrateKits(array $kits, KitManager $kitsManager): Generator{
		$gens = [];
		$kits = array_map(static function(string $serialized): Kit{
			$deserialized = unserialize($serialized);
			if(!$deserialized instanceof Kit){
				throw new AssumptionFailedError('This should never happen');
			}
			return $deserialized;
		}, array_column($kits, 'Kit', 'Name'));
		foreach($kits as $kit){
			try{
				$gens[] = $kitsManager->add($kit);
			}catch(RuntimeException $e){
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		yield from Await::all($gens);
	}

	/**
	 * @param list<array{'Arena': string, 'Name'?: string}>|list<array{'Name': string, 'LevelName': string, 'FirstSpawnPosX': float, 'FirstSpawnPosY': float, 'FirstSpawnPosZ': float, 'SecondSpawnPosX': float, 'SecondSpawnPosY': float, 'SecondSpawnPosZ': float, 'Kit': ?string}> $arenas
	 * @return Generator<mixed, Await::RESOLVE|null|Await::RESOLVE_MULTI|Await::REJECT|Await::ONCE|Await::ALL|Await::RACE|Generator<mixed, mixed, mixed, mixed>, mixed, void>
	 */
	private function migrateArenas(array $arenas, ArenaManager $arenasManager): Generator{
		$gens = [];
		/** @var array<string, Arena> $unserializedArenas */
		$unserializedArenas = array_map('unserialize', array_column($arenas, 'Arena', 'Name'));
		foreach($unserializedArenas as $arena){
			try{
				$gens[] = $arenasManager->add($arena);
			}catch(RuntimeException $e){
				throw new AssumptionFailedError('This should never happen', 0, $e);
			}
		}
		yield from Await::all($gens);
	}
}
