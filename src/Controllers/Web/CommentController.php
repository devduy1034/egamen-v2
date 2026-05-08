<?php

namespace LARAVEL\Controllers\Web;

use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Models\CommentModel;
use LARAVEL\Models\GalleryModel;
use LARAVEL\Models\MemberModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\OrdersModel;
use LARAVEL\Traits\TraitSave;
use View;

class CommentController extends Controller
{
    private $errors = [], $result = [], $response = [];
    private $upload;

    use TraitSave;

    public function handle($action, Request $request): void
    {
        match ($action) {
            'add-comment' => $this->addComment($request),
            'reply-comment' => $this->replyComment($request),
            'load-comment' => $this->loadComment($request),
            default => 'unknown',
        };
    }

    public function addComment(Request $request)
    {
        $data = !empty($request->dataReview) ? $request->dataReview : null;
        $uploadedFiles = $request->file('review-file-photo');
        if (!empty($uploadedFiles) && !is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }
        $uploadedFiles = is_array($uploadedFiles) ? array_values(array_filter($uploadedFiles)) : [];

        if (!empty($data)) {
            foreach ($data as $column => $value) {
                $data[$column] = htmlspecialchars(Func::sanitize($value));
            }

            $this->hydrateAuthorFromSession($data);
            $isVerifiedPurchase = \LARAVEL\Helpers\Comment::getInstance()->isVerifiedPurchaseComment($data);

            if (isset($data['star']) && empty($data['star'])) {
                $this->errors[] = 'Chưa chọn đánh giá sao';
            }
            if (isset($data['star']) && !empty($data['star']) && !Func::isNumber($data['star'])) {
                $this->errors[] = 'Đánh giá sao không hợp lệ';
            }
            if (isset($data['title']) && empty($data['title'])) {
                $this->errors[] = 'Chưa nhập tiêu đề đánh giá';
            }
            if (!empty($uploadedFiles) && count($uploadedFiles) > 3) {
                $this->errors[] = 'Hình ảnh không được vượt quá 3 hình';
            }

            if (empty($this->errors)) {
                $data['date_posted'] = time();
                if ($isVerifiedPurchase) {
                    $data['status'] = 'hienthi,damuahang';
                }
                $itemSave = CommentModel::create($data);

                if (!empty($itemSave)) {
                    $id = $itemSave->id;

                    if (!empty($uploadedFiles)) {
                        $this->insertImges(GalleryModel::class, $request, $uploadedFiles, $id, 'comment', $data['type'], $data['type'], 'photo');
                    }

                    if ($isVerifiedPurchase) {
                        $this->result = [
                            'auto_approved' => true,
                            'message' => 'Đánh giá đã được hiển thị ngay vì tài khoản này đã mua sản phẩm.',
                        ];
                    }
                }
            }
        } else {
            $this->errors[] = 'Dữ liệu không hợp lệ';
        }

        echo $this->response();
    }

    public function replyComment(Request $request)
    {
        $data = !empty($request->dataReview) ? $request->dataReview : null;

        if (!empty($data)) {
            foreach ($data as $column => $value) {
                $data[$column] = htmlspecialchars(Func::sanitize($value));
            }

            $this->hydrateAuthorFromSession($data);
            $isVerifiedPurchase = \LARAVEL\Helpers\Comment::getInstance()->isVerifiedPurchaseComment($data);

            if (isset($data['title']) && empty($data['title'])) {
                $this->errors[] = 'Chưa nhập tiêu đề đánh giá';
            }

            if (empty($this->errors)) {
                $data['date_posted'] = time();
                if ($isVerifiedPurchase) {
                    $data['status'] = 'hienthi,damuahang';
                }
                CommentModel::create($data);
            }
        } else {
            $this->errors[] = 'Dữ liệu không hợp lệ';
        }

        echo $this->response();
    }

    public function loadComment(Request $request)
    {
        $data = !empty($request->dataLoad) ? $request->dataLoad : null;

        if (!empty($data)) {
            $rowComment = CommentModel::select('*')
                ->where('type', $data['type'])
                ->where('id_parent', 0)
                ->where('id_variant', $data['id'])
                ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
                ->skip($data['limit'])->take(2)
                ->get();

            if (($data['limit'] + 2) >= $data['count']) {
                $limit = $data['count'];
                $this->result['pageout'] = true;
            } else {
                $limit = $data['limit'] + 2;
            }

            $this->result['limit'] = $limit;
            $this->result['view'] = View::render('component.comment.loadcomment', ['list' => $rowComment]);
        } else {
            $this->errors[] = 'Dữ liệu không hợp lệ';
        }

        echo $this->response();
    }

    private function response()
    {
        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        } else if (!empty($this->result)) {
            $response['result'] = $this->result;
        } else {
            $response['success'] = true;
        }

        return json_encode($response);
    }

    private function hydrateAuthorFromSession(array &$data): void
    {
        $member = $this->resolveLoggedInMember();

        if (empty($member)) {
            return;
        }

        $data['fullname'] = trim((string) ($member->fullname ?? $member->username ?? session()->get('member_name', '')));
        $data['phone'] = trim((string) ($member->phone ?? ''));
        $data['email'] = trim((string) ($member->email ?? ''));
    }

    private function resolveLoggedInMember(): ?MemberModel
    {
        $memberSession = session()->get('member');
        $memberId = is_array($memberSession) ? (int) ($memberSession['member'] ?? 0) : (int) $memberSession;

        if (empty($memberId)) {
            return null;
        }

        return MemberModel::find($memberId);
    }

    private function hasPurchasedProduct(int $memberId = 0, int $productId = 0): bool
    {
        if ($memberId <= 0 || $productId <= 0) {
            return false;
        }

        $deliveredStatusId = $this->resolveDeliveredOrderStatusId();
        if ($deliveredStatusId <= 0) {
            return false;
        }

        $query = OrdersModel::select('order_status', 'order_detail')
            ->where('id_user', $memberId)
            ->where('order_status', $deliveredStatusId);

        $orders = $query->orderBy('id', 'desc')->get();
        foreach ($orders as $order) {
            $orderItems = is_array($order->order_detail ?? null) ? array_values($order->order_detail) : [];
            foreach ($orderItems as $item) {
                $orderedProductId = (int) data_get($item, 'options.itemProduct.id', 0);
                if ($orderedProductId === $productId) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resolveDeliveredOrderStatusId(): int
    {
        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        if (empty($statuses)) {
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
                        return (int) ($status->id ?? 0);
                    }
                }
            }
        }

        return 0;
    }

    private function resolveCanceledOrderStatusId(): int
    {
        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        if (empty($statuses)) {
            return 0;
        }

        $keywordGroups = [
            ['huy don', 'da huy', 'huy'],
            ['cancel', 'cancelled', 'canceled'],
        ];

        foreach ($keywordGroups as $keywords) {
            foreach ($statuses as $status) {
                $name = $this->normalizeText((string) ($status->namevi ?? ''));
                foreach ($keywords as $keyword) {
                    if ($keyword !== '' && strpos($name, $keyword) !== false) {
                        return (int) ($status->id ?? 0);
                    }
                }
            }
        }

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
