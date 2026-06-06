<?php 
session_start();
class Character {
    public $name;
    public $role;
    public $health;
    public $strength;
    public $stamina;
    public $intelligence;
    public $spriteImage;
    public $spriteClass;
    public $spriteOffsetX;
    public $spriteOffsetY;
    public $spriteFrameSize;
    public $stats;
    public $attacks;

    public function __construct($name, $role, $health, $strength, $stamina, $intelligence, $spriteImage, $spriteClass, $spriteOffsetX, $spriteOffsetY, $spriteFrameSize, array $attacks)
    {
        $this->name = $name;
        $this->role = $role;
        $this->health = $health;
        $this->strength = $strength;
        $this->stamina = $stamina;
        $this->intelligence = $intelligence;
        $this->spriteImage = $spriteImage;
        $this->spriteClass = $spriteClass;
        $this->spriteOffsetX = $spriteOffsetX;
        $this->spriteOffsetY = $spriteOffsetY;
        $this->spriteFrameSize = $spriteFrameSize;
        $this->stats = [
            'hp' => $health,
            'intelligence' => $intelligence,
            'strength' => $strength,
            'stamina' => $stamina,
        ];
        $this->attacks = $attacks;
    }
}











$characters = [
    'mage' => new Character(
        'Mage',
        'Arcane Caster',
        80,
        5,
        70,
        9,
        'Mage.png',
        'mage-idle',
        '0px',
        '0px',
        64,
        [
            ['name' => 'Fireball', 'icon' => 'fireball.png', 'description' => 'Launch a blazing fireball, 20-30dmg, 30% to apply burn, +10% dmg to forest enemies .'],
            ['name' => 'Physical Attack', 'icon' => 'Strength.png', 'description' => 'Close physical attack, 10dmg .']
        ]
    ),
    'knight' => new Character(
        'Knight',
        'Heavy Warrior',
        90,
        8,
        80,
        6,
        'knight.png',
        'knight-idle',
        '-100px',
        '-96px',
        32,
        [
            ['name' => 'Sword Swing', 'icon' => 'SwordSwing.png', 'description' => 'A sword attack, 20dmg, 30% of applying bleed, +10% dmg to city enemies .'],
            ['name' => 'Leg Kick', 'icon' => 'LegKick.png', 'description' => 'A powerful kick which has 20% chance of stunning the enemy 10-15dmg.']
        ]
    ),
    'traveler' => new Character(
        'Traveler',
        'Wandering Scout',
        70,
        6,
        100,
        7,
        'traveler.png',
        'traveler-idle',
        '0px',
        '0px',
        64,
        [
            ['name' => 'Map Strike', 'icon' => 'map.png', 'description' => 'Attack An enemy with a map , 10-20dmg, 20% chance of applying bleed, +10% dmg to city enemies .'],
            ['name' => 'Torch Jab', 'icon' => 'torch.png', 'description' => 'Hit a enemy with a torch, 15-25dmg, 25% chance of applying burn, +10% dmg to forest enemies .']
        ]
    ),
];

?>
