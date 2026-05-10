<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScannerMiddleware
{
    /**
     * Scanner staff access via PIN stored in session.
     * PIN is set in .env as SCANNER_PIN (default: 1234)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('scanner_authenticated') !== true) {
            // Redirect to PIN login page
            return redirect()->route('scanner.login');
        }

        return $next($request);
    }
}
