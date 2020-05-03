<?php

namespace DuoIncure\ComplexRelics;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class RelicFunctions {

	public const RELIC_TAG = "isRelic";


	public static function sendTotemSound (Player $player) {
        $te = new LevelEventPacket();
		$te->evid = LevelEventPacket::EVENT_SOUND_TOTEM;
        $te->position = $player->add(0, $player->eyeHeight, 0);
        $te->data = 0;
        $player->dataPacket($te);		
	}
	
	public static function acceptedBlockAndRelics ($blockID) {
		$config = ComplexRelics::getInstance()->getConfig()->getAll();
		$configBlocks = $config["block-ids"];
		$return = false;
		
		foreach ($configBlocks as $key => $value){
			if ( is_array($value) ) {
				if (array_key_first($value)  === $blockID) {
					$return = $value[array_key_first($value)];
					break;	
				}				
			}
			else {
				if ($value === $blockID) {
					$return = "*";
					break;	
				}
			}
		}
		return $return;
	}
	
	public static function checkIn() {
		$configBlocks = ComplexRelics::$cfg["block-ids"];
		$worlds = ComplexRelics::$cfg["worlds"];
		
		//Check Block Config
		foreach ($configBlocks as $key => $value){
			//Block can contain array of relics
			if ( is_array($value) ) {
				foreach ($value as $keyBlockRelicsType => $blockRelicsType){
					if (array_diff($blockRelicsType, ComplexRelics::$relicList)) {
						ComplexRelics::getInstance()->getLogger()->error("Bad configuration for Relics Block ID, array is wrong for Line (start from 0) $key -> $keyBlockRelicsType");
						return false;	
					}
				}
			}
			//Block can contain just it's ID
			elseif (!is_int($value)) {
				ComplexRelics::getInstance()->getLogger()->error("Bad configuration for Relics Block ID: $value, must be integer.");
				return false;	
			}
		}
		
		//Check relic-list config and language too
		foreach (ComplexRelics::$cfg["relic-list"] as $keyRelic => $valueRelic){
			//RELIC in relic-list must contain  chance / commands
			if (!isset($valueRelic["chance"]) || !is_int($valueRelic["chance"]))	{
				ComplexRelics::getInstance()->getLogger()->error("Bad configuration for relic-list (CHANCE), for relic $keyRelic, must be integer");
				return false;				
			}
			if (!isset($valueRelic["sound"]) || !is_bool($valueRelic["sound"]))	{
				ComplexRelics::getInstance()->getLogger()->error("Bad configuration for relic-list (SOUND), for relic $keyRelic, must be boolean");
				return false;				
			}
			if (!isset($valueRelic["commands"]) || !is_array($valueRelic["commands"]) || count($valueRelic["commands"]) < 1) {
				ComplexRelics::getInstance()->getLogger()->error("Bad configuration for relic-list (COMMANDS), for relic $keyRelic, must be array and contain at least one command");
				return false;	
			}
			
			//And RELIC must have their translation in all translation file loaded
			foreach (ComplexRelics::$lang as $keyLang => $valueLang){
				//$keyLang = fr_FR
				//Check RELIC
				if (!isset($valueLang["relic"][$keyRelic]) || !is_array($valueLang["relic"][$keyRelic]) || count($valueLang["relic"][$keyRelic]) < 1) {
					ComplexRelics::getInstance()->getLogger()->error("Bad configuration for language file $keyLang, for relic $keyRelic, no value set");
					return false;
				}
				if (!isset($valueLang["relic"][$keyRelic]["name"]) || !is_string($valueLang["relic"][$keyRelic]["name"])) {
					ComplexRelics::getInstance()->getLogger()->error("Bad configuration for language file $keyLang, for relic $keyRelic and 'name', no value set");
					return false;				
				}
				if (!isset($valueLang["relic"][$keyRelic]["title"]) || !is_string($valueLang["relic"][$keyRelic]["title"])) {
					ComplexRelics::getInstance()->getLogger()->error("Bad configuration for language file $keyLang, for relic $keyRelic and 'title', no value set");
					return false;				
				}
				if (!isset($valueLang["relic"][$keyRelic]["tip"]) || !is_string($valueLang["relic"][$keyRelic]["tip"])) {
					ComplexRelics::getInstance()->getLogger()->error("Bad configuration for language file $keyLang, for relic $keyRelic and 'tip', no value set");
					return false;				
				}
				if (!isset($valueLang["relic"][$keyRelic]["message"]) || !is_string($valueLang["relic"][$keyRelic]["message"])) {
					ComplexRelics::getInstance()->getLogger()->error("Bad configuration for language file $keyLang, for relic $keyRelic and 'message', no value set");
					return false;				
				}
				//Check LORE
				if (!isset($valueLang["lore"][$keyRelic]) || !is_array($valueLang["lore"][$keyRelic]) || count($valueLang["lore"][$keyRelic]) < 1) {
					ComplexRelics::getInstance()->getLogger()->error("Bad configuration for language file $keyLang, for lore $keyRelic, not an array");
					return false;
				}
			}
		}		
		
		//Check World config
		if ( !is_array($worlds) ) {
			ComplexRelics::getInstance()->getLogger()->error("World must be an array");
			return false;		
		}
		
		//Order Relics by chance
		uasort(ComplexRelics::$cfg["relic-list"], function ($a, $b) {
			return $b["chance"] <=> $a["chance"];
		});
		
		//Check LanguageManager
		
		if(ComplexRelics::$cfg["language_manager"]) {
			$languageManager = ComplexRelics::getInstance()->getServer()->getPluginManager()->getPlugin("Language");
			if($languageManager === null OR !$languageManager->isEnabled()) {
				ComplexRelics::getInstance()->getLogger()->error("Language manager not found or not loaded, revert to default language");
				ComplexRelics::$cfg["language_manager"] = false;
			}
		}
		
		return true;
	}

}