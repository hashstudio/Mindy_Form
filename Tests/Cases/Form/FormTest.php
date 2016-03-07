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
 * @date 20/10/14.10.2014 16:34
 */

namespace Mindy\Form\Tests;

use Mindy\Form\BaseForm;
use Mindy\Form\Fields\CharField;
use Mindy\Form\Form;

class FormTest extends TestCase
{
    public function setUp()
    {
        BaseForm::$ids = [];
    }

    public function testFieldsWithoutPrefix()
    {
        $f = new CharField(['form' => new TestForm(), 'name' => 'foo', 'hint' => 'foo bar']);
        $this->assertEquals('TestForm', $f->getPrefix());
        $this->assertEquals('TestForm[foo]', $f->getHtmlName());
        $this->assertEquals('TestForm_foo', $f->getHtmlId());
        $this->assertEquals("<label for='TestForm_foo'>Foo</label>", $f->renderLabel());
        $this->assertEquals('<p class="form-hint-text">foo bar</p>', $f->renderHint());
        $this->assertEquals("<ul class='error' id='TestForm_foo_errors' style='display:none;'></ul>", $f->renderErrors());

        $this->assertEquals("<input type='text' value='' id='TestForm_foo' name='TestForm[foo]'/>", $f->renderInput());

        $f->setValue('bar');
        $this->assertEquals("<input type='text' value='bar' id='TestForm_foo' name='TestForm[foo]'/>", $f->renderInput());

        $f->html = ['data-id' => 1];
        $this->assertEquals(" data-id='1'", $f->getHtmlAttributes());

        $f->html = ['data-id' => 1, 'readonly'];
        $this->assertEquals(" data-id='1' readonly", $f->getHtmlAttributes());

        $f->html = " data-id='1' readonly";
        $this->assertEquals(" data-id='1' readonly", $f->getHtmlAttributes());
    }

    public function testFieldsWithPrefix()
    {
        $f = new CharField(['form' => new TestForm(), 'name' => 'foo', 'hint' => 'foo bar']);
        $f->setPrefix('test');
        $this->assertEquals('test[TestForm][0]', $f->getPrefix());
        $this->assertEquals('test[TestForm][0][foo]', $f->getHtmlName());
        $this->assertEquals('test_TestForm_0_foo', $f->getHtmlId());
        $this->assertEquals("<label for='test_TestForm_0_foo'>Foo</label>", $f->renderLabel());
        $this->assertEquals('<p class="form-hint-text">foo bar</p>', $f->renderHint());
        $this->assertEquals("<ul class='error' id='test_TestForm_0_foo_errors' style='display:none;'></ul>", $f->renderErrors());

        $this->assertEquals("<input type='text' value='' id='test_TestForm_0_foo' name='test[TestForm][0][foo]'/>", $f->renderInput());

        $f->setValue('bar');
        $this->assertEquals("<input type='text' value='bar' id='test_TestForm_0_foo' name='test[TestForm][0][foo]'/>", $f->renderInput());

        $f->html = ['data-id' => 1];
        $this->assertEquals(" data-id='1'", $f->getHtmlAttributes());

        $f->html = ['data-id' => 1, 'readonly'];
        $this->assertEquals(" data-id='1' readonly", $f->getHtmlAttributes());

        $f->html = " data-id='1' readonly";
        $this->assertEquals(" data-id='1' readonly", $f->getHtmlAttributes());
    }

    public function testInit()
    {
        $f = new Form([
            'exclude' => ['foo', 'bar'],
        ]);
        $this->assertEquals([], $f->getFields());
        $this->assertEquals([], $f->getFieldsInit());
        $this->assertEquals(['foo', 'bar'], $f->getExclude());
        $this->assertEquals(null, $f->getPrefix());
        $this->assertEquals([], $f->getInlines());
        $this->assertEquals('block', $f->defaultTemplateType);
    }

    public function testRender()
    {
        $f = new TestForm();
        $this->assertEquals(['name'], array_keys($f->getFields()));
        $this->assertEquals(['name'], array_keys($f->getFieldsInit()));
        $this->assertEquals([], $f->getExclude());
        $this->assertEquals(null, $f->getPrefix());
        $this->assertEquals([], $f->getInlines());
        $this->assertEquals('block', $f->defaultTemplateType);

        $this->assertEquals(realpath(__DIR__ . '/../../Templates/block.php'), $f->getTemplateFromType('block'));
        $this->assertEquals(realpath(__DIR__ . '/../../Templates/table.php'), $f->getTemplateFromType('table'));
        $this->assertEquals(realpath(__DIR__ . '/../../Templates/ul.php'), $f->getTemplateFromType('ul'));

        $result = implode("\n", [
            "<label for='TestForm_name'>Name</label>",
            "<input type='text' value='' id='TestForm_name' name='TestForm[name]'/>",
            "",
            "<ul class='error' id='TestForm_name_errors' style='display:none;'></ul>",
            ""
        ]);
        $this->assertEquals($result, $f->renderInternal(realpath(__DIR__ . '/../../Templates/block.php'), [
            'form' => $f,
            'inlines' => $f->getInlinesInit()
        ]));
        $this->assertEquals($result, $f->asBlock());
    }

    public function testInline()
    {
        $f = new ManagedTestForm();
        $this->assertEquals(1, count($f->getInlines()));
        $result = implode("\n", [
            "<label for='ManagedTestForm_name'>Name</label>",
            "<input type='text' value='' id='ManagedTestForm_name' name='ManagedTestForm[name]'/>",
            "",
            "<ul class='error' id='ManagedTestForm_name_errors' style='display:none;'></ul>",
            "<h2>InlineTestForm</h2>",
            "<label for='ManagedTestForm_InlineTestForm_0_foo'>Foo</label>",
            "<input type='text' value='' id='ManagedTestForm_InlineTestForm_0_foo' name='ManagedTestForm[InlineTestForm][0][foo]'/>",
            "",
            "<ul class='error' id='ManagedTestForm_InlineTestForm_0_foo_errors' style='display:none;'></ul>",
            "<label for='ManagedTestForm_InlineTestForm_0_bar'>Bar</label>",
            "<input type='file' id='ManagedTestForm_InlineTestForm_0_bar' name='ManagedTestForm[InlineTestForm][0][bar]'/>",
            "",
            "<ul class='error' id='ManagedTestForm_InlineTestForm_0_bar_errors' style='display:none;'></ul>",
            "",
        ]);
        $this->assertEquals($result, $f->asBlock());

        $f->setAttributes([
            'name' => '1',
            'InlineTestForm' => [
                ['foo' => '1', 'bar' => '1'],
                // This inline not created (validation error)
                ['foo' => '', 'bar' => '1'],
            ]
        ]);

        /** @var \Mindy\Form\Tests\InlineTestForm[] $inlines */
        $inlines = $f->getInlinesCreate();
        $this->assertEquals(2, count($inlines));
        list($first, $last) = $inlines;
        $this->assertEquals(['foo' => '1', 'bar' => '1'], $first->getAttributes());
        $this->assertTrue($first->isValid());
        $this->assertEquals(['foo' => '', 'bar' => '1'], $last->getAttributes());
        $this->assertFalse($last->isValid());

        $this->assertFalse($f->isValidInlines());
        $this->assertFalse($f->isValid());

        $f->clearErrors();

        $f->setAttributes([
            'name' => '1',
            'InlineTestForm' => [
                // This inline created
                ['foo' => '3', 'bar' => '', '_delete' => '1'],
            ]
        ]);
        $this->assertTrue($f->isValid());
        $this->assertEquals([], $f->getErrors());
        $f->clearErrors();
        $this->assertEquals([], $f->getErrors());
        $inlines = $f->getInlinesDelete();
        $this->assertEquals(1, count($inlines));

        $f->setAttributes([
            'name' => '1',
            'InlineTestForm' => [
                // This inline not created (ignoring)
                ['foo' => '', 'bar' => ''],
            ]
        ]);
        $this->assertFalse($f->isValid());
        $inlines = $f->getInlinesDelete();
        $this->assertEquals(1, count($inlines));
    }

    public function testCloneForm()
    {
        $f = new TestForm(['prefix' => 'foo']);
        $f->setAttributes(['name' => 123]);
        $this->assertEquals(0, $f->getId());
        $this->assertEquals(['name' => 123], $f->getAttributes());
        $this->assertEquals(0, $f->getField('name')->getId());
        $this->assertEquals('foo_TestForm_0_name', $f->getField('name')->getHtmlId());
        $this->assertEquals('foo[TestForm][0][name]', $f->getField('name')->getHtmlName());

        $twoForm = clone $f;
        $this->assertEquals(['name' => 123], $twoForm->getAttributes());
        $this->assertEquals(1, $twoForm->getId());
        $this->assertEquals(1, $twoForm->getField('name')->getId());
        $this->assertEquals('foo_TestForm_1_name', $twoForm->getField('name')->getHtmlId());
        $this->assertEquals('foo[TestForm][1][name]', $twoForm->getField('name')->getHtmlName());

        $threeForm = clone $f;
        $this->assertEquals(['name' => 123], $threeForm->getAttributes());
        $this->assertEquals(2, $threeForm->getId());
        $this->assertEquals(2, $threeForm->getField('name')->getId());
        $this->assertEquals('foo_TestForm_2_name', $threeForm->getField('name')->getHtmlId());
        $this->assertEquals('foo[TestForm][2][name]', $threeForm->getField('name')->getHtmlName());
    }
}