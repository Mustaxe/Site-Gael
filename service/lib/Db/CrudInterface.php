<?php

namespace lib\Db;

interface CrudInterface
{
    public function setVariables($name, $value);

    public function getVariables();

    public function findAll();

    public function findById($id);

    public function save();

    public function create();

    public function delete($id);
}
