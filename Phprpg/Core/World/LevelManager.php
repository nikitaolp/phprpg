<?php

namespace Phprpg\Core\World;
use Phprpg\Core\VictoryDefeat\VictoryDefeatManager;
use Phprpg\Core\{Lo};

/**
 * Description of Level
 *
 * @author Nikita
 */

class LevelManager {
    
    private array $levels = [];
    
    public function __construct(array $configArray, private array $defaultVictoryDefeatArray){
        
        foreach ($configArray as $levelData){
            
            if (isset($levelData['name']) && 
                !empty($levelData['entityIdArray']) &&
                isset($levelData['order']) &&
                ctype_digit((string)$levelData['order']) &&
                !empty($levelData['victoryDefeatConditions']) 
            ){
                $resettable = false;
                if (isset($levelData['resettable']) && is_bool($levelData['resettable'])){
                    $resettable = $levelData['resettable'];
                }
                
                $this->levels[$levelData['order']] = new Level(
                        $levelData['victoryDefeatConditions'],
                        $levelData['entityIdArray'],
                        $levelData['name'],
                        $levelData['order'],
                        $levelData['maxMobCount'],
                        $levelData['maxItemCount'],
                        $resettable
                );
            } else {
                throw new Exception("Level config is invalid");
            }
            
        }
        ksort($this->levels);
    }
    
    private function getFirstLevel():?Level{
        
        if ($first = reset($this->levels)){
            return $first;
        }
        return null;
        
    }
    
    
    public function getLevel(?Level $prevLevel = null):?Level{
        
        if ($prevLevel){
            if (!empty($this->levels[$prevLevel->getOrder()+1])){
                return $this->levels[$prevLevel->getOrder()+1];
            }
        } else {
            return $this->getFirstLevel();
        }

        return null;
        
    }
    
    
}
