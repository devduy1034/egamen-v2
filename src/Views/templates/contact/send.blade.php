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
                            <td align="left" height="auto" style="padding:15px" width="600">
                                <table width="100%">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h1
                                                    style="font-size:17px;font-weight:bold;color:#444;padding:0 0 5px 0;margin:0">
                                                    Kinh chao Quy khach</h1>
                                                <p
                                                    style="margin:4px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal">
                                                    Thong tin lien he cua quy khach da duoc tiep nhan. Chung toi se phan
                                                    hoi trong thoi gian som nhat.</p>
                                                <h3
                                                    style="font-size:13px;font-weight:bold;color:{emailColor};text-transform:uppercase;margin:20px 0 0 0;padding: 0 0 5px;border-bottom:1px solid #ddd">
                                                    Thong tin lien he
                                                </h3>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        @foreach ($params as $key => $value)
                                                            <tr>
                                                                <td style="padding:3px 0px;border-top:0;border-left:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#444;line-height:18px;font-weight:normal"
                                                                    valign="top">{{ $value }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
