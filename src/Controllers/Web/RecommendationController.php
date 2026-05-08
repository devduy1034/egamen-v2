<?php

namespace LARAVEL\Controllers\Web;

use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Models\ProductModel;
use LARAVEL\Services\RecommendationEngineService;

class RecommendationController extends Controller
{
    public function index(Request $request)
    {
        $userId = trim((string) $request->query('user_id', ''));
        $anonymousId = trim((string) $request->query('anonymous_id', ''));
        $sessionId = trim((string) $request->query('session_id', ''));
        $limit = (int) $request->query('limit', 20);
        $contextProductId = (int) $request->query('context_product_id', 0);

        if ($userId === '' && $anonymousId === '') {
            $member = session()->get('member');
            if (is_array($member)) {
                $member = $member['member'] ?? '';
            }
            $userId = trim((string) $member);
        }

        $service = new RecommendationEngineService();
        $result = $service->recommend(
            $userId !== '' ? $userId : null,
            $anonymousId !== '' ? $anonymousId : null,
            $limit,
            $contextProductId,
            $sessionId !== '' ? $sessionId : session_id()
        );

        $format = strtolower(trim((string) $request->query('format', 'json')));
        if ($format === 'html') {
            $products = $this->loadRecommendedProducts((array) ($result['items'] ?? []));
            $reasonMap = [];
            foreach ((array) ($result['items'] ?? []) as $item) {
                $pid = (int) ($item['product_id'] ?? 0);
                if ($pid > 0) {
                    $reasonMap[$pid] = (string) ($item['reason'] ?? 'Recommended for you');
                }
            }
            return view('ajax.recommendProducts', [
                'productAjax' => $products,
                'reasonMap' => $reasonMap,
            ]);
        }

        $thumbSize = '320x320x1';
        $fallbackPhoto = assets('assets/images/noimage.png');
        $result['items'] = array_map(function ($item) use ($thumbSize, $fallbackPhoto) {
            $item = is_array($item) ? $item : [];
            $slug = trim((string) ($item['slug'] ?? ''));
            $photo = trim((string) ($item['photo'] ?? ''));
            $item['product_url'] = $slug !== '' ? url('slugweb', ['slug' => $slug]) : '';
            $item['photo_url'] = $photo !== '' ? assets_photo('product', $thumbSize, $photo, 'thumbs') : $fallbackPhoto;
            return $item;
        }, (array) ($result['items'] ?? []));

        return response()->json($result);
    }

    private function loadRecommendedProducts(array $items)
    {
        $ids = [];
        foreach ($items as $item) {
            $pid = (int) ($item['product_id'] ?? 0);
            if ($pid > 0) {
                $ids[] = $pid;
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return collect();
        }

        $products = ProductModel::select(
            'name' . $this->lang,
            'photo',
            'icon',
            'desc' . $this->lang,
            'slug' . $this->lang,
            'regular_price',
            'sale_price',
            'discount',
            'id',
            'type',
            'properties'
        )
            ->with(['getPhotos' => function ($query) {
                $query->where('type', 'san-pham')->orderBy('numb', 'asc');
            }])
            ->where('type', 'san-pham')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $ordered = collect();
        foreach ($ids as $id) {
            if ($products->has($id)) {
                $ordered->push($products->get($id));
            }
        }
        return $ordered;
    }
}
