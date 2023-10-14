<div>
    {{ Auth::user()->name ?? '' }}

    <a href="{{ route('profile.edit') ?? '#' }}">{{ __('Profile') }}</a>

    <form method="POST" action="{{ route('logout') ?? '#' }}" style="display: inline;">
        @csrf

        <a href="{{ route('logout') ?? '#' }}" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</a>
    </form>
</div>
