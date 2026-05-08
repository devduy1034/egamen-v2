<?php

namespace LARAVEL\Middlewares;

use LARAVEL\Models\MemberModel;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class LoginUser implements IMiddleware
{
    public function handle(Request $request): void
    {
        if (session()->has('member')) {
            $memberSession = session()->get('member');
            if (is_array($memberSession)) {
                $memberSession = $memberSession['member'] ?? 0;
            }
            $memberId = (int) $memberSession;
            if ($memberId > 0) {
                $member = MemberModel::where('id', $memberId)->first();
                if (!empty($member) && strtolower(trim((string) ($member->status ?? ''))) === 'locked') {
                    session()->unset('member');
                    session()->unset('member_name');
                    response()->redirect(url('user.login', null, ['social_error' => 'account_locked']));
                    return;
                }
            }
            return;
        }

        response()->redirect(url('user.login', null, ['redirect' => PeceeRequest()->getUrl()->getRelativeUrl()]));
    }
}
