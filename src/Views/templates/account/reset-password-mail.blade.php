<table align="center" bgcolor="#dcf0f8" border="0" cellpadding="0" cellspacing="0"
    style="margin:0;padding:0;background-color:#f2f2f2;width:100%!important;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px"
    width="100%">
    <tbody>
        @component('component.email.header')
        @endcomponent
        <tr>
            <td align="center"
                style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal"
                valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="600">
                    <tbody>
                        <tr style="background:#fff">
                            <td align="left" height="auto" style="padding:18px" width="600">
                                <h2 style="margin:0 0 8px;color:#1f2e46;">Đặt lại mật khẩu</h2>
                                <p style="margin:0 0 8px;">Xin chào {{ $params['fullname'] ?? 'ban' }},</p>
                                <p style="margin:0 0 16px;">
                                    Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.
                                    Link này có hiệu lực trong {{ $params['expire_minutes'] ?? 60 }} phút.
                                </p>
                                <p style="margin:0 0 16px;">
                                    <a href="{{ $params['reset_link'] ?? '#' }}"
                                        style="display:inline-block;background:#2f5b8f;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;font-weight:700;">
                                        Đặt lại mật khẩu
                                    </a>
                                </p>
                                <p style="margin:0;">
                                    Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        @component('component.email.footer')
        @endcomponent
    </tbody>
</table>
