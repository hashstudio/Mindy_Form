<?php

namespace Mindy\Form\Fields;

use Mindy\Locale\Translate;
use Mindy\Orm\Fields\FileField as ModelFileField;
use Mindy\Validation\FileValidator;

/**
 * Class FileField
 * @package Mindy\Form
 */
class FileField extends Field
{
    /**
     * @var string
     */
    public $type = 'file';
    /**
     * @var bool
     */
    public $cleanValue = '1';
    /**
     * @var string
     */
    public $currentTemplate = '<p class="current-file-container">{label}:<br/><a class="current-file" href="{current}" target="_blank">{current}</a></p>';
    /**
     * @var string
     */
    public $cleanTemplate = '<label for="{id}-clean" class="clean-label"><input type="checkbox" id="{id}-clean" name="{name}" value="{value}"> {label}</label>';
    /**
     * @var string
     */
    public $template = "<input type='{type}' id='{id}' name='{name}'{html}/>";
    /**
     * @var null
     */
    public $oldValue = null;
    /**
     * List of allowed file types
     * @var array|null
     */
    public $types = [];
    /**
     * @var null|int maximum file size or null for unlimited. Default value 2 mb.
     */
    public $maxSize = 2097152;

    public function init()
    {
        parent::init();

        $hasFileValidator = false;
        foreach ($this->validators as $validator) {
            if ($validator instanceof FileValidator) {
                $hasFileValidator = true;
                break;
            }
        }

        if ($hasFileValidator === false) {
            $fileValidator = new FileValidator($this->required, $this->types, $this->maxSize);
            $fileValidator->setName($this->name);
            $this->validators = array_merge([
                $fileValidator
            ], $this->validators);
        }

        $this->html['accept'] = implode('|', $this->types);
    }

    public function renderInput()
    {
        $t = Translate::getInstance();
        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getHtmlId(),
            '{name}' => $this->getHtmlName(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $value = $this->getValue();
        if (is_array($value) || $value == '') {
            $value = $this->getOldValue();
        }

        if ($value) {
            $currentLink = strtr($this->currentTemplate, [
                '{current}' => $value,
                '{label}' => $t->t('form', "Current file")
            ]);
            if ($this->required) {
                $clean = '';
            } else {
                $clean = strtr($this->cleanTemplate, [
                    '{id}' => $this->getHtmlId(),
                    '{name}' => $this->getHtmlName(),
                    '{value}' => $this->cleanValue,
                    '{label}' => $t->t('form', "Clean")
                ]);
            }
            $input = $currentLink . $clean . $input;
        }
        return $input;
    }

    public function setValue($value)
    {
        if ($value === $this->cleanValue) {
            $this->value = null;
            return $this;
        } else {
            if ($value instanceof ModelFileField) {
                $value = $value->getUrl();
            }
            $this->setOldValue();
            $this->value = $value;
            return $this;
        }
    }

    public function getOldValue()
    {
        return $this->oldValue;
    }

    public function setOldValue()
    {
        if (is_string($this->value) || !$this->oldValue) {
            $this->oldValue = $this->value;
        }
    }
}