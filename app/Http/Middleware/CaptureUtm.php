<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CaptureUtm — captures UTM parameters and referrer on first touch
 * and stores them in the session for use at booking confirmation time.
 *
 * Parameters captured:
 *   utm_source, utm_medium, utm_campaign, utm_content, utm_term,
 *   referrer (HTTP_REFERER), landing_page (current URL)
 *
 * Uses FIRST-TOUCH attribution — once set, not overwritten in same session.
 * To switch to LAST-TOUCH, remove the `hasUtmData()` guard.
 */
class CaptureUtm
{
    /** UTM query param keys we care about */
    private const UTM_KEYS = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only capture on first touch (first-touch attribution)
        if (!$this->hasUtmData($request)) {
            $utm = [];

            foreach (self::UTM_KEYS as $key) {
                if ($request->filled($key)) {
                    $utm[$key] = strip_tags(substr($request->input($key), 0, 200));
                }
            }

            // Always capture referrer + landing page on first visit
            $utm['referrer']     = $this->cleanReferrer($request->headers->get('referer', ''));
            $utm['landing_page'] = $request->url();

            // Auto-detect source from referrer if utm_source is missing
            if (empty($utm['utm_source'])) {
                $utm['utm_source'] = $this->detectSource($utm['referrer'], $request);
            }

            $request->session()->put('utm', $utm);
        }

        return $next($request);
    }

    /**
     * Check if any UTM data has already been captured this session.
     */
    private function hasUtmData(Request $request): bool
    {
        $existing = $request->session()->get('utm', []);
        return !empty($existing['utm_source']) || !empty($existing['referrer']);
    }

    /**
     * Auto-detect traffic source from HTTP referrer or request context.
     */
    private function detectSource(string $referrer, Request $request): string
    {
        if (empty($referrer)) {
            // Could be direct, app bookmark, or dark social
            return 'direct';
        }

        $host = strtolower(parse_url($referrer, PHP_URL_HOST) ?? '');

        // Search engines
        if (str_contains($host, 'google'))    return 'google';
        if (str_contains($host, 'bing'))      return 'bing';
        if (str_contains($host, 'yahoo'))     return 'yahoo';
        if (str_contains($host, 'duckduck'))  return 'duckduckgo';

        // Social media
        if (str_contains($host, 'facebook') || str_contains($host, 'fb.'))  return 'facebook';
        if (str_contains($host, 'instagram')) return 'instagram';
        if (str_contains($host, 'twitter') || str_contains($host, 't.co'))  return 'twitter';
        if (str_contains($host, 'linkedin'))  return 'linkedin';
        if (str_contains($host, 'youtube'))   return 'youtube';
        if (str_contains($host, 'whatsapp'))  return 'whatsapp';

        // Own domain (internal navigation — shouldn't really land here)
        $appHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?? '');
        if ($appHost && str_contains($host, $appHost)) return 'internal';

        // Otherwise treat as referral
        return 'referral';
    }

    /**
     * Sanitize and shorten the referrer URL.
     */
    private function cleanReferrer(string $ref): string
    {
        return substr(strip_tags($ref), 0, 500);
    }
}
