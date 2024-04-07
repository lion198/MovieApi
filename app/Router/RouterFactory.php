<?php

declare(strict_types=1);

namespace App\Router;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
        $router->addRoute('api/v1/movies', 'Api:Movies');
        $router->addRoute('api/v1/movies/<id>', 'Api:SpecificMovie');
        return $router;
	}
}
