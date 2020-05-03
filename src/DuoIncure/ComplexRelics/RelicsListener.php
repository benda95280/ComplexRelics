<?php

namespace DuoIncure\ComplexRelics;

use pocketmine\event\Listener;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use DuoIncure\ComplexRelics\RelicFunctions;
use DuoIncure\ComplexRelics\functions\CreateRelicFunctions;




class RelicsListener implements Listener {

	/**
	 * @param PlayerInteractEvent $ev
	 */
	public function onInteract(PlayerInteractEvent $ev){
		$player = $ev->getPlayer();
		if($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK || $ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR){
			$item = $ev->getItem();
			$nbt = $item->getNamedTag();
			if($nbt->hasTag(RelicFunctions::RELIC_TAG)){
				$relicType = $nbt->getTagValue(RelicFunctions::RELIC_TAG, StringTag::class);
				if (in_array($relicType, ComplexRelics::$relicList)) {
					CreateRelicFunctions::giveRelicReward($player, $item, $relicType);
				}
				else {
					$player->sendMessage(ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["main"]["errorOnInteract"]);
				}
			}
		}
	}

	/**
	 * @param BlockBreakEvent $ev
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onBreak(BlockBreakEvent $ev){
		$player = $ev->getPlayer();
		$config = ComplexRelics::$cfg;
		$blockID = $ev->getBlock()->getId();
		$levelName = $player->getLevel()->getName();
		$acceptedBlockAndRelics = RelicFunctions::acceptedBlockAndRelics($blockID);		
		
		if ($acceptedBlockAndRelics && ($config["worlds"][0] == "*" OR in_array($levelName, $config["worlds"])) && ($player->isOp() === true ? $config["apply-to-op"] : true) && ($config["right-tool-needed"] === true ? ($ev->getBlock()->isCompatibleWithTool($ev->getItem()))  : true) ) {
			$chance = mt_rand(1, 200);
			$previousChance = 0;
			
			foreach ($config["relic-list"] as $key => $value) {
				$relicType = $key;
				$relicChance = $value["chance"];
				$chance -= $previousChance;
				if ($chance <= $relicChance && ($acceptedBlockAndRelics == "*" OR in_array($relicType,$acceptedBlockAndRelics))) {
					CreateRelicFunctions::giveRelic($player, $relicType);
					if($config["relic-list"][$relicType]["sound"]) RelicFunctions::sendTotemSound($player);
					break;
				}
				else $previousChance = $relicChance;
			}
		}
	}

	public function onPlace(BlockPlaceEvent $ev) {
		$player = $ev->getPlayer();
		$blockID = $ev->getBlock()->getId();
		$config = ComplexRelics::$cfg;
		$configBlocks = $config["block-ids"];
		$levelName = $player->getLevel()->getName();

		if (in_array($blockID, $configBlocks) && $config["prevent-placing"] == true && ($config["worlds"][0] == "*" OR in_array($levelName, $config["worlds"]))) {
			$ev->setCancelled();
		}
	}
	
}