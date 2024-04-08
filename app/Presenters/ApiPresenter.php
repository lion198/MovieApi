<?php

namespace App\Presenters;

use JetBrains\PhpStorm\NoReturn;
use Movie;
use Nette;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\IRequest;

final class ApiPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    const AUTH_TOKEN = '$2y$10$xXWpmyNxGSG.JmBGsW3F2.7BiSz8UHa06mCVym5mPDp2qKMB.obD.';

    public function startup(): void
    {
        parent::startup();
        $token = $this->getHttpRequest()->getHeader('Authorization');
        if (!$token) {
            $this->sendMsgStatus('Need Authorization token', 403);
        }
        /* password_hash('token') bcrypt Token*/
        if (!password_verify($token, self::AUTH_TOKEN)) {
            $this->sendMsgStatus('Bad token', 403);
        }
    }

    public function actionMovies(): void
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

    public function actionSpecificMovie($id): void
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
        $data = json_decode($this->getHttpRequest()->getRawBody(), true);
        $filter = $data['filter'] ?? '';
        $limit = isset($data['limit']) ? (int)$data['limit'] : 0;
        $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
        $where = [];

        if ($filter) {
            $where['title LIKE ?'] = "%$filter%";
        }
        $query = $this->database->table('movie')->where($where)->order('title ASC');

        if ($limit > 0) {
            if ($offset > 0) {
                $query->limit($limit, $offset);
            } else {
                $query->limit($limit);
            }
        }
        $movies = $query->fetchAll();
        $data = array_map(
            function ($movie) {
                return [
                'id' => $movie->id,
                'title' => $movie->title,
                ];
            }, $movies
        );

        $this->sendResponse(new JsonResponse($data));
    }

    private function addMovie()
    {
        $data = json_decode($this->getHttpRequest()->getRawBody(), true);

        $this->isValidData($data);
        $movie = new Movie($this->database);
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setGenreId($data['genre_id']);
        $movie->setDirectorId($data['director_id']);
        $movie->save();

        $this->sendJson($movie->getDataArray());

    }

    private function editMovie(string $id): void
    {
        $movie = Movie::find($this->database, $id);
        if (!$movie instanceof Movie) {
            $this->sendMsgStatus('Movie not found', 404);
        }
        $data = json_decode($this->getHttpRequest()->getRawBody(), true);
        $this->isValidData($data);
        $movie->setTitle($data['title']);
        $movie->setDescription($data['description']);
        $movie->setGenreId($data['genre_id']);
        $movie->setDirectorId($data['director_id']);
        $movie->save();
        $this->sendResponse(new JsonResponse($movie->getDataArray()));

    }

    private function detailMovie(string $id): void
    {
        $movie = Movie::find($this->database, $id);
        if (!$movie instanceof Movie) {
            $this->sendMsgStatus('Movie not found', 404);
        }
        $this->sendJson($movie->getDataArray());
    }

    private function deleteMovie(int $id): void
    {
        $movie = Movie::find($this->database, $id);
        if (!$movie instanceof Movie) {
            $this->sendMsgStatus('Movie not found', 404);
        }
        $movie->delete();
        $this->sendMsgStatus('Movie with' . $id . ' deleted');
    }

    #[NoReturn] public function sendMsgStatus(string $msg = 'OK', int $statusCode = 200): void
    {
        $this->getHttpResponse()->setCode($statusCode);
        $response = [
            'State' => $msg
        ];
        $jsonResponse = new JsonResponse($response);
        $this->sendResponse($jsonResponse);
    }

    private function isValidData($data): void
    {
        $dataToCompare = ['title', 'description', 'genre_id', 'director_id'];
        foreach ($dataToCompare as $item) {
            if (!isset($data[$item])) {
                $this->sendMsgStatus('Bad param', 404);
            }
            if (($item === 'title' || $item === 'description') && (is_string($data[$item]) && !empty($data[$item]))) {
                continue;

            } elseif (($item === 'genre_id' || $item === 'director_id') && (is_int($data[$item]) && $data[$item] !== 0)) {
                continue;
            } else {
                $this->sendMsgStatus('Bad param', 404);
            }
        }
    }

}

