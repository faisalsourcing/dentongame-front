<div class="top-box">
    <div class="row">
        <div class="col-12 col-md-8">

        </div>
        <div class="col-12 col-md-4">
            <div class="user-info">
                <div class="team-info">
                    <div class="dropdown">
                        <div type="button" data-toggle="dropdown">
                            <span><img class="avatar" src="{{ asset('assets/images/avatar.png') }}" alt=""></span>
                            <div class="info">
                                <h3>Team</h3>
                                <h4>{{ Auth::user()->name }}</h4>
                            </div>
                        </div>
                        <ul class="dropdown-menu">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>