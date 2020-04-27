<?php

namespace DuoIncure\ComplexRelics;

use DuoIncure\ComplexRelics\functions\CommonRelicFunctions;
use DuoIncure\ComplexRelics\functions\RareRelicFunctions;
use DuoIncure\ComplexRelics\functions\EpicRelicFunctions;
use DuoIncure\ComplexRelics\functions\LegendaryRelicFunctions;
use pocketmine\item\Item;
use pocketmine\Player;

class RelicFunctions {

	public const RELIC_TAG = "isRelic";

	/** @var Main */
	private $plugin;
	private $crf, $rrf, $erf, $lrf;

	/**
	 * RelicFunctions constructor.
	 * @param Main $plugin
	 */
	public function __construct(ComplexRelics $plugin){
		$this->plugin = $plugin;
		$this->crf = new CommonRelicFunctions($plugin);
		$this->rrf = new RareRelicFunctions($plugin);
		$this->erf = new EpicRelicFunctions($plugin);
		$this->lrf = new LegendaryRelicFunctions($plugin);
	}

	/**
	 * @param Player $player
	 * @param Item $relic
	 * @param string $type
	 */
	public function giveCorrespondingReward(Player $player, Item $relic, string $type){
		switch($type){
			case "common":
				$this->crf->giveCommonRelicReward($player, $relic);
				break;
			case "rare":
				$this->rrf->giveRareRelicReward($player, $relic);
				break;
			case "epic":
				$this->erf->giveEpicRelicReward($player, $relic);
				break;
			case "legendary":
				$this->lrf->giveLegendaryRelicReward($player, $relic);
				break;
		}
	}

	/**
	 * @param Player $player
	 * @param string $type
	 */
	public function giveCorrespondingRelic(Player $player, string $type){
		switch($type){
			case "common":
				$this->crf->giveCommonRelic($player);
				break;
			case "rare":
				$this->rrf->giveRareRelic($player);
				break;
			case "epic":
				$this->erf->giveEpicRelic($player);
				break;
			case "legendary":
				$this->lrf->giveLegendaryRelic($player);
				break;
		}
	}
}