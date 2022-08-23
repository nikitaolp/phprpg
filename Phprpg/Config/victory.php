<?php

return [
    'victory' => [
        'Phprpg\Core\Entities\Player'=>[
            'stats'=>['level'=>10],
            'inventory'=>['gold'=>5],
            'coordinates'=>[1,1]
            
            ]
        ],
    'defeat' => [
        'Phprpg\Core\Entities\Mob' =>[
            'stats'=>['level'=>10],
            'inventory'=>['gold'=>15]
            ],
        'players' => [
            'all_dead'=>true
        ]
    ]
];
