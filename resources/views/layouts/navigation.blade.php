<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-semibold" href="{{ route('projects.index') }}">
            {{ config('app.name', 'Issue Tracker') }}
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('projects.*') ? ' active' : '' }}"
                       href="{{ route('projects.index') }}">
                        Projects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('issues.*') ? ' active' : '' }}"
                       href="{{ route('issues.index') }}">
                        Issues
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ request()->routeIs('tags.*') ? ' active' : '' }}"
                       href="{{ route('tags.index') }}">
                        Tags
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                {{ __('Profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</nav>
