<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\WaQueue;
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
            $controller = app(WaRangkumanController::class);
            $dataRequest = new Request(['date' => $today]);
            $dataResponse = $controller->getData($dataRequest);
            $data = json_decode($dataResponse->getContent(), true);

            if ($data && isset($data['rows'])) {
                // Kirim langsung ke WaQueue (tanpa simpan history)
                $groupId = config('app.wa_rangkuman_group_id');
                if (!empty($groupId)) {
                    $lines = [];
                    $carbonDate = Carbon::parse($today);
                    $formatted = $carbonDate->locale('id')->isoFormat('D MMMM YYYY');
                    $lines[] = '═══════════════════════════════';
                    $lines[] = '  *WA RANGKUMAN PRODUKSI*';
                    $lines[] = '  ' . strtoupper($formatted);
                    $lines[] = '═══════════════════════════════';
                    $lines[] = '';

                    foreach ($data['rows'] as $group) {
                        $lines[] = '*' . $group['group'] . '*';
                        foreach ($group['items'] as $item) {
                            $t = (int)($item['T'] ?? 0);
                            $a = (int)($item['A'] ?? 0);
                            $s = (int)($item['S'] ?? 0);
                            $gt = (int)($item['GT'] ?? 0);
                            $sStr = $s < 0 ? (string)$s : ($s > 0 ? (string)$s : '0');
                            $lines[] = '  ' . $item['label'] . ':';
                            $lines[] = '    T = ' . $t . '   A = ' . $a . '   S = ' . $sStr . '   GT = ' . $gt;
                        }
                        $lines[] = '';
                    }

                    $lines[] = '_Update: ' . $now->format('d/m/Y H:i:s') . '_';
                    $lines[] = '═══════════════════════════════';

                    WaQueue::create([
                        'message' => implode("\n", $lines),
                        'group_id' => $groupId,
                        'status' => 'pending',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silent fail — tidak ganggu request
        }

        Cache::put($doneKey, true, now()->addHours(24));

        return $next($request);
    }
}
