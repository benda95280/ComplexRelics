<?php
declare(strict_types=1);
// namespace MyPlot\provider;
namespace MiningRelics\functions;

// use onebone\economyapi\EconomyAPI;
// use pocketmine\Player;
use DaPigGuy\PiggyCustomEnchants\{CustomEnchantManager,PiggyCustomEnchants,utils\Utils};
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use MiningRelics\MiningRelics;

// implements EconomyProvider
class CreateItemPiggy
{
	/** @var EconomyAPI $plugin */
	// private $plugin;
	public $PiggyCE_List = [];
	public $PiggyCE_Level = [];
	/**
	 * EconomySProvider constructor.
	 *
	 * @param EconomyAPI $plugin
	 */
	// public function __construct(EconomyAPI $plugin) {
		// $this->plugin = $plugin;
	// }
	
	//Rarity -> "Uncommon" "Rare" "Mythic" "Common"
	//itemsEnchantable[level][$enchantRarity][itemsID][enchantID] = MaxLevel
	public function getRandomPiggyRelic(int $level, int $maxlevelEnchant) {
		
		if (isset($this->PiggyCE_Level[$level])) {
			$arrayOfItemsPiggyCERarity = $this->PiggyCE_List[$level];
			//$arrayOfItemsPggyCERarity[10] = common / 1 = mythic
			
			$chance = mt_rand(1, max(array_keys($arrayOfItemsPiggyCERarity)));
			$previousChance = 0;
			
			//Roll over rarity inside the level choosen
			foreach ($arrayOfItemsPiggyCERarity as $key => $value) {
				$ItemsPiggyCERarity = $key;
				$ItemsArrayID = $value;
				if ($chance <= $ItemsPiggyCERarity) {
					//$ItemsArrayID
					$randedItemID = array_rand($ItemsArrayID);
					$randedEnchantID = array_rand($ItemsArrayID[$randedItemID]);
					$enchantMaxLevel = $ItemsArrayID[$randedItemID][$randedEnchantID];
					$enchantLevel = mt_rand(1,$enchantMaxLevel);
					if ($enchantLevel > $maxlevelEnchant) $enchantLevel = $maxlevelEnchant;
					break;
				}
				else $previousChance = $ItemsPiggyCERarity;
			}
			
			$item = Item::get($randedItemID);
			$item->addEnchantment(new EnchantmentInstance(CustomEnchantManager::getEnchantment($randedEnchantID), $enchantLevel));
			return $item;
		}
		else return null;
	}
	public function initReclicPiggy() {
		$itemArray = [];
		$itemArray[0] = array(268, 269, 270, 271, 290, 298, 299, 300, 301);
		$itemArray[1] = array(283, 284, 285, 286, 294, 314, 315, 316, 317, 261);
		$itemArray[2] = array(272, 273, 274, 275, 291, 302, 303, 304, 305);
		$itemArray[3] = array(306, 307, 308, 309, 256, 257, 258, 267, 292, 444);
		$itemArray[4] = array(276, 277, 278 ,279, 293, 310, 311, 312, 313, 455);
		$excluedItem = array(340, 359, 346, 345, 259);

		$listenchant = array_keys(CustomEnchantManager::getEnchantments());
		$itemsEnchantable = [];
		$itemsNotFound = [];
		//itemsEnchantable[level][$enchantRarity][itemsID][enchantID] = MaxLevel
		//Loop over items
		for ($k = 256 ; $k <= 737; $k++){
			$item = Item::get($k);
			$itemID = $item->getID();
			$itemName = $item->getName();
			if ($itemName != "Unknown" && !in_array($itemID, $excluedItem) ) {
				//var_dump($item);
				//($item,1,1)
				foreach ($listenchant as $valueEnchant){
					$createdEnchant = CustomEnchantManager::getEnchantment($valueEnchant);
					if (Utils::canBeEnchanted($item,	$createdEnchant	,1)) {
						$enchantName = $createdEnchant->name;
						if ($enchantName != "Soulbound" && $enchantName != "Lucky Charm" ) {
							$enchantRarity = $createdEnchant->rarity; //Utils::RARITY_NAMES[]
							$enchantMaxLevel = $createdEnchant->maxLevel;
							$i = 0;
							$categoryFound = false;
							foreach ($itemArray as $itemArrayValue) {
								if (in_array($itemID ,$itemArrayValue)) {
									if (!isset($itemsEnchantable[$i][$enchantRarity])) $itemsEnchantable[$i][$enchantRarity] = [];
									if (!isset($itemsEnchantable[$i][$enchantRarity][$itemID])) $itemsEnchantable[$i][$enchantRarity][$itemID] = [];
									$itemsEnchantable[$i][$enchantRarity][$itemID][$valueEnchant] = $enchantMaxLevel;
									$categoryFound = true;
								}
								$i++;
							}
							if ($categoryFound === false) {
								$itemsNotFound[$itemID] = $itemName;
							}
						}
					}
				}
			}
		}
		foreach ($itemsNotFound as $itemsNotFoundKey => $itemsNotFoundValue){
			MiningRelics::getInstance()->getLogger()->warning("MinningRelics: PiggyCE missing ID item declaration for $itemsNotFoundKey: $itemsNotFoundValue");
		}
		$this->PiggyCE_List = $itemsEnchantable;
		$this->PiggyCE_Level = array_keys($itemsEnchantable);
		// var_dump(array_keys($itemsEnchantable[0]));
		// var_dump(Utils::RARITY_NAMES[10]);
		
	}
	
}