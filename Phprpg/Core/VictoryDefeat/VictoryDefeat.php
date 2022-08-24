<?php
namespace Phprpg\Core\VictoryDefeat;

class VictoryDefeat {
    //put your code here
    
    private bool $victory = false;
    private bool $defeat = false;

    
    private string $message = '';
    
    public function __construct(){
        
        
    }
    
    public function setMessage(string $message):void{
        $this->message = $message;
    }
    
    
    public function getMessage():string{
        $return = '';
        if ($this->victory){
            $return = "Victory! ".$this->message;
        } else if ($this->defeat){
            $return = "Defeat! ".$this->message;
        }
        
        
        
        return $return;
    }
    
    
    public function setVictory(bool $victory):void{
        $this->victory = $victory;
    }
    
    public function setDefeat(bool $defeat):void{
        $this->defeat = $defeat;
    }
    
    
    public function getVictory():bool{
        return $this->victory;
    }
    
    public function getDefeat():bool{
        return $this->defeat;
    }
    
    public function isConcluded():bool{
        
        if ($this->victory || $this->defeat){
            return true;
        }
        
        return false;
    }
            
}
