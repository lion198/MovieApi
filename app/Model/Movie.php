<?php

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class Movie extends dbObject
{
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    protected string $title;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDirectorId(): int
    {
        return $this->directorId;
    }

    public function setDirectorId(int $directorId): void
    {
        $this->directorId = $directorId;
    }

    public function getGenreId(): int
    {
        return $this->genreId;
    }

    public function setGenreId(int $genreId): void
    {
        $this->genreId = $genreId;
    }
    protected string $description;
    protected int $directorId;
    protected int $genreId;
    const TABLE_NAME = 'movie';

    public function __construct(Explorer $database)
    {
        parent::__construct($database, 'movie');
    }


    protected function dataToObject(ActiveRow $data): void
    {
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->directorId = $data['director_id'];
        $this->genreId = $data['genre_id'];
    }

    public function getDataArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'director_id' => $this->directorId,
            'genre_id' => $this->genreId,
        ];
    }
}
