<?php

/** This file handles form validation.
 *
 * @author Matthew Frazier <mlfrazie@ncsu.edu>
 */


class Tuffy_Form_ValidationError extends Exception {
    // this class intentionally left blank
}


class Tuffy_Form_Reader {
    public $name;
    public $label;
    public $validators = array();

    public function __construct ($name, $label, $options) {
        $this->name = $name;
        $this->label = $label;
        $this->validators = $validators;
    }

    public function loadDefault ($value) {
        return $value;
    }

    public function loadInput ($value) {
        return $value;
    }

    public function validate ($data) {
        return;
    }
}


class Tuffy_Form_FieldDef {
    private $field;

    public function __construct ($fieldClass, $name, $label, $options) {
        $this->field = new $fieldClass($name, $label, $options);
    }
}


class Tuffy_Form_Definition {
    public static $fieldTypes = array();
    public static $validators = array();

    private $fields = array();

    public function __construct ($parent = NULL) {
        $this->fields = $parent->getFields();
    }

    public function getFields () {
        return $this->fields;
    }
}

