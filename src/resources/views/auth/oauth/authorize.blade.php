<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Authorize application') }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #fafafa; color: #171717; margin: 0; padding: 1.5rem; line-height: 1.5; }
        .card { max-width: 32rem; margin: 2rem auto; padding: 2rem; background: #fff; border: 1px solid #e5e5e5; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        h1 { font-size: 1.25rem; font-weight: 600; margin: 0 0 0.5rem; }
        p { margin: 0.5rem 0; color: #525252; font-size: 0.875rem; }
        ul { margin: 0.5rem 0 0; padding-left: 1.25rem; font-size: 0.875rem; color: #404040; }
        .actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
        button { font-size: 0.875rem; font-weight: 500; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; border: none; }
        .primary { background: #171717; color: #fff; }
        .secondary { background: #fff; color: #171717; border: 1px solid #d4d4d4; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ __('Authorize application') }}</h1>
        <p>
            {{ __(':client is requesting permission to access your account.', ['client' => $client->name]) }}
        </p>

        @if (count($scopes) > 0)
            <p><strong>{{ __('This application will be able to:') }}</strong></p>
            <ul>
                @foreach ($scopes as $scope)
                    <li>{{ $scope->description ?? $scope->id }}</li>
                @endforeach
            </ul>
        @endif

        <div class="actions">
            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="primary">{{ __('Authorize') }}</button>
            </form>

            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="secondary">{{ __('Cancel') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
