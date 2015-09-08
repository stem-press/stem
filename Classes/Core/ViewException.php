<?php
namespace ILab\Stem\Core;

class ViewException extends \ErrorException {
    protected $original;
    protected $data;

    public function __construct($data,$original, $message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous=null) {
        parent::__construct($message,$code,$severity,$filename,$lineno,$previous);

        $this->data=$data;
        $this->original=$original;
    }

    public function getOriginal() {
        return $this->original;
    }

    public function getData() {
        return $this->data;
    }
}