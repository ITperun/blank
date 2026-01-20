<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\RouteList;

final class RouterFactory
{
public static function createRouter(): RouteList
{
    $router = new RouteList;

    // Admin модуль (оставляем как есть, он должен быть первым!)
    $adminRouter = new RouteList('Admin');
    $adminRouter->addRoute('admin/<presenter>/<action>[/<id>]', 'Dashboard:default');
    $router->add($adminRouter);
    
    // Этот маршрут у тебя был добавлен отдельно
    $router->addRoute('admin/search[/<action>[/<keyword>]]', 'Admin:Search:default');

    // Front модуль
    $frontRouter = new RouteList('Front');
    
    // БЫЛО: $frontRouter->addRoute('front/<presenter>/<action>[/<id>]', 'Home:default');
    // СТАЛО (убираем 'front/'):
    $frontRouter->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
    
    $router->add($frontRouter);

    return $router;
}}