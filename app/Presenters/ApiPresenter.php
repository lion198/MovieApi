<?php

namespace App\Presenters;

use Nette;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\IRequest;
use Nette\Http\Request;

final class ApiPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    )
    {
    }

    public function actionMovies()
    {
        $request = $this->getHttpRequest();
        switch ($request->getMethod()) {
            case IRequest::Get:
                $this->getMovieList($request);
                break;
            case IRequest::Post:
                $this->addMovie($request);
                break;
            default:
                $this->error('Bad Request', 400);
        }
    }

    public function actionSpecificMovie($id)
    {
        $request = $this->getHttpRequest();
        switch ($request->getMethod()) {
            case IRequest::Get:
                $this->detailMovie($id);
                break;
            case IRequest::Put:
                $this->editMovie($id);
                break;
            case IRequest::Delete:
                $this->deleteMovie($id);
                break;
            default:
                $this->error('Bad Request', 400);
        }
    }

    private function getMovieList(): void
    {
        $movies = $this->database->table('movie')->fetchAll();
        $data = array_map(function ($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
            ];
        }, $movies);

        $this->sendResponse(new JsonResponse($data));
    }

    private function addMovie()
    {
        $data = json_decode($this->getHttpRequest()->getRawBody(), true);

        $data = [
            'title' => $data ?? '',
            'description' => $data ?? '',
            'genre_id' => $data ?? '',
            'director_id' => $data ?? '',
        ];
        $this->database->table('movie')->insert($data);

        $this->sendJson($data);

    }

    private function editMovie($id): void
    {
        $movie = $this->database->table('movie')->where('id', $id)->fetch();

        if (!$movie) {
            $this->error(404, 'Movie not found');
        }
        $data = json_decode($this->getHttpRequest()->getRawBody(), true);

        $data = [
            'title' => $data['title'] ?? $movie->title,
            'description' => $data['description'] ?? $movie->description,
            'genre_id' => $data['genre_id'] ?? $movie->genre_id,
            'director_id' => $data['director_id'] ?? $movie->director_id,
        ];

        $this->database->table('movie')
            ->where('id', $id)
            ->update($data);

        $this->sendResponse(new JsonResponse($data));

    }

    private function detailMovie($id): void
    {
        $movie = $this->database->table('movie')->where('id', $id)->fetch();
        if (!$movie) {
            $this->error(404, 'Movie not found');
        }
        $data = [
            'id' => $movie->id,
            'title' => $movie->title,
            'description' => $movie->description,
        ];

        $this->sendResponse(new JsonResponse($data));
    }

    private function deleteMovie($id)
    {
        $movie = $this->database->table('movie')->where('id', $id)->delete();
        $this->sendResponse(new JsonResponse('OK'));

    }

}

