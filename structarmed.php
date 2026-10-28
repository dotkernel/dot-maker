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
    ->layer('BaseComponent', ['src/Component.php', 'src/Component/'], 'src/Component/Method/')
    ->layer('MethodComponent', 'src/Component/Method/')
    ->layer('FileSystem', ['src/FileSystem.php', 'src/FileSystem/'])
    ->layer('CommonType', 'src/Type/', [
        'src/Type/Form/',
        'src/Type/Handler/',
        'src/Type/Input/',
        'src/Type/InputFilter/',
    ])
    ->layer('FormType', 'src/Type/Form/')
    ->layer('HandlerType', 'src/Type/Handler/', 'src/Type/Handler/Api/')
    ->layer('ApiHandlerType', 'src/Type/Handler/Api/')
    ->layer('InputType', 'src/Type/Input/')
    ->layer('InputFilterType', 'src/Type/InputFilter/')
    ->ruleset([
        'ColorEnum'       => [],
        'VisibilityEnum'  => [],
        'Context'         => [],
        'IO'              => ['ColorEnum'],
        'Message'         => ['ColorEnum', 'Context'],
        'BaseComponent'   => ['Context', 'VisibilityEnum', 'MethodComponent'],
        'MethodComponent' => ['+BaseComponent'],
        'Exception'       => ['FileSystem'],
        'FileSystem'      => ['+BaseComponent', 'Exception'],
        'FormType'        => ['CommonType', 'Config', '+FileSystem', '+IO', '+Message'],
        'HandlerType'     => ['CommonType', 'Config', '+FileSystem', '+IO', '+Message'],
        'ApiHandlerType'  => ['+HandlerType'],
        'InputType'       => ['CommonType', 'Config', '+FileSystem', '+IO', '+Message'],
        'InputFilterType' => ['CommonType', 'Config', '+FileSystem', '+IO', '+Message'],
        'CommonType'      => ['+FormType', '+ApiHandlerType', '+InputType', '+InputFilterType'],
        'Maker'           => ['+CommonType'],
        'Config'          => ['Maker'],
    ]);
