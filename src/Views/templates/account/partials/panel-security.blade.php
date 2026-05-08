<section class="account-panel {{ $activeSection === 'security' ? 'is-active' : '' }}">
    <div class="account-panel__head">
        <h2 class="account-panel__title">Bảo mật tài khoản</h2>
    </div>

    <div class="account-security-tabs js-security-tabs">
        <div class="account-security-tablist" role="tablist" aria-label="Security tabs">
            <button type="button" class="account-security-tab is-active" role="tab" aria-selected="true" data-target="#security-tab-password">
                Đổi mật khẩu
            </button>
            <button type="button" class="account-security-tab" role="tab" aria-selected="false" data-target="#security-tab-sessions">
                Phiên đăng nhập
            </button>
            <button type="button" class="account-security-tab" role="tab" aria-selected="false" data-target="#security-tab-google">
                Liên kết Google
            </button>
        </div>

        <div class="account-security-panes">
            <article id="security-tab-password" class="account-security-card account-security-pane is-active" role="tabpanel">
                <h3>Đổi mật khẩu</h3>
                <form method="post" action="{{ url('user.account.password.change') }}">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    <div class="account-password-wrap mb-2">
                        <input id="current-password-account" class="form-control" type="password" name="current_password" placeholder="Mật khẩu hiện tại" autocomplete="current-password" maxlength="128" required>
                        <button class="account-password-toggle js-toggle-password" type="button" data-target="#current-password-account" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="account-password-wrap mb-2">
                        <input id="new-password-account" class="form-control js-new-password-check" type="password" name="new_password" placeholder="Mật khẩu mới" autocomplete="new-password" minlength="8" maxlength="128" required>
                        <button class="account-password-toggle js-toggle-password" type="button" data-target="#new-password-account" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <ul class="account-password-rules js-password-rules mb-2">
                        <li class="account-password-rule" data-rule="length">Tối thiểu 8 ký tự</li>
                        <li class="account-password-rule" data-rule="upper">Có chữ in hoa (A-Z)</li>
                        <li class="account-password-rule" data-rule="lower">Có chữ thường (a-z)</li>
                        <li class="account-password-rule" data-rule="digit">Có số (0-9)</li>
                        <li class="account-password-rule" data-rule="special">Có ký tự đặc biệt (!@#$...)</li>
                        <li class="account-password-rule" data-rule="not_same_current">Không trùng mật khẩu hiện tại</li>
                        <li class="account-password-rule" data-rule="confirm_match">Khớp với ô nhập lại mật khẩu</li>
                    </ul>
                    <div class="account-password-wrap mb-2">
                        <input id="new-password-confirm-account" class="form-control js-new-password-confirm-check" type="password" name="new_password_confirm" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" minlength="8" maxlength="128" required>
                        <button class="account-password-toggle js-toggle-password" type="button" data-target="#new-password-confirm-account" aria-label="Hiện mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <button class="btn account-btn account-btn--outline" type="submit">Đổi mật khẩu</button>
                </form>
            </article>

            <article id="security-tab-sessions" class="account-security-card account-security-pane" role="tabpanel">
                <h3>Phiên đăng nhập</h3>
                @forelse (($loginSessions ?? []) as $loginSession)
                    <div class="account-session-item">
                        <p class="mb-1"><strong>{{ !empty($loginSession['user_agent']) ? \Illuminate\Support\Str::limit($loginSession['user_agent'], 80) : 'Thiết bị không rõ' }}</strong></p>
                        <p class="mb-1">IP: {{ $loginSession['ip'] ?? '-' }}</p>
                        <p class="mb-1">Lần cuối: {{ !empty($loginSession['last_seen']) ? date('d/m/Y H:i', (int) $loginSession['last_seen']) : '-' }}</p>
                        @if (($loginSession['session_id'] ?? '') !== ($currentSessionId ?? ''))
                            <form method="post" action="{{ url('user.account.session.revoke') }}">
                                <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="session_id" value="{{ $loginSession['session_id'] ?? '' }}">
                                <button class="btn account-btn account-btn--outline" type="submit">Đăng xuất phiên này</button>
                            </form>
                        @else
                            <span class="account-badge">Phiên hiện tại</span>
                        @endif
                    </div>
                @empty
                    <p>Chưa có dữ liệu phiên đăng nhập.</p>
                @endforelse

                <form method="post" action="{{ url('user.account.session.revoke.others') }}">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                    <button class="btn account-btn account-btn--outline" type="submit">Đăng xuất tất cả thiết bị khác</button>
                </form>
            </article>

            <article id="security-tab-google" class="account-security-card account-security-pane" role="tabpanel">
                <h3>Liên kết Google</h3>
                <p>{{ !empty($googleLinked) ? 'Đã liên kết Google.' : 'Chưa liên kết Google.' }}</p>
                @if (!empty($googleLinked))
                    <form method="post" action="{{ url('user.account.google.unlink') }}" class="js-google-unlink-form">
                        <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                        <div class="account-password-wrap mb-2 js-google-unlink-fields" style="display:none;">
                            <input class="form-control" type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại để hủy liên kết" autocomplete="current-password" maxlength="128" required>
                        </div>
                        <p class="mb-2 text-muted js-google-unlink-fields" style="display:none;">Khuyến nghị: hãy chắc chắn bạn đăng nhập được bằng email/mật khẩu trước khi hủy liên kết Google.</p>
                        <button class="btn account-btn account-btn--outline js-google-unlink-toggle" type="button">Hủy liên kết</button>
                        <button class="btn account-btn account-btn--outline js-google-unlink-submit" type="submit" style="display:none;">Xác nhận hủy liên kết</button>
                    </form>
                @else
                    <a class="btn account-btn account-btn--outline" href="{{ url('user.login.google') }}">Lien ket</a>
                @endif
            </article>
        </div>
    </div>
</section>
