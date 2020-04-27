<?php

namespace DuoIncure\ComplexRelics\functions;

use DuoIncure\ComplexRelics\ComplexRelics;
use DuoIncure\ComplexRelics\RelicFunctions;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\item\ItemFactory;
use pocketmine\item\Item;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\utils\TextFormat as TF;
use function str_replace;
use function array_rand;

class EpicRelicFunctions {

	public const RARITY = "epic";

	/** @var Main */
	private $plugin;
	private $cfg, $relicID;

	/**
	 * EpicRelicFunctions constructor.
	 * @param Main $plugin
	 */
	public function __construct(ComplexRelics $plugin){
		$this->plugin = $plugin;
		$this->cfg = $this->plugin->getConfig()->getAll();
		$this->relicID = $this->cfg["relic-id"] ?? 399;
	}

	/**
	 * @return Item
	 */
	public function createEpicRelic(): Item{
		$relic = ItemFactory::get($this->relicID, 0, 1);
		$name = str_replace("&", "§", $this->cfg["epic"]["name"]);
		$relic->setCustomName($name);
		$lore = str_replace("&", "§", $this->cfg["epic"]["lore"]);
		$relic->setLore([$lore]);
		$nbt = $relic->getNamedTag();
		$nbt->setTag(new StringTag(RelicFunctions::RELIC_TAG, self::RARITY));
		return $relic;
	}

	/**
	 * @param Player $player
	 */
	public function sendCorrespondingMessage(Player $player){
		$msgForm = $this->cfg["message-type"] ?? "title";
		switch($msgForm){
			case "title":
				$title = str_replace("&", "§", $this->cfg["epic"]["title"]);
				$player->addTitle($title);
				break;
			case "tip":
				$tip = str_replace("&", "§", $this->cfg["epic"]["tip"]);
				$player->sendTip($tip);
				break;
			case "message":
				$message = str_replace("&", "§", $this->cfg["epic"]["message"]);
				$player->sendMessage($message);
				break;
		}
	}

	/**
	 * @param Player $player
	 * @param string $type
	 */
	public function sendCorrespondingParticles(Player $player, string $type){
		$x = $player->getX();
		$y = $player->getY();
		$z = $player->getZ();
		$pos = new Vector3($x, $y, $z);
		switch ($type){
			case "found":
				$player->getLevel()->addParticle(new HappyVillagerParticle($pos), [$player]);
				break;
			case "open":
				$player->getLevel()->addParticle(new HugeExplodeSeedParticle($pos), [$player]);
				break;
		}
	}

	/**
	 * @param Player $player
	 * @param Item $relic
	 */
	public function giveRelicToPlayer(Player $player, Item $relic){
		$playerInventory = $player->getInventory();
		$playerX = $player->getX();
		$playerY = $player->getY();
		$playerZ = $player->getZ();
		$vector3Pos = new Vector3($playerX, $playerY, $playerZ);
		if($playerInventory->canAddItem($relic)){
			$playerInventory->addItem($relic);
		} else {
			$player->getLevel()->dropItem($vector3Pos, $relic);
			$player->sendTip(TF::RED . "You found a relic but your inventory was full!");
		}
	}

	/**
	 * @param Player $player
	 */
	public function giveEpicRelic(Player $player){
		$relic = $this->createEpicRelic();
		$msgEnabled = $this->cfg["found-message-enabled"] ?? true;
		if($msgEnabled === true){
			$this->sendCorrespondingMessage($player);
		}
		$particlesEnabled = $this->cfg["particles-enabled"] ?? true;
		if($particlesEnabled === true){
			$this->sendCorrespondingParticles($player, "found");
		}
		$this->giveRelicToPlayer($player, $relic);
	}

	/**
	 * @param Player $player
	 * @param Item $relic
	 */
	public function giveEpicRelicReward(Player $player, Item $relic){
		$rewardArray = $this->cfg["epic"]["commands"];
		$chosenReward = $rewardArray[array_rand($rewardArray)];
		$commandToUse = str_replace("{player}", $player->getName(), $chosenReward);
		$relic->setCount($relic->getCount() - 1);
		$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $relic);
		$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $commandToUse);
		$particlesEnabled = $this->cfg["particles-enabled"] ?? true;
		if($particlesEnabled === true){
			$this->sendCorrespondingParticles($player, "open");
		}
	}
}