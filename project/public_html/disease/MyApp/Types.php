<?php
	namespace MyApp;
	
	class Types {

		const Messages_HELLO 		= 0;
		const Messages_WELCOME 		= 1;
		const Messages_SPAWN 		= 2;
		const Messages_DESPAWN 		= 3;
		const Messages_MOVE 		= 4;
		const Messages_LOOTMOVE 	= 5;
		const Messages_AGGRO 		= 6;
		const Messages_ATTACK 		= 7;
		const Messages_HIT 			= 8;
		const Messages_HURT 		= 9;
		const Messages_HEALTH 		= 10;
		const Messages_CHAT 		= 11;
		const Messages_LOOT 		= 12;
		const Messages_EQUIP 		= 13;
		const Messages_DROP 		= 14;
		const Messages_TELEPORT 	= 15;
		const Messages_DAMAGE 		= 16;
		const Messages_POPULATION 	= 17;
		const Messages_KILL 		= 18;
		const Messages_LIST 		= 19;
		const Messages_WHO 			= 20;
		const Messages_ZONE 		= 21;
		const Messages_DESTROY 		= 22;
		const Messages_HP 			= 23;
		const Messages_BLINK 		= 24;
		const Messages_OPEN 		= 25;
		const Messages_CHECK 		= 26;
		const Messages_BITE			= 27;
		const Messages_INFECT		= 28;
		const Messages_EXPOSED		= 29;
		const Messages_RECOVERED	= 30;
		//Player
		const Entities_WARRIOR		= 1;
		// Mobs
		const Entities_RAT			= 2;
		const Entities_SKELETON		= 3;
		const Entities_GOBLIN		= 4;
		const Entities_OGRE			= 5;
		const Entities_SPECTRE		= 6;
		const Entities_CRAB			= 7;
		const Entities_BAT			= 8;
		const Entities_WIZARD		= 9;
		const Entities_EYE			= 10;
		const Entities_SNAKE		= 11;
		const Entities_SKELETON2	= 12;
		const Entities_BOSS			= 13;
		const Entities_DEATHKNIGHT	= 14;
		// Armors
		const Entities_FIREFOX		= 20;
		const Entities_CLOTHARMOR	= 21;
		const Entities_LEATHERARMOR	= 22;
		const Entities_MAILARMOR	= 23;
		const Entities_PLATEARMOR	= 24;
		const Entities_REDARMOR		= 25;
		const Entities_GOLDENARMOR	= 26;
		// Objects
		const Entities_FLASK		= 35;
		const Entities_BURGER		= 36;
		const Entities_CHEST		= 37;
		const Entities_FIREPOTION	= 38;
		const Entities_CAKE			= 39;
		// NPCs
		const Entities_GUARD		= 40;
		const Entities_KING			= 41;
		const Entities_OCTOCAT		= 42;
		const Entities_VILLAGEGIRL	= 43;
		const Entities_VILLAGER		= 44;
		const Entities_PRIEST		= 45;
		const Entities_SCIENTIST	= 46;
		const Entities_AGENT		= 47;
		const Entities_RICK			= 48;
		const Entities_NYAN			= 49;
		const Entities_SORCERER		= 50;
		const Entities_BEACHNPC		= 51;
		const Entities_FORESTNPC	= 52;
		const Entities_DESERTNPC	= 53;
		const Entities_LAVANPC		= 54;
		const Entities_CODER		= 55;
		// Weapons
		const Entities_SWORD1		= 60;
		const Entities_SWORD2		= 61;
		const Entities_REDSWORD		= 62;
		const Entities_GOLDENSWORD	= 63;
		const Entities_MORNINGSTAR	= 64;
		const Entities_AXE			= 65;
		const Entities_BLUESWORD	= 66;
    
		const Orientations_UP		= 1;
		const Orientations_DOWN		= 2;
		const Orientations_LEFT		= 3;
		const Orientations_RIGHT	= 4;
		
		const DiseaseState_MOTHER = 0;
		const DiseaseState_SUSCEPTIBLE = 1;
		const DiseaseState_EXPOSED = 2;
		const DiseaseState_INFECTED = 3;
		const DiseaseState_RECOVERED = 4;
		
		
		const Disease_Dengue = 0;
		const Disease_Chikungunya = 1;
		const Disease_Lepto = 2;
		
		public static $kinds = array(
			'warrior' => array(self::Entities_WARRIOR, 'player'),
			'rat' => array(self::Entities_RAT, "mob"),
			'skeleton' => array(self::Entities_SKELETON , "mob"),
			'goblin' => array(self::Entities_GOBLIN, "mob"),
			'ogre' => array(self::Entities_OGRE, "mob"),
			'spectre' => array(self::Entities_SPECTRE, "mob"),
			'deathknight' => array(self::Entities_DEATHKNIGHT, "mob"),
			'crab' => array(self::Entities_CRAB, "mob"),
			'snake' => array(self::Entities_SNAKE, "mob"),
			'bat' => array(self::Entities_BAT, "mob"),
			'wizard' => array(self::Entities_WIZARD, "mob"),
			'eye' => array(self::Entities_EYE, "mob"),
			'skeleton2' => array(self::Entities_SKELETON2, "mob"),
			'boss' => array(self::Entities_BOSS, "mob"),

			'sword1' => array(self::Entities_SWORD1, "weapon"),
			'sword2' => array(self::Entities_SWORD2, "weapon"),
			'axe' => array(self::Entities_AXE, "weapon"),
			'redsword' => array(self::Entities_REDSWORD, "weapon"),
			'bluesword' => array(self::Entities_BLUESWORD, "weapon"),
			'goldensword' => array(self::Entities_GOLDENSWORD, "weapon"),
			'morningstar' => array(self::Entities_MORNINGSTAR, "weapon"),
			
			'firefox' => array(self::Entities_FIREFOX, "armor"),
			'clotharmor' => array(self::Entities_CLOTHARMOR, "armor"),
			'leatherarmor' => array(self::Entities_LEATHERARMOR, "armor"),
			'mailarmor' => array(self::Entities_MAILARMOR, "armor"),
			'platearmor' => array(self::Entities_PLATEARMOR, "armor"),
			'redarmor' => array(self::Entities_REDARMOR, "armor"),
			'goldenarmor' => array(self::Entities_GOLDENARMOR, "armor"),

			'flask' => array(self::Entities_FLASK, "object"),
			'cake' => array(self::Entities_CAKE, "object"),
			'burger' => array(self::Entities_BURGER, "object"),
			'chest' => array(self::Entities_CHEST, "object"),
			'firepotion' => array(self::Entities_FIREPOTION, "object"),

			'guard' => array(self::Entities_GUARD, "npc"),
			'villagegirl' => array(self::Entities_VILLAGEGIRL, "npc"),
			'villager' => array(self::Entities_VILLAGER, "npc"),
			'coder' => array(self::Entities_CODER, "npc"),
			'scientist' => array(self::Entities_SCIENTIST, "npc"),
			'priest' => array(self::Entities_PRIEST, "npc"),
			'king' => array(self::Entities_KING, "npc"),
			'rick' => array(self::Entities_RICK, "npc"),
			'nyan' => array(self::Entities_NYAN, "npc"),
			'sorcerer' => array(self::Entities_SORCERER, "npc"),
			'agent' => array(self::Entities_AGENT, "npc"),
			'octocat' => array(self::Entities_OCTOCAT, "npc"),
			'beachnpc' => array(self::Entities_BEACHNPC, "npc"),
			'forestnpc' => array(self::Entities_FORESTNPC, "npc"),
			'desertnpc' => array(self::Entities_DESERTNPC, "npc"),
			'lavanpc' => array(self::Entities_LAVANPC, "npc"),
		);
	    		
		public static function getType($kind) {
			return self::$kinds[self::getKindAsString($kind)][1];
		}
		
		public static $rankedWeapons = array(
			self::Entities_SWORD1,
			self::Entities_SWORD2,
			self::Entities_AXE,
			self::Entities_MORNINGSTAR,
			self::Entities_BLUESWORD,
			self::Entities_REDSWORD,
			self::Entities_GOLDENSWORD
		);
		
		public static $rankedArmors = array(
			self::Entities_CLOTHARMOR,
			self::Entities_LEATHERARMOR,
			self::Entities_MAILARMOR,
			self::Entities_PLATEARMOR,
			self::Entities_REDARMOR,
			self::Entities_GOLDENARMOR
		);
		//SIR MODEL
		public static $SIR = array(
			self::DiseaseState_SUSCEPTIBLE,
			self::DiseaseState_INFECTED,
			self::DiseaseState_RECOVERED
		);
		//SEIR MODEL
		public static $SEIR = array(
			self::DiseaseState_SUSCEPTIBLE,
			self::DiseaseState_EXPOSED,
			self::DiseaseState_INFECTED,
			self::DiseaseState_RECOVERED
		);
		public static function getWeaponRank($weaponKind) {
			return array_search($weaponKind, self::$rankedWeapons);
		}
		
		public static function getArmorRank($armorKind) {
			return array_search($armorKind, self::$rankedArmors);
		}
		
		public static function isPlayer($kind) {
			return (self::getType($kind) === 'player');
		}
		
		public static function isMob($kind) {
			return (self::getType($kind) === 'mob');
		}
		
		public static function isNPC($kind) {
			return (self::getType($kind) === 'npc');
		}
		
		public static function isCharacter($kind) {
			return ( self::isMob($kind) || self::isNPC($kind) || self::isPlayer($kind) );
		}
		
		public static function isArmor($kind) {
			return (self::getType($kind) === 'armor');
		}
		
		public static function isWeapon($kind) {
			return (self::getType($kind) === 'weapon');
		}
		
		public static function isObject($kind) {
			return (self::getType($kind) === 'object');
		}
		
		public static function isChest($kind) {
			return ($kind === self::Entities_CHEST);
		}
		
		public static function isItem($kind) {
			return ( self::isWeapon($kind) || self::isArmor($kind) || ( self::isObject($kind) && !self::isChest($kind) ) );
		}
		
		public static function isHealingItem($kind) {
			return ($kind === self::Entities_FLASK || $kind === self::Entities_BURGER);
		}
		
		public static function isExpendableItem($kind) {
			return (self::isHealingItem($kind) || $kind === self::Entities_FIREPOTION || $kind === self::Entities_CAKE);
		}
		
		public static function getKindFromString($kind) {
			if ( array_key_exists($kind, self::$kinds) ) {
				return self::$kinds[$kind][0];
			}
		}
		
		public static function getKindAsString($type) {
			foreach (self::$kinds as $kindString => $kind) {
				if ($kind[0] === $type) {
					return $kindString;
				}
			}
		}
		

		// These are the Message Types received by the server which will need Message Handlers created for them.
        public static function messageHandlers ($actionID){	
		switch ($actionID){
			case 0:
				return 'Hello';
			case 4:
				return 'Move';
			case 5:
				return 'LootMove';
			case 6:
				return 'Aggro';				
			case 7:
				return 'Attack';
			case 8:
				return 'Hit';	
			case 9:
				return 'Hurt';	
			case 11:
				return 'Chat';
			case 12:
				return 'Loot';	
			case 15:
				return 'Teleport';
			case 20:
				return 'Who';
			case 21:
				return 'Zone';	
			case 25:
				return 'Open';
			case 26:
				return 'Check';		
			case 27:
				return 'Bite';
			case 28:
				return 'Infect';
			case 29:
				return 'Exposed';
			case 30:
				return 'Recover';
			default:
				echo " Action ID not found $actionID";
				}
        }	

				public static function getOrientationAsString($orientation) {
    switch($orientation) {
        case Types.Orientations.LEFT: return "left"; break;
        case Types.Orientations.RIGHT: return "right"; break;
        case Types.Orientations.UP: return "up"; break;
        case Types.Orientations.DOWN: return "down"; break;
			}
		}
	//}
	
};
	
/*

Types.forEachKind = function(callback) {
    for(var k in kinds) {
        callback(kinds[k][0], k);
    }
};

Types.forEachArmor = function(callback) {
    Types.forEachKind(function(kind, kindName) {
        if(Types.isArmor(kind)) {
            callback(kind, kindName);
        }
    });
};

Types.forEachMobOrNpcKind = function(callback) {
    Types.forEachKind(function(kind, kindName) {
        if(Types.isMob(kind) || Types.isNpc(kind)) {
            callback(kind, kindName);
        }
    });
};

Types.forEachArmorKind = function(callback) {
    Types.forEachKind(function(kind, kindName) {
        if(Types.isArmor(kind)) {
            callback(kind, kindName);
        }
    });
};

Types.getOrientationAsString = function(orientation) {
    switch(orientation) {
        case Types.Orientations.LEFT: return "left"; break;
        case Types.Orientations.RIGHT: return "right"; break;
        case Types.Orientations.UP: return "up"; break;
        case Types.Orientations.DOWN: return "down"; break;
    }
};

Types.getRandomItemKind = function(item) {
    var all = _.union(this.rankedWeapons, this.rankedArmors),
        forbidden = [Types.Entities.SWORD1, Types.Entities.CLOTHARMOR],
        itemKinds = _.difference(all, forbidden),
        i = Math.floor(Math.random() * _.size(itemKinds));
    
    return itemKinds[i];
};

Types.getMessageTypeAsString = function(type) {
    var typeName;
    _.each(Types.Messages, function(value, name) {
        if(value === type) {
            typeName = name;
        }
    });
    if(!typeName) {
        typeName = "UNKNOWN";
    }
    return typeName;
};

if(!(typeof exports === 'undefined')) {
    module.exports = Types;
}
*/
