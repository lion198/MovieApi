<?php

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

abstract class dbObject
{
    const TABLE_NAME = '';
    protected Explorer $database;
    protected string $tableName;

    protected ?int $id = null;

    public function __construct(Explorer $database, string $tableName)
    {
        $this->database = $database;
        $this->tableName = $tableName;
    }

    public static function find(Explorer $database, int $id): ?self
    {
        $data = $database->table(static::TABLE_NAME)->get($id);
        if ($data) {
            $object = new static($database);
            $object->id = $id;
            $object->dataToObject($data);
            return $object;
        }
        return null;
    }


    protected abstract function dataToObject(ActiveRow $data): void;

    public function save(): void
    {
        $values = $this->getDataArray();
        if ($this->id === null) {
            $this->id = $this->database->table($this->tableName)->insert($values);
        } else {
            $this->database->table($this->tableName)->wherePrimary($this->id)->update($values);
        }
    }

    public function delete(): void
    {
        if ($this->id !== null) {
            $this->database->table($this->tableName)->wherePrimary($this->id)->delete();
            $this->id = null;
        }
    }

    protected abstract function getDataArray(): array;
}