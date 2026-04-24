<?php

namespace Middlewares;

use Src\Request;
use function Collect\collection;

class JSONMiddleware
{
    public function handle(Request $request): Request
    {
        if ($request->method === 'GET') {
            return $request;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        collection($data)->each(function ($item, $key, $request): void {
            $request->set($key, $item);
        }, $request);

        return $request;
    }
}
