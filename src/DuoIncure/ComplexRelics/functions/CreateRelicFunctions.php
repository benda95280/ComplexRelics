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

class CreateRelicFunctions {

	/**
	 * @return Item
	 */
	public static function createRelic(player $player, $rarity): Item{
		if (in_array($rarity, ComplexRelics::$relicList)) {
			$relic = ItemFactory::get(ComplexRelics::$cfg["relic-id"], 0, 1);
			$name = str_replace("&", "§", ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["relic"][$rarity]["name"]);
			$relic->setCustomName($name);
			$hint = str_replace("&", "§", ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["main"]["loreInstruction"]);
			$lore = str_replace("&", "§", ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["lore"][$rarity][mt_rand(0, count(ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["lore"][$rarity]) - 1)]);
			$relic->setLore([$lore,$hint]);
			$nbt = $relic->getNamedTag();
			$nbt->setTag(new StringTag(RelicFunctions::RELIC_TAG, $rarity));
			if (!ComplexRelics::$cfg["can-be-stacked"]) $nbt->setTag(new StringTag("UnStacker", substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(30/strlen($x)) )),1,30)));
			return $relic;
		}
		else {
			#Return error item
			$relic = ItemFactory::get(ComplexRelics::$cfg["relic-id"], 0, 1);
			$name = "ERROR";
			$relic->setCustomName($name);
			$relic->setLore(["Contact Admin"]);
			return $relic;
		}
	}

	/**
	 * @param Player $player
	 */
	public static function sendCorrespondingMessage(Player $player, $rarity){
		$msgForm = ComplexRelics::$cfg["message-type"] ?? "title";
		switch($msgForm){
			case "title":
				$title = str_replace("&", "§", ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["relic"][$rarity]["title"]);
				$player->addTitle($title);
				break;
			case "tip":
				$tip = str_replace("&", "§", ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["relic"][$rarity]["tip"]);
				$player->sendTip($tip);
				break;
			case "message":
				$message = str_replace("&", "§", ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["relic"][$rarity]["message"]);
				$player->sendMessage($message);
				break;
		}
	}

	/**
	 * @param Player $player
	 * @param string $type
	 */
	public static function sendCorrespondingParticles(Player $player, string $type){
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
	public static function giveRelicToPlayer(Player $player, Item $relic){
		$playerInventory = $player->getInventory();
		$playerX = $player->getX();
		$playerY = $player->getY();
		$playerZ = $player->getZ();
		$vector3Pos = new Vector3($playerX, $playerY, $playerZ);
		if($playerInventory->canAddItem($relic)){
			$playerInventory->addItem($relic);
		} else {
			$player->getLevel()->dropItem($vector3Pos, $relic);
			$player->sendTip(TF::RED . ComplexRelics::$lang[ComplexRelics::getPlayerLanguage($player)]["main"]["inventoryFull"]);
		}
	}

	/**
	 * @param Player $player
	 * @param String $rarity
	 */
	public static function giveRelic(Player $player, $rarity){
		$relic = CreateRelicFunctions::createRelic($player, $rarity);
		$msgEnabled = ComplexRelics::$cfg["found-message-enabled"] ?? true;
		if($msgEnabled === true){
			CreateRelicFunctions::sendCorrespondingMessage($player,$rarity);
		}
		$particlesEnabled = ComplexRelics::$cfg["particles-enabled"] ?? true;
		if($particlesEnabled === true){
			CreateRelicFunctions::sendCorrespondingParticles($player, "found");
		}
		CreateRelicFunctions::giveRelicToPlayer($player, $relic);
	}

	/**
	 * @param Player $player
	 * @param Item $relic
	 * @param String $rarity
	 */
	public static function giveRelicReward(Player $player, Item $relic, $rarity){
		$rewardArray = ComplexRelics::$cfg["relic-list"][$rarity]["commands"];
		$chosenReward = $rewardArray[array_rand($rewardArray)];
		$particlesEnabled = ComplexRelics::$cfg["particles-enabled"] ?? true;
		$commandToUse = str_replace("{player}", $player->getName(), $chosenReward);
		
		$relic->setCount($relic->getCount() - 1);
		$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $relic);
		ComplexRelics::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), $commandToUse);
		
		if($particlesEnabled === true){
			CreateRelicFunctions::sendCorrespondingParticles($player, "open");
		}
	}
}