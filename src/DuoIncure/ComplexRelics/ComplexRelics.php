<?php
declare(strict_types=1);

namespace DuoIncure\ComplexRelics;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use function mkdir;
use function file_exists;

class ComplexRelics extends PluginBase{

	public const VERSION = 4;

	/** @var Config */
	private $cfg;
	/** @var Language */
	public static $lang;
	/** @var RelicFunctions */
	public $relicFunctions;

	public function onEnable()
	{
		//Check DataFolder exist or make it
		if(!file_exists($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		} else if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->getLogger()->info("Config Not Found! Creating new config...");
			$this->saveDefaultConfig();
		}
		$this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->cfg = $this->cfg->getAll();
		if($this->cfg["version"] < self::VERSION){
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
		if(!file_exists($pathLang.DIRECTORY_SEPARATOR .$this->cfg["language"].".yml")){
			$this->getLogger()->error("Language file not found. Please, verify your configuration!");
			$this->getLogger()->error($pathLang.DIRECTORY_SEPARATOR .$this->cfg["language"].".yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		else {
			self::$lang = new Config($pathLang.DIRECTORY_SEPARATOR .$this->cfg["language"].".yml", Config::YAML);
			self::$lang = self::$lang->getAll();
		}
		$this->relicFunctions = new RelicFunctions($this);
		
		//Validate config file
		$this->relicFunctions->checkIn();
		
		$this->getServer()->getPluginManager()->registerEvents(new RelicsListener($this), $this);
	}

	/**
	 * @return RelicFunctions
	 */
	public function getRelicFunctions(){
		if(!$this->relicFunctions instanceof RelicFunctions){
			throw new \RuntimeException("relicFunctions was not an instanceof RelicFunctions");
		}
		return $this->relicFunctions;
	}

}
