<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.title') }}</title>
    <style>
        :root {
            color-scheme: light dark;
        }

        html {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f8fafc;
            color: #0f172a;
            transition: background-color .3s ease, color .3s ease;
        }

        html.dark {
            background: #020617;
            color: #f8fafc;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            width: 100%;
            max-width: 760px;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        html.dark .card {
            background: rgba(15, 23, 42, 0.88);
            border-color: rgba(255, 255, 255, 0.08);
        }

        h1 {
            margin: 0 0 1rem;
            font-size: clamp(2.2rem, 4vw, 3rem);
            line-height: 1.05;
        }

        p {
            margin: 0 0 1.5rem;
            font-size: 1.05rem;
            line-height: 1.75;
            max-width: 40rem;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            justify-content: {{ app()->getLocale() === 'ar' ? 'flex-end' : 'flex-start' }};
        }

        .button,
        .button-secondary {
            border: none;
            border-radius: 0.85rem;
            padding: 0.95rem 1.4rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform .15s ease, background-color .15s ease, color .15s ease;
        }

        .button:hover,
        .button-secondary:hover {
            transform: translateY(-1px);
        }

        .button {
            background: #2563eb;
            color: #ffffff;
        }

        .button-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        html.dark .button-secondary {
            background: #1f2937;
            color: #f8fafc;
        }

        .button-secondary[aria-current="true"] {
            opacity: 0.7;
        }

        .locale-label {
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            color: #475569;
        }

        html.dark .locale-label {
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ __('messages.welcome') }}</h1>
        <p>{{ __('messages.description') }}</p>
        <div class="locale-label">{{ __('messages.choose_language') }}</div>
        <div class="buttons">
            <a class="button-secondary" href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" aria-current="{{ app()->getLocale() === 'en' ? 'true' : 'false' }}">{{ __('messages.english') }}</a>
            <a class="button-secondary" href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}" aria-current="{{ app()->getLocale() === 'ar' ? 'true' : 'false' }}">{{ __('messages.arabic') }}</a>
            <button class="button" id="theme-toggle" type="button">{{ __('messages.dark_mode') }}</button>
        </div>
    </div>

    <script>
        const themeKey = 'elegant_store_theme';
        const button = document.getElementById('theme-toggle');
        const darkText = @json(__('messages.dark_mode'));
        const lightText = @json(__('messages.light_mode'));

        function setTheme(mode) {
            document.documentElement.classList.toggle('dark', mode === 'dark');
            button.textContent = mode === 'dark' ? lightText : darkText;
        }

        const savedTheme = localStorage.getItem(themeKey);
        const initialTheme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        setTheme(initialTheme);

        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem(themeKey, nextTheme);
            setTheme(nextTheme);
        });
    </script>
</body>
</html>
