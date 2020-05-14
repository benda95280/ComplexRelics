<?php
declare(strict_types=1);

namespace MiningRelics;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use MiningRelics\RelicFunctions;
use MiningRelics\functions\CreateItemPiggy;



class MiningRelics extends PluginBase{

	public const VERSION = 7;

	/** @var Config */
	public static $cfg;
	/** @var Language */
	public static $lang;
	/** @var Default Language */
	public static $defaultLang;
    /** @var MiningRelics */
    private static $instance;
    /** @var relicList */	
	public static $relicList = [];
    /** @var langList */	
	public static $langList = [];
	/** @var PiggyCE_List */	
	public static $RelicPiggyCE = null;
	

	public function onEnable()
	{

        if (self::$instance === null) {
            self::$instance = $this;
        }
		
		//Check DataFolder exist or make it
		if(!file_exists($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		} else if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->getLogger()->info("Config Not Found! Creating new config...");
			$this->saveDefaultConfig();
		}
		self::$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		self::$cfg = self::$cfg->getAll();
		if(self::$cfg["version"] < self::VERSION){
			$this->getLogger()->error("Config Version is outdated! Please delete your current config file!");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
				
		$pathLang = $this->getDataFolder() . "lang";
        //Save default lang on first load
		@mkdir($pathLang);
        foreach ($this->getResources() as $resource) {
            $this->saveResource("lang" . DIRECTORY_SEPARATOR . $resource->getFilename());
		}

		//Check Language file exist
		if(!file_exists($pathLang.DIRECTORY_SEPARATOR .self::$cfg["language"].".yml")){
			$this->getLogger()->error("Default language file not found. Please, verify your configuration!");
			$this->getLogger()->error($pathLang.DIRECTORY_SEPARATOR .self::$cfg["language"].".yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		else {
			self::$defaultLang = self::$cfg["language"];
			$files = glob($pathLang . DIRECTORY_SEPARATOR . '*.{yml}', GLOB_BRACE);
			//Load all language availaible
			foreach($files as $file) {
				$langCodification = basename($file, ".yml");
				self::$lang[$langCodification] = (new Config($file, Config::YAML))->getAll();
			}
		}
		
		//define relic list
		self::$relicList = array_keys(self::$cfg["relic-list"]);
		//define language list
		self::$langList = array_keys(self::$lang);

		//Validate config file and register lister if OK
		if (RelicFunctions::checkIn())	{
			$this->getServer()->getPluginManager()->registerEvents(new RelicsListener($this), $this);
		}
		else $this->getServer()->getPluginManager()->disablePlugin(MiningRelics::getInstance());	

		//Init PiggyCE ?
		if (self::$cfg["PiggyCE"]) {
			self::$RelicPiggyCE = new CreateItemPiggy();
			self::$RelicPiggyCE->initReclicPiggy();
		}
		

	}

    public static function getInstance(): MiningRelics
    {
        return self::$instance;
    }
	
	public static function getPlayerLanguage(player $player) {
		if(self::$cfg["language_manager"]) {
			//Get language of the player
			$languageManager = self::getInstance()->getServer()->getPluginManager()->getPlugin("Language");
			//If his language is not available in server, set default of this plugin
			$langOfPlayer = $languageManager->getLanguage($player);
			if (in_array($langOfPlayer,self::$langList)) return $langOfPlayer;
			else {
				return self::$defaultLang;
				$this->getLogger()->error("MiningRelics - Language Error: Language '$langOfPlayer' was not found in this plugin, but exist in language available");
			}
			
			
		}
		else return self::$defaultLang;
	}
	

}
