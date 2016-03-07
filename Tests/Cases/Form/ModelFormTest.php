<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 22/10/14.10.2014 16:34
 */

namespace Mindy\Form\Tests;

use Exception;
use Mindy\Form\ModelForm;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Model;
use Mindy\Orm\Sync;
use Mindy\Query\ConnectionManager;

class Game extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'required' => true
            ],
            'patches' => [
                'class' => HasManyField::className(),
                'modelClass' => Patch::className(),
                'editable' => false
            ],
        ];
    }
}

class Patch extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'required' => true
            ],
            'game' => [
                'class' => ForeignField::className(),
                'modelClass' => Game::className(),
                'relatedName' => 'patches'
            ],
            'file' => [
                'class' => FileField::className(),
                'null' => true
            ]
        ];
    }
}

abstract class TestModelForm extends ModelForm
{
    public $templates = [
        'block' => '../../Templates/block.php',
        'table' => '../../Templates/table.php',
        'ul' => '../../Templates/ul.php',
    ];

    public function getTemplateFromType($type)
    {
        if (array_key_exists($type, $this->templates)) {
            $template = $this->templates[$type];
        } else {
            throw new Exception("Template type {$type} not found");
        }
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($template, DIRECTORY_SEPARATOR));
    }

    public function renderInternal($view, array $data = [])
    {
        ob_start();
        extract($data);
        include($view);
        return ob_get_clean();
    }
}

class GameForm extends TestModelForm
{
    public function getModel()
    {
        return new Game;
    }

    public function getInlines()
    {
        return [
            ['game' => PatchForm::className()],
        ];
    }
}

class PatchForm extends TestModelForm
{
    public function getModel()
    {
        return new Patch;
    }
}

class ModelFormTest extends TestCase
{
    public function setUp()
    {
        $db = new ConnectionManager([
            'databases' => [
                'default' => [
                    'class' => '\Mindy\Query\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ]);

        $sync = new Sync([new Game, new Patch]);
        $sync->create();
    }

    public function renew()
    {
        $this->tearDown();
        $this->setUp();
    }

    public function tearDown()
    {
        $sync = new Sync([new Game, new Patch]);
        $sync->delete();
    }

    public function testInit()
    {
        $this->assertEquals(0, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $game = new Game();
        $this->assertEquals(3, count($game->getFieldsInit()));
        $patch = new Patch();
        $this->assertEquals(4, count($patch->getFieldsInit()));

        $gameForm = new GameForm();
        $this->assertEquals(1, count($gameForm->getFieldsInit()));
        $patchForm = new PatchForm();
        $this->assertEquals(3, count($patchForm->getFieldsInit()));
    }

    public function testModelForm()
    {
        $form = new GameForm();
        $this->assertEquals(1, count($form->getFieldsInit()));
        $this->assertEquals(implode("\n", [
            "<label for='GameForm_name'>Name</label>",
            "<input type='text' value='' id='GameForm_name' name='GameForm[name]'/>",
            "",
            "<ul class='error' id='GameForm_name_errors' style='display:none;'></ul>",
            "<h2>PatchForm</h2>",
            "<label for='GameForm_PatchForm_0_name'>Name</label>",
            "<input type='text' value='' id='GameForm_PatchForm_0_name' name='GameForm[PatchForm][0][name]'/>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0_name_errors' style='display:none;'></ul>",
//            "<input type='hidden' value='' name='GameForm[PatchForm][0][game]' />",
//            "<label for='GameForm_PatchForm_0_game'>Game</label>",
//            "<span class='select-holder'><select id='GameForm_PatchForm_0_game' name='GameForm[PatchForm][0][game]' ></select></span>",
//            "",
//            "<ul class='error' id='GameForm_PatchForm_0_game_errors' style='display:none;'></ul>",
            "<label for='GameForm_PatchForm_0_file'>File</label>",
            "<input type='file' id='GameForm_PatchForm_0_file' name='GameForm[PatchForm][0][file]'/>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0_file_errors' style='display:none;'></ul>",
            "",
            "<input type='hidden' value='' id='GameForm_PatchForm_0__pk' name='GameForm[PatchForm][0][_pk]' class='_pk'/>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0__pk_errors' style='display:none;'></ul>",
            "",
            "<input type='hidden' value='' id='GameForm_PatchForm_0__changed' name='GameForm[PatchForm][0][_changed]' class='_changed'/>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0__changed_errors' style='display:none;'></ul>",
            "<input type='hidden' value='' name='GameForm[PatchForm][0][_delete]' />",
            "<input type='checkbox' id='GameForm_PatchForm_0__delete' value='1' name='GameForm[PatchForm][0][_delete]' class='_delete'/>",
            "<label for='GameForm_PatchForm_0__delete'>Delete</label>",
            "",
            "<ul class='error' id='GameForm_PatchForm_0__delete_errors' style='display:none;'></ul>",
            "",
        ]), $form->asBlock());

        $form->setAttributes([
            'name' => 'test'
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(0, count($form->getInlinesCreate()));
        $this->assertEquals(0, count($form->getInlinesDelete()));
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);

        $this->renew();

        $form = new GameForm();
        $form->setAttributes([
            'name' => 'test',
            'PatchForm' => [
                ['name' => 'Summer update']
            ]
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(1, count($form->getInlinesCreate()));
        $this->assertEquals(0, count($form->getInlinesDelete()));
        $form->isValid();
        // $this->assertTrue($form->isValid());
        $this->assertEquals([], $form->getErrors());
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);
    }

    public function testUpdateInline()
    {
        $this->assertEquals(0, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $model = Game::objects()->getOrCreate(['name' => 'foo']);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $form = new GameForm(['instance' => $model]);
        $this->assertEquals(1, count($form->getFieldsInit()));
        $form->setAttributes([
            'name' => 'test',
            'PatchForm' => [
                ['name' => 'Winter update']
            ]
        ]);
        $this->assertEquals(1, count($form->getFieldsInit()));
        $this->assertEquals(['name' => 'test'], $form->getAttributes());

        $createInlines = $form->getInlinesCreate();
        $this->assertEquals(1, count($createInlines));

        $patchForm = $createInlines[0];
        $valid = $patchForm->isValid();
        $this->assertEquals([], $patchForm->getErrors());
        $this->assertTrue($valid);

        $this->assertEquals(0, count($form->getInlinesDelete()));

        $valid = $form->isValid();
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($valid);
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($form->save());

        $patch = Patch::objects()->get();
        $this->assertEquals('Winter update', $patch->name);

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(1, Patch::objects()->count());
        $this->assertEquals(1, $instance->patches->count());
    }

    public function testDeleteInline()
    {
        $model = Game::objects()->getOrCreate(['name' => 'foo']);
        $form = new GameForm(['instance' => $model]);
        $form->setAttributes([
            'name' => 'test',
            'PatchForm' => [
                ['name' => 'Winter update', '_delete' => 1]
            ]
        ]);
        $this->assertEquals(['name' => 'test'], $form->getAttributes());
        $this->assertEquals(0, count($form->getInlinesCreate()));
        $this->assertEquals(1, count($form->getInlinesDelete()));
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, $instance->pk);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());
        $this->assertEquals(0, $instance->patches->count());
    }

    public function testPopulate()
    {
        $form = new GameForm();
        $get = [
            'GameForm' => [
                'name' => 'test',
                'PatchForm' => [
                    ['name' => 'Winter update']
                ]
            ]
        ];
        $form->populate($get);

        $patchForm = $form->getInlinesCreate()[0];
        $this->assertEquals('GameForm', $patchForm->getPrefix());
        // @TODO: This field does not exists?
        //$gameField = $patchForm->getField('game');
        // This field ignored then validation
        //$this->assertFalse($gameField->isValid());

        $valid = $form->isValidInlines();
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($valid);
        $valid = $form->isValid();
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($valid);

        $valid = $form->save();
        $this->assertTrue($valid);

        $instance = $form->getInstance();
        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(1, Patch::objects()->count());
        $this->assertEquals(1, $instance->patches->count());
    }

    /**
     * https://github.com/studio107/Mindy_Form/issues/7
     */
    public function testIssue7()
    {
        $this->assertEquals(0, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $model = Game::objects()->getOrCreate(['name' => 'foo']);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $form = new GameForm([
            'instance' => $model
        ]);
        $get = [
            'GameForm' => [
                'name' => 'test',
                'PatchForm' => [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                ]
            ]
        ];
        $form->populate($get);

        $inlinesCreate = $form->getInlinesCreate();
        $this->assertEquals(4, count($inlinesCreate));

        $valid = $form->isValidInlines();
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($valid);

        $valid = $form->isValid();
        $this->assertEquals([], $form->getErrors());
        $this->assertTrue($valid);

        $inlinesCreate = $form->getInlinesCreate();
        $this->assertEquals(4, count($inlinesCreate));

        $this->assertTrue($form->save());

        $instance = $form->getInstance();
        $this->assertEquals(1, Game::objects()->count());

        $patches = Patch::objects()->all();
        $this->assertEquals(1, $patches[0]->pk);

        $this->assertEquals(4, Patch::objects()->count());
        $this->assertEquals(4, $instance->patches->count());
    }

    public function testSetInstance()
    {
        $this->assertEquals(0, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $model = Game::objects()->getOrCreate(['name' => 'foo']);

        $this->assertEquals(1, Game::objects()->count());
        $this->assertEquals(0, Patch::objects()->count());

        $form = new GameForm();
        $this->assertNull($form->getInstance());
        $this->assertEquals(null, $form->getField('name')->getValue());

        $form = new GameForm([
            'instance' => $model
        ]);
        $this->assertNotNull($form->getInstance());
        $this->assertEquals('foo', $form->getField('name')->getValue());
    }
}
