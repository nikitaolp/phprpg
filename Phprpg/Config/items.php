<?php

return [
    'health'=>[
        'gfx'=>'heart.png',
        'char'=>'H',
        'desc'=>'Heal up!',
        'chance'=>3,
        'desireable'=>true,
        'action'=>['maxhp'=>10,'hp'=>100]
    ],
    'spikes'=>[
        'gfx'=>'spikes.png',
        'char'=>'S',
        'desc'=>'Spike trap. Bad to step on.',
        'chance'=>1,
        'desireable'=>false,
        'action'=>['hp'=>-50]
    ],
    'gold'=>[
        'gfx'=>'gold.png',
        'char'=>'G',
        'desc'=>'some gold',
        'chance'=>4,
        'desireable'=>null,
        'action'=>['inventory'=>'1']
    ],
    'xp'=>[
        'gfx'=>'xp.png',
        'char'=>'x',
        'desc'=>'A book of knowledge',
        'chance'=>3,
        'desireable'=>true,
        'action'=>['xp'=>'150']
    ]
    
    
];