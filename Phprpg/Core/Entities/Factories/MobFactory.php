<?php
namespace Phprpg\Core\Entities\Factories;
use Phprpg\Core\Entities\{GameEntity,Mob};

class MobFactory extends GameEntityFactory {
    //put your code here
    
    public function fromArray(array $array) {
        foreach ($array as $name=>$mobArray){
            $this->blueprints[$name] = new Mob(
                    $name,
                    $mobArray['gfx'],
                    $mobArray['char'],
                    $mobArray['desc'],
                    $mobArray['chance'],
                    $mobArray['hp'],
                    $mobArray['dmg'],
                    $mobArray['team'],
                    $mobArray['xp_value'],
                    $mobArray['xp_to_level_up']);
        } 
        
    }
    
    public function tryToGetRandom():?GameEntity{
        if ($mob = parent::tryToGetRandom()){
            $this->generateNickname($mob);
            return $mob;
        }
        return null;
    }
    
    public function generateNickname(Mob $mob):void{
        //silly thing for creating silly names
        $vowels = ["a", 'e', 'i', 'o', 'u'];
        $consonants = ["b","c","d","f","g","h","j","k","l","m","n","p","q","r","s","t","v","x","z"];
        
        $nicknameLength = rand(1,8);
        $nickname = '';
        
        if (1 == rand(1,2)){
            $nickname .= $vowels[array_rand($vowels)];
        } else {
            $nickname .= $consonants[array_rand($consonants)];
        }
        
        $prev = $nickname;
        
        for ($i=0;$i<=$nicknameLength;$i++){
            
            if (in_array($prev,$vowels)){
                $next = $consonants[array_rand($consonants)];
            } else {
                $next = $vowels[array_rand($vowels)];
            }
            $nickname .= $next;
            $prev = $next;
            
        }
        
        $mob->setNickname(ucwords($nickname).' the '.ucwords($mob->getName()));
        
    }
}