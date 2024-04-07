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

    const AUTH_TOKEN = '$2y$10$xXWpmyNxGSG.JmBGsW3F2.7BiSz8UHa06mCVym5mPDp2qKMB.obD.';

    public function startup()
    {
        parent::startup();
        $token = $this->getHttpRequest()->getHeader('Authorization');
        if (!$token) {
            $this->error('Need Authorization token', 403);
        }
        /* password_hash('token') bcrypt Token*/
        if (!password_verify($token, self::AUTH_TOKEN)) {
            $this->error('Bad token', 403);
        }
    }

    public function actionMovies()
    {
        $request = $this->getHttpRequest();
        switch ($request->getMethod()) {
            case IRequest::Get:
                $this->getMovieList();
                break;
            case IRequest::Post:
                $this->addMovie();
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
        $data = json_decode($this->getHttpRequest()->getRawBody(), true);
        $filter = $data['filter'] ?? '';
        $limit = $data['limit'] ?? 0;

        $offset = $data['offset'] ?? 0;
        $where = [];
//        if ($filter) {
//            $where[] = ['title LIKE ?', "%{$this->database->getConnection()->quote($filter)}%"];
//        }

        if ($limit && $offset) {
            $movies = $this->database->table('movie')
                ->where($where)
                ->order('title ' . 'ASC')
                ->limit($limit, $offset)
                ->fetchAll();
        } else {
            $this->database->table('movie')
                ->where($where)
                ->order('title ASC')
                ->fetchAll();
        }
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
            'genre_id' => $movie->genre_id,
            'director_id' => $movie->director_id,
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

