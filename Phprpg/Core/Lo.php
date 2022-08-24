<?php

namespace Phprpg\Core;

class Lo {
    //put your code here
    static protected array $log = [];
    
    public static function g(Mixed $data = '',string $color=''):void{
        if (is_array($data) || is_object($data)){
            $store['txt'] = "<pre style='color:$color;'>".print_r($data, true)."</pre>"; 
        } else {
            $store['txt'] = "<span style='color:$color;'>$data</span>";
        }
        $store['time'] = microtime(true);
        self::$log[] = $store;
    }
    
    public static function print():void{
        echo $this->getString();
    }
    
    
    public static function getString():string{
        $output = self::$log;
        $output_str = '';
        if (!empty($output[0]['time'])){
            //array_walk($output, fn(&$x) => $x = "<p>{$x['txt']} (".$x['time']-$output[0]['time'].'s)</p>');
            //echo implode(PHP_EOL, $output); // 'a','b','c'
            
            
            
            foreach($output as $k=>$entry){
                
                $time_since_beginning = $entry['time']-$output[0]['time'];
                
                $time_since_beginning = number_format($time_since_beginning,6);
                
                $time_since_previous_entry = '0.000000';
                
                if (!empty($output[$k-1]['time'])){
                    $time_since_previous_entry = $entry['time'] - $output[$k-1]['time'];
                    $time_since_previous_entry = number_format($time_since_previous_entry,6);
                }
                
                $output_str .= "<p>{$entry['txt']} (since first log: {$time_since_beginning}s; since prev log: {$time_since_previous_entry}s)</p>";
            }
        }
        return $output_str;
        
    }
}
