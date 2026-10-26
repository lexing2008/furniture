<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('Подтвердите регистрацию') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;">
    {{-- Preheader: the grey line next to the subject in the inbox --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
        {{ __('Осталось подтвердить адрес электронной почты') }}{{ $company ? __(' — кабинет компании «:company»', ['company' => $company]) : '' }}.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                    <tr>
                        <td style="background-color:#0073b8;padding:28px 32px;">
                            <div style="font-size:22px;font-weight:700;letter-spacing:.5px;color:#ffffff;">{{ $appName }}</div>
                            <div style="margin-top:4px;font-size:13px;color:#cfe6f5;">{{ __('Мебель от проверенных компаний') }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <h1 style="margin:0 0 16px;font-size:22px;line-height:1.3;color:#111827;">{{ __('Здравствуйте, :name!', ['name' => $name]) }}</h1>

                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
                                @if ($company)
                                    {{ __('Вы зарегистрировали кабинет компании «:company».', ['company' => $company]) }}
                                @else
                                    {{ __('Вы зарегистрировались на сайте :app.', ['app' => $appName]) }}
                                @endif
                                {{ __('Чтобы завершить регистрацию, подтвердите адрес электронной почты.') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:8px 32px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius:8px;background-color:#0073b8;">
                                        <a href="{{ $url }}" target="_blank"
                                           style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                                            {{ __('Подтвердить почту') }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px;">
                            @php($minuteWord = trans_choice(__('{1}минуту|[2,4]минуты|[5,*]минут'), $expire))
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#6b7280;">
                                {{ __('Ссылка действует :expire :unit.', ['expire' => $expire, 'unit' => $minuteWord]) }}
                                {{ __('Если вы не регистрировались, просто проигнорируйте это письмо — ничего не изменится.') }}
                            </p>

                            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 6px;font-size:13px;color:#9ca3af;">{{ __('Если кнопка не нажимается, скопируйте адрес в браузер:') }}</p>
                                <p style="margin:0;font-size:12px;line-height:1.5;word-break:break-all;"><a href="{{ $url }}" style="color:#0073b8;">{{ $url }}</a></p>
                            </div>
                        </td>
                    </tr>
                </table>

                <p style="max-width:560px;margin:20px 0 0;font-size:12px;line-height:1.5;color:#9ca3af;text-align:center;">
                    {{ __('© :year :app. Это автоматическое письмо, отвечать на него не нужно.', ['year' => date('Y'), 'app' => $appName]) }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
