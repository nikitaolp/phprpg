<?php
namespace Phprpg\Core\Entities\Factories;
use Phprpg\Core\Entities\Player;

class PlayerFactory extends MobFactory{
    //put your code here
    
    public function fromArray(array $array):void {
        foreach ($array as $name=>$mobArray){
            $this->blueprints[$mobArray['entity_id']] = new Player(
                    $name,
                    $mobArray['gfx'],
                    $mobArray['entity_id'],
                    $mobArray['desc'],
                    $mobArray['chance'],
                    $mobArray['hp'],
                    $mobArray['dmg'],
                    $mobArray['team'],
                    $mobArray['xp_value'],
                    $mobArray['xp_to_level_up']);
        } 
        
    }
    
//    
//    public function createWithId($player_id):?GameEntity{
//        if ($mob = parent::tryToGetRandom()){
//            $this->setId($player_id);
//            return $mob;
//        } else {
//            throw new Exception("Player wasn't created, likely no fallback 100% chance player sprite in cfg");
//        }
//    }
    
}
