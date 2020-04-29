<?php

namespace DuoIncure\ComplexRelics;

use pocketmine\event\Listener;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class RelicsListener implements Listener {

	/** @var Main */
	private $plugin;

	/**
	 * RelicsListener constructor.
	 * @param Main $plugin
	 */
	public function __construct(ComplexRelics $plugin)
	{
		$this->plugin = $plugin;
	}

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
				switch($relicType){
					case "common":
						$this->plugin->getRelicFunctions()->giveCorrespondingReward($player, $item, "common");
						break;
					case "rare":
						$this->plugin->getRelicFunctions()->giveCorrespondingReward($player, $item, "rare");
						break;
					case "epic":
						$this->plugin->getRelicFunctions()->giveCorrespondingReward($player, $item, "epic");
						break;
					case "legendary":
						$this->plugin->getRelicFunctions()->giveCorrespondingReward($player, $item, "legendary");
						break;
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
		$config = $this->plugin->getConfig()->getAll();
		$blockID = $ev->getBlock()->getId();
		$levelName = $player->getLevel()->getName();
		$acceptedBlockAndRelics = $this->relicFunctions->acceptedBlockAndRelics($blockID);
		
		if ($acceptedBlockAndRelics && ($config["worlds"][0] == "*" OR in_array($levelName, $config["worlds"]))) {
			$commonChance = $config["common"]["chance"] ?? 50;
			$rareChance = $config["rare"]["chance"] ?? 25;
			$epicChance = $config["epic"]["chance"] ?? 15;
			$legendaryChance = $config["legendary"]["chance"] ?? 10;
			$chance = mt_rand(1, 200);
        
			if ($chance <= $commonChance && ($acceptedBlockAndRelics == "*" OR in_array("common",$acceptedBlockAndRelics))) {
				$this->plugin->getRelicFunctions()->giveCorrespondingRelic($player, "common");
			}
			else {
				$chance -= $commonChance;
				if ($chance <= $rareChance && ($acceptedBlockAndRelics == "*" OR in_array("rare",$acceptedBlockAndRelics))) {
					$this->plugin->getRelicFunctions()->giveCorrespondingRelic($player, "rare");
				}
				else {
					$chance -= $rareChance;
					if ($chance <= $epicChance && ($acceptedBlockAndRelics == "*" OR in_array("epic",$acceptedBlockAndRelics))) {
						$this->plugin->getRelicFunctions()->giveCorrespondingRelic($player, "epic");
					} 
					else {
						$chance -= $epicChance;
						if ($chance <= $legendaryChance && ($acceptedBlockAndRelics == "*" OR in_array("legendary",$acceptedBlockAndRelics))) {
							$this->plugin->getRelicFunctions()->giveCorrespondingRelic($player, "legendary");
						}
					}
				}
			}
		}
	}

	public function onPlace(BlockPlaceEvent $ev) {
		$player = $ev->getPlayer();
		$blockID = $ev->getBlock()->getId();
		$config = $this->plugin->getConfig()->getAll();
		$configBlocks = $config["block-ids"];
		$levelName = $player->getLevel()->getName();

		if (in_array($blockID, $configBlocks) && $config["prevent-placing"] == true && ($config["worlds"][0] == "*" OR in_array($levelName, $config["worlds"]))) {
			$ev->setCancelled();
		}
	}
	
}