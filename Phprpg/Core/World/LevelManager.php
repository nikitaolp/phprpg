<?php

namespace Phprpg\Core\World;

/**
 * Description of Level
 *
 * @author Nikita
 */

class LevelManager {
    
    private array $levels = [];
    
    public function __construct(array $configArray){
        
        foreach ($configArray as $levelData){
            
            if (isset($levelData['name']) && 
                !empty($levelData['entityIdArray']) &&
                !empty($levelData['order']) &&
                ctype_digit($levelData['order']) &&
                !empty($levelData['victoryDefeatConditions']) 
            ){
                $this->levels[$levelData['order']] = new Level(
                        new VictoryDefeatManager($levelData['victoryDefeatConditions']),
                        $levelData['entityIdArray'],
                        $levelData['name'],
                        $levelData['order']
                );
            } else {
                throw new Exception("Level config is invalid");
            }
            
        }
        ksort($this->levels);
    }
    
    public function nextLevel(Level $level):?Level{
        if (!empty($this->levels[$level->getOrder()])){
            return $this->levels[$level->getOrder()];
        }
        return null;
    }
    
    
}
