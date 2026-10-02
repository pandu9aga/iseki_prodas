<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Admin\WaRangkumanController;
use Carbon\Carbon;

class WaRangkumanAutoMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeek;
        $today = $now->toDateString();

        // Skip weekend
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            return $next($request);
        }

        // Tentukan jam trigger: Mon-Thu 16:25, Fri 16:55
        $triggerMinute = ($dayOfWeek === 5) ? 55 : 25;
        $triggerTime = Carbon::today()->setHour(16)->setMinute($triggerMinute)->setSecond(0);

        // Cek sudah lewat trigger & belum pernah diproses hari ini
        $doneKey = 'wa_rangkuman_done_' . $today;

        if ($now < $triggerTime || Cache::has($doneKey)) {
            return $next($request);
        }

        try {
            app(WaRangkumanController::class)->queueForDate($today);
        } catch (\Exception $e) {
            // Silent fail — tidak ganggu request
        }

        Cache::put($doneKey, true, now()->addHours(24));

        return $next($request);
    }
}
