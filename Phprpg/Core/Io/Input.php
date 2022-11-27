<?php
namespace Phprpg\Core\Io;

class Input {
    //put your code here
    
    private array $directions = ['north','south','east','west'];
    private array $actions = ['move','skip','check','reset'];
    
    private ?string $action = null;
    
    private ?string $direction = null;
    
    public function __construct($post){
        if (!empty($post['action']) && in_array($post['action'], $this->actions)){
            
            $this->action = $post['action'];
            
            if ($post['action'] == 'move' && !empty($post['direction']) && in_array($post['direction'],$this->directions)){
                $this->direction = $post['direction'];
            }
            
            
        }
    }
    
    public function getDirection():?string{
        return $this->direction;
    }
    
    public function getAction():?string{
        return $this->action;
    }
    

}
