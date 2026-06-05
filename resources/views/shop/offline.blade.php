<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @lang('global.offline_title')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="color-scheme" content="dark light">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{height:100%}
        body{
            height:100%;
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
            background:#f9fafb;color:#111827;display:flex;flex-direction:column
        }
        .dark body,
        body.dark{
            background:#030712;color:#f3f4f6
        }
        .page{flex:1;display:flex;align-items:center;justify-content:center;padding:3rem 1.5rem}
        .card{text-align:center;max-width:20rem;width:100%}
        .logo{display:inline-block;margin-bottom:2.5rem}
        .logo img{height:2.5rem;display:block;margin:0 auto}
        .icon-box{
            width:4rem;height:4rem;margin:0 auto 1.5rem;
            border-radius:1rem;
            background:#fffbeb;border:1px solid #fde68a;
            display:flex;align-items:center;justify-content:center
        }
        .dark .icon-box{
            background:rgba(251,191,36,0.1);border-color:rgba(217,119,6,0.3)
        }
        .icon-box svg{width:2rem;height:2rem;color:#f59e0b}
        .dark .icon-box svg{color:#fbbf24}
        h1{font-size:1.25rem;font-weight:600;margin-bottom:.5rem}
        p{font-size:.875rem;color:#6b7280;line-height:1.625;margin-bottom:2rem;max-width:18rem;margin-left:auto;margin-right:auto}
        .dark p{color:#9ca3af}
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
            width:100%;padding:.75rem 1.5rem;
            background:#4f46e5;color:#fff;
            font-size:.875rem;font-weight:500;
            border:none;border-radius:.75rem;
            cursor:pointer;transition:background .15s
        }
        .btn:hover{background:#4338ca}
        .btn:active{background:#3730a3}
        .btn svg{width:1rem;height:1rem;flex-shrink:0}
        footer{padding:1.5rem;text-align:center}
        footer p{font-size:.75rem;color:#9ca3af;margin:0;max-width:none}
        .dark footer p{color:#4b5563}
        @@media(min-width:640px){.btn{width:auto}}
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}">
            </a>

            <div class="icon-box">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </div>

            <h1>{{ __('global.offline_title') }}</h1>

            <p>{{ __('global.offline_desc') }}</p>

            <button onclick="location.reload()" class="btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                </svg>
                {{ __('global.try_again') }}
            </button>
        </div>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('global.rights_reserved') }}</p>
    </footer>
</body>
</html>