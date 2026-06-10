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
            ['name' => 'Fireball', 'icon' => 'fireball.png', 'description' => 'Launch a blazing fireball, 20-30dmg, 30% to apply burn (3 turns). +10% dmg to forest enemies. Intelligence gives 5% crit chance per point!'],
            ['name' => 'Physical Attack', 'icon' => 'Strength.png', 'description' => 'Close physical attack, 10-15dmg + strength bonus. Intelligence gives 5% crit chance per point!']
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
            ['name' => 'Sword Swing', 'icon' => 'SwordSwing.png', 'description' => 'A powerful sword attack, 15-25dmg + strength bonus, 30% chance to apply bleed (2 turns). +10% dmg to city enemies. Intelligence gives 5% crit chance!'],
            ['name' => 'Leg Kick', 'icon' => 'LegKick.png', 'description' => 'A powerful kick, 10-15dmg + strength bonus, 20% chance of stunning the enemy. Intelligence gives 5% crit chance!']
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
            ['name' => 'Map Strike', 'icon' => 'map.png', 'description' => 'Attack with a map, 10-20dmg + strength bonus, 20% chance to apply bleed. +10% dmg to city enemies. Intelligence gives 5% crit chance per point!'],
            ['name' => 'Torch Jab', 'icon' => 'torch.png', 'description' => 'Hit with a torch, 15-25dmg + strength bonus, 25% chance to apply burn (3 turns). +10% dmg to forest enemies. Intelligence gives 5% crit chance per point!']
        ]
    ),
];
?>