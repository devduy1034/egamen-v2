@php
    $langKey = (string) ($lang ?? 'vi');
    $siteName = trim((string) ($setting['name' . $langKey] ?? $setting['namevi'] ?? 'Website'));
@endphp

<div class="chat-products-widget"
    data-chat-products-root
    data-chat-endpoint="{{ url('api.chat-products') }}"
    data-chat-site-name="{{ $siteName }}">
    <div class="chat-products-widget__launcher" data-chat-products-launcher>
        <button type="button" class="chat-products-widget__dismiss" data-chat-products-dismiss title="Tắt gợi ý">×</button>
        <div class="chat-products-widget__teaser" data-chat-products-open>
            <div class="chat-products-widget__teaser-title">
                <span class="chat-products-widget__brand-dot">✶</span>
                <strong>{{ $siteName }}</strong>
            </div>
            <p>Em rất sẵn lòng hỗ trợ Anh/Chị.</p>
        </div>
        <button type="button" class="chat-products-widget__fab" data-chat-products-open>
            <span class="chat-products-widget__fab-icon">AI</span>
            <span class="chat-products-widget__fab-text">Trợ lý AI</span>
        </button>
    </div>

    <div class="chat-products-widget__panel" data-chat-products-panel hidden>
        <div class="chat-products-widget__header">
            <div class="chat-products-widget__header-main">
                <span class="chat-products-widget__brand-dot">✶</span>
                <strong>{{ $siteName }}</strong>
            </div>
            <div class="chat-products-widget__header-actions">
                <button type="button" data-chat-products-reset title="Làm mới">↻</button>
                <button type="button" data-chat-products-minimize title="Thu gọn">−</button>
            </div>
        </div>
        <div class="chat-products-widget__messages" data-chat-products-messages>
            <div class="chat-products-widget__bubble is-assistant">
                Xin chào Anh/Chị! Em là trợ lý AI của {{ $siteName }}.
            </div>
            <div class="chat-products-widget__bubble is-assistant">
                Em rất sẵn lòng hỗ trợ Anh/Chị.
            </div>
        </div>
        <form class="chat-products-widget__form" data-chat-products-form>
            <input type="text" data-chat-products-input maxlength="160"
                placeholder="Nhập tin nhắn..." autocomplete="off">
            <button type="submit" data-chat-products-submit aria-label="Gửi tin nhắn">
                <i class="bi bi-send-fill" aria-hidden="true"></i>
            </button>
        </form>
        <button type="button" class="chat-products-widget__human" data-chat-products-human hidden>
            Liên hệ tư vấn viên
        </button>
        <p class="chat-products-widget__note">Thông tin chỉ mang tính tham khảo, được tư vấn bởi Trí Tuệ Nhân Tạo</p>
    </div>
</div>
