<?php



namespace LARAVEL\Helpers;

use LARAVEL\Core\Singleton;
use LARAVEL\Models\CommentModel;
use LARAVEL\Models\PhotoModel;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\MemberModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\OrdersModel;
use Func;

class Comment
{
    use Singleton;

    protected array $verifiedPurchaseMemberCache = [];
    protected array $verifiedPurchaseOrderCache = [];
    protected ?int $deliveredOrderStatusIdCache = null;

    public function countStar($id = 0, $type = '')
    {
        $count = array();

        for ($i = 1; $i <= 5; $i++) {
            $count[$i] = $this->getStar($id, $type, $i);
        }

        return json_encode($count);
    }

    private function getStar($id, $type, $star = 1)
    {
        $row = CommentModel::selectRaw('count(*) as num')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->where('id_variant', $id)
            ->where('type',  $type)
            ->where('star',  $star)
            ->first();

        return (!empty($row)) ? $row['num'] : 0;
    }

    public function totalByID($id_variant = 0, $type = '', $is_admin = false)
    {
        $query = CommentModel::select('count(id) as num')
            ->where('id_parent', 0)
            ->where('id_variant', $id_variant)
            ->where('type',  $type);
        if (empty($is_admin)) $query->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
        $row = $query->orderBy('id', 'desc')
            ->first();

        return (!empty($row)) ? $row['num'] : 0;
    }

    public function newPost($id_variant = 0, $type = '', $status = '')
    {
        $row = CommentModel::select('id_variant')
            ->where('id_variant', $id_variant)
            ->where('type',  $type)
            ->whereRaw("FIND_IN_SET(?,status)", [$status])
            ->orderBy('id', 'desc')
            ->get();

        return (!empty($row)) ? count($row) : 0;
    }

    public function photo($id_parent = 0, $type = '')
    {
        $rows = GalleryModel::select('id', 'photo')
            ->where('type', $type)
            ->where('com', 'comment')
            ->where('id_parent', $id_parent)
            ->get();

        return $rows;
    }

    public function video($id_parent = 0)
    {
        $row = PhotoModel::select('id', 'photo')
            ->where('type', 'comment')
            ->where('com', 'video-comment')
            ->where('id_parent', $id_parent)
            ->first();
        return $row;
    }

    public function perScore($id = 0, $type = '', $num = 1)
    {

        return (!empty($this->total($id, $type))) ? round((json_decode($this->countStar($id, $type), true)[$num] * 100) / $this->total($id, $type), 1) : 0;
    }

    private function totalStar($id = 0, $type = '')
    {
        $row = CommentModel::selectRaw('sum(star) as total_star')
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->where('id_variant', $id)
            ->where('type',  $type)
            ->first();
        return $row['total_star'];
    }

    public function countReply($id = 0, $check = '')
    {
        $query = CommentModel::selectRaw('count(id) as sum')
            ->where('id_parent', $id);
        if (!empty($check)) $query->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
        if (empty($check)) $query->whereRaw("NOT FIND_IN_SET(?,status)", ['hienthi']);
        $row = $query->first();

        return $row['sum'];
    }

    function total($id = 0, $type = '', $is_admin = false)
    {
        $query = CommentModel::where('id_parent', 0)
            ->where('id_variant', $id)
            ->where('type', $type);

        if (empty($is_admin)) {
            $query->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
        }

        return $query->count();
    }

    public function avgPoint($id = 0, $type = '', $is_admin = false)
    {
        return (!empty($this->total($id, $type))) ? round((($this->totalStar($id, $type)) / $this->total($id, $type)), 1) : 0;
    }

    public function avgStar($id = 0, $type = '')
    {
        return (!empty($this->total($id, $type))) ? ($this->totalStar($id, $type) * 100) / ($this->total($id, $type) * 5) : 0;
    }

    public function scoreStar($star = 0)
    {
        return (!empty($star)) ? ($star * 100) / 5 : 0;
    }

    public function isVerifiedPurchaseComment($comment = null): bool
    {
        $statusList = array_filter(array_map('trim', explode(',', (string) data_get($comment, 'status', ''))));
        if (in_array('damuahang', $statusList, true)) {
            return true;
        }

        $productId = (int) data_get($comment, 'id_variant', 0);
        if ($productId <= 0) {
            return false;
        }

        $memberId = $this->resolveMemberIdForComment($comment);
        if ($memberId <= 0) {
            return false;
        }

        $cacheKey = $memberId . ':' . $productId;
        if (array_key_exists($cacheKey, $this->verifiedPurchaseOrderCache)) {
            return $this->verifiedPurchaseOrderCache[$cacheKey];
        }

        $deliveredStatusId = $this->resolveDeliveredOrderStatusId();
        if ($deliveredStatusId <= 0) {
            $this->verifiedPurchaseOrderCache[$cacheKey] = false;
            return false;
        }

        $orders = OrdersModel::select('order_detail')
            ->where('id_user', $memberId)
            ->where('order_status', $deliveredStatusId)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($orders as $order) {
            $orderItems = is_array($order->order_detail ?? null) ? array_values($order->order_detail) : [];
            foreach ($orderItems as $item) {
                $orderedProductId = (int) data_get($item, 'options.itemProduct.id', data_get($item, 'id', 0));
                if ($orderedProductId === $productId) {
                    $this->verifiedPurchaseOrderCache[$cacheKey] = true;
                    return true;
                }
            }
        }

        $this->verifiedPurchaseOrderCache[$cacheKey] = false;
        return false;
    }


    function subName($str)
    {
        $words = Func::changeTitle($str);
        $words = explode(' ', $words);

        $firstLetters = array_map(function ($word) {
            return $word[0];
        }, $words);

        return implode('', $firstLetters);
    }


    public function timeAgo($time = 0)
    {
        $result = '';
        $lang = [
            'now' => 'Vài giây trước',
            'ago' => 'trước',
            'vi' => [
                'y' => 'năm',
                'm' => 'tháng',
                'd' => 'ngày',
                'h' => 'giờ',
                'm' => 'phút',
                's' => 'giây'
            ]
        ];

        $ago = time() - $time;

        if ($ago < 1) {
            $result = $lang['now'];
        } else {
            $unit = [
                365 * 24 * 60 * 60  =>  'y',
                30 * 24 * 60 * 60  =>  'm',
                24 * 60 * 60  =>  'd',
                60 * 60  =>  'h',
                60  =>  'm',
                1  =>  's'
            ];

            foreach ($unit as $secs => $key) {
                $time = $ago / $secs;

                if ($time >= 1) {
                    $time = round($time);
                    $result = $time . ' ' . ($time > 1 ? $lang['vi'][$key] : $lang['vi'][$key]) . ' ' . $lang['ago'];
                    break;
                }
            }
        }

        return $result;
    }

    private function resolveMemberIdForComment($comment = null): int
    {
        $email = trim((string) data_get($comment, 'email', ''));
        if ($email !== '') {
            $cacheKey = 'email:' . strtolower($email);
            if (array_key_exists($cacheKey, $this->verifiedPurchaseMemberCache)) {
                return $this->verifiedPurchaseMemberCache[$cacheKey];
            }

            $memberId = (int) (MemberModel::where('email', $email)->value('id') ?? 0);
            $this->verifiedPurchaseMemberCache[$cacheKey] = $memberId;
            if ($memberId > 0) {
                return $memberId;
            }
        }

        $phone = trim((string) data_get($comment, 'phone', ''));
        if ($phone !== '') {
            $cacheKey = 'phone:' . $phone;
            if (array_key_exists($cacheKey, $this->verifiedPurchaseMemberCache)) {
                return $this->verifiedPurchaseMemberCache[$cacheKey];
            }

            $memberId = (int) (MemberModel::where('phone', $phone)->value('id') ?? 0);
            $this->verifiedPurchaseMemberCache[$cacheKey] = $memberId;
            return $memberId;
        }

        return 0;
    }

    private function resolveDeliveredOrderStatusId(): int
    {
        if ($this->deliveredOrderStatusIdCache !== null) {
            return $this->deliveredOrderStatusIdCache;
        }

        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        if (empty($statuses)) {
            $this->deliveredOrderStatusIdCache = 0;
            return 0;
        }

        $keywordGroups = [
            ['da giao', 'giao hang thanh cong', 'giao thanh cong', 'hoan tat', 'hoan thanh'],
            ['delivered', 'completed'],
        ];

        foreach ($keywordGroups as $keywords) {
            foreach ($statuses as $status) {
                $name = $this->normalizeText((string) ($status->namevi ?? ''));
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && strpos($name, $keyword) !== false) {
                        $this->deliveredOrderStatusIdCache = (int) ($status->id ?? 0);
                        return $this->deliveredOrderStatusIdCache;
                    }
                }
            }
        }

        $this->deliveredOrderStatusIdCache = 0;
        return 0;
    }

    private function normalizeText(string $value = ''): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if ($value === '') {
            return '';
        }

        $replacements = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a', 'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];

        $value = strtr($value, $replacements);
        $value = preg_replace('/[^a-z0-9]+/', ' ', (string) $value);
        $value = preg_replace('/\s+/', ' ', (string) $value);

        return trim((string) $value);
    }
}
