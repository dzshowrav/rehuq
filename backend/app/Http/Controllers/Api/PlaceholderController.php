<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function svg(Request $request, $id)
    {
        $name = urldecode($request->query('t', 'Product'));
        $color = $request->query('c', '#f97316');
        $name = mb_substr($name, 0, 40);
        $h = (int) $request->query('h', 600);
        $w = (int) $request->query('w', 600);

        $safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $palette = ['#f97316', '#8b5cf6', '#06b6d4', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#14b8a6', '#a855f7'];
        $c = $palette[$id % count($palette)];
        $c2 = $palette[($id + 3) % count($palette)];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">'
            . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
            . '<stop offset="0%" stop-color="'.$c.'"/><stop offset="100%" stop-color="'.$c2.'"/></linearGradient></defs>'
            . '<rect width="100%" height="100%" fill="url(#g)"/>'
            . '<circle cx="'.($w * 0.8).'" cy="'.($h * 0.15).'" r="'.($w * 0.3).'" fill="rgba(255,255,255,0.12)"/>'
            . '<circle cx="'.($w * 0.15).'" cy="'.($h * 0.85).'" r="'.($w * 0.25).'" fill="rgba(255,255,255,0.10)"/>'
            . '<rect x="'.($w * 0.12).'" y="'.($h * 0.42).'" width="'.($w * 0.76).'" height="'.($h * 0.2).'" rx="14" fill="rgba(255,255,255,0.92)"/>'
            . '<text x="50%" y="'.($h * 0.56).'" font-family="Arial, sans-serif" font-size="'.(int) ($w * 0.045).'" font-weight="700" fill="#1e293b" text-anchor="middle" dominant-baseline="middle">'.$safe.'</text>'
            . '<text x="50%" y="'.($h * 0.66).'" font-family="Arial, sans-serif" font-size="'.(int) ($w * 0.025).'" fill="rgba(255,255,255,0.9)" text-anchor="middle">Rehuq</text>'
            . '</svg>';

        return response($svg, 200)->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
