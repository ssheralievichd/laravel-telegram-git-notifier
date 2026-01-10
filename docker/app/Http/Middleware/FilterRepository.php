<?php

namespace App\Http\Middleware;

use App\Services\RepositoryFilter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterRepository
{
    public function handle(Request $request, Closure $next): Response
    {
        $filter = new RepositoryFilter();

        $payload = $request->all();

        if (!$filter->shouldProcess($payload)) {
            return response('', 200);
        }

        return $next($request);
    }
}
