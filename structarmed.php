<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())

    ->layer('ColorEnum', 'src/ColorEnum.php')
    ->layer('VisibilityEnum', 'src/VisibilityEnum.php')
    ->layer('Context', 'src/Context.php')
    ->layer('Config', 'src/Config.php')
    ->layer('Message', 'src/Message.php')
    ->layer('Maker', 'src/Maker.php')
    ->layer('IO', 'src/IO/')
    ->layer('Exception', 'src/Exception/')
    ->layer('Component', ['src/Component.php', 'src/Component/'], 'src/Component/Method/')
    ->layer('Method', 'src/Component/Method/')
    ->layer('FileSystem', ['src/FileSystem.php', 'src/FileSystem/'])
    ->layer('Type', 'src/Type/', [
        'src/Type/Form/',
        'src/Type/Handler/',
        'src/Type/Input/',
        'src/Type/InputFilter/',
    ])
    ->layer('Form', 'src/Type/Form/')
    ->layer('Handler', 'src/Type/Handler/', 'src/Type/Handler/Api/')
    ->layer('ApiHandler', 'src/Type/Handler/Api/')
    ->layer('Input', 'src/Type/Input/')
    ->layer('InputFilter', 'src/Type/InputFilter/')
    ->ruleset([
        'ColorEnum'      => [],
        'VisibilityEnum' => [],
        'Context'        => [],
        'IO'             => ['ColorEnum'],
        'Message'        => ['ColorEnum', 'Context'],
        'Component'      => ['Context', 'VisibilityEnum', 'Method'],
        'Method'         => ['+Component'],
        'Exception'      => ['FileSystem'],
        'FileSystem'     => ['+Component', 'Exception'],
        'Form'           => ['Type', 'Config', '+FileSystem', '+IO', '+Message'],
        'Handler'        => ['Type', 'Config', '+FileSystem', '+IO', '+Message'],
        'ApiHandler'     => ['+Handler'],
        'Input'          => ['Type', 'Config', '+FileSystem', '+IO', '+Message'],
        'InputFilter'    => ['Type', 'Config', '+FileSystem', '+IO', '+Message'],
        'Type'           => ['+Form', '+ApiHandler', '+Input', '+InputFilter'],
        'Maker'          => ['+Type'],
        'Config'         => ['Maker'],
    ]);
