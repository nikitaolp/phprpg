<?php
return [
        'type'=>'warehouse',
        'player_limit'=>1,
        'height'=>70,
        'width'=>70,
        'mob_sight'=>3,
        'mob_priorities'=>[
            'escape'=>1,
            'pickup_desireable'=>1,
            'attack'=>1,
            'avoid_undesireable'=>1
            
        ],
        'turn_timeout'=>30,
        'print_radius'=>7,
        'zoom_in_radius'=>3,
        'index_location'=>'index_warehouse.html'
    ];