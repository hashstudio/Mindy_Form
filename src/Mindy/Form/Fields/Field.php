<?php

namespace Mindy\Form\Fields;

use Exception;
use Mindy\Form\BaseForm;
use Mindy\Form\ModelForm;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Validation\Interfaces\IValidateField;
use Mindy\Validation\RequiredValidator;
use Mindy\Validation\Traits\ValidateField;

/**
 * Class Field
 * @package Mindy\Form
 */
abstract class Field implements IValidateField
{
    use Accessors, Configurator, ValidateField;

    /**
     * @var bool Технические аттрибуты для inline моделей
     */
    public $hidden = false;
    /**
     * @var bool Технические аттрибуты для inline моделей
     */
    public $delete = false;
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var bool
     */
    public $required = false;
    /**
     * @var TODO
     */
    public $widget;
    /**
     * @var string
     */
    public $inputType;
    /**
     * @var string
     */
    public $template = "<input type='{type}' id='{id}' value='{value}' name='{name}'{html}/>";
    /**
     * @var string
     */
    public $hint;
    /**
     * @var string
     */
    public $type = 'text';
    /**
     * @var string
     */
    public $html = '';
    /**
     * @var array
     */
    public $choices = [];
    /**
     * @var string html class for render hint
     */
    public $hintClass = 'form-hint-text';
    /**
     * @var
     */
    public $label;
    /**
     * @var string
     */
    public $errorClass = 'error';
    /**
     * @var
     */
    private $_name;
    /**
     * @var BaseForm
     */
    private $_form;
    /**
     * @var string
     */
    private $_validatorClass = '\Mindy\Form\Validator\Validator';
    /**
     * @var string
     */
    private $_prefix;

    public function init()
    {
        if ($this->required) {
            $this->validators[] = new RequiredValidator();
        }
        foreach ($this->validators as $validator) {
            /** @var $validator \Mindy\Validation\Validator */
            if (is_subclass_of($validator, $this->_validatorClass)) {
                $validator->setName($this->label ? $this->label : $this->name);
            }
        }
    }

    public function __toString()
    {
        try {
            return (string)$this->render();
        } catch (Exception $e) {
            echo (string)$e;
            die();
        }
    }

    /**
     * @param BaseForm $form
     * @return $this
     */
    public function setForm(BaseForm $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @return BaseForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    public function setPrefix($value)
    {
        $this->_prefix = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        $prefix = $this->_prefix ? $this->_prefix : $this->getForm()->getPrefix();
        if ($prefix) {
            return $prefix . '[' . $this->form->classNameShort() . '][' . $this->getId() . ']';
        } else {
            return $this->getForm()->classNameShort();
        }
    }

    public function getId()
    {
        return $this->form->getId();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function renderInput()
    {
        return strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getHtmlId(),
            '{name}' => $this->getHtmlName(),
            '{value}' => $this->getValue(),
            '{html}' => $this->getHtmlAttributes()
        ]);
    }

    public function render()
    {
        $label = $this->renderLabel();
        $input = $this->renderInput();

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        return implode("\n", [$label, $input, $hint, $errors]);
    }

    public function getHtmlName()
    {
        return $this->getPrefix() . '[' . $this->name . ']';
    }

    public function getHtmlAttributes()
    {
        $hasErrors = $this->hasErrors();

        if (is_array($this->html)) {
            if ($hasErrors) {
                if (!isset($this->html['class'])) {
                    $this->html['class'] = 'has-errors';
                } else {
                    $this->html['class'] .= ' has-errors';
                }
            }
            $html = '';
            foreach ($this->html as $name => $value) {
                $html .= is_numeric($name) ? " $value" : " $name='$value'";
            }
            return $html;
        } else {
            $html = (string) $this->html;
            if (mb_strpos($html, 'class') === false && $hasErrors) {
                $html .= " class='has-errors'";
            }
            return $html;
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
//        if ($this->value === null) {
//            if ($this->form instanceof ModelForm) {
//                $instance = $this->form->getInstance();
//                if ($instance->hasField($this->name)) {
//                    return $instance->getField($this->name)->getValue();
//                }
//            }
//        }
        return $this->value;
    }

    public function renderLabel()
    {
        if ($this->label === false) {
            return '';
        }

        if ($this->label) {
            $label = $this->label;
        } else {
            if ($this->form instanceof ModelForm) {
                $instance = $this->form->getModel();
                if ($instance->hasField($this->name)) {
                    $verboseName = $instance->getField($this->name)->verboseName;
                    if ($verboseName) {
                        $label = $verboseName;
                    }
                }
            }

            if (!isset($label)) {
                $label = ucfirst($this->name);
            }
        }

        return strtr("<label for='{for}'>{label}</label>", [
            '{for}' => $this->getHtmlId(),
            '{label}' => $label
        ]);
    }

    public function renderErrors()
    {
        $errors = "";
        foreach ($this->getErrors() as $error) {
            $errors .= "<li>{$error}</li>";
        }

        $html = "";
        if (!$errors) {
            $html = "style='display:none;'";
        }

        return "<ul class='{$this->errorClass}' id='{$this->getHtmlId()}_errors' {$html}>{$errors}</ul>";
    }

    public function renderHint()
    {
        return strtr('<p class="{class}">{hint}</p>', [
            '{class}' => $this->hintClass,
            '{hint}' => $this->hint
        ]);
    }

    /**
     * Format:
     * [
     *     "Main" => [
     *         "Name", "Url", "Content"
     *     ],
     *     "Extra" => [ ... ]
     * ]
     * @return array
     */
    public function getFieldSets()
    {
        return [];
    }

    public function getHtmlId()
    {
        return $this->getHtmlPrefix() . $this->getName();
    }

    public function getHtmlPrefix()
    {
        return rtrim(str_replace(['][', '[]', '[', ']'], '_', $this->getPrefix()), '_') . '_';
    }
}
