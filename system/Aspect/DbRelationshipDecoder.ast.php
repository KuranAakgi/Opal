<?php
abstract class DbRelationshipDecoder{
    protected $id;
    public function __construct($id) {
        $this->id = $id;
    }
    abstract public function decode($config);
}
