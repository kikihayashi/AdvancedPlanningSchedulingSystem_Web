<div class="top-sidebar-div">
    <div id="top-home-div">
        <a href="{{ route('home') }}">
            APS系統
        </a>
    </div>
    <div id="top-user-div">
        <ul>
            @guest
            @if (Route::has('login'))
            <!-- <li>
                <a onMouseOver="this.style.color='#F0F11C'" onMouseOut="this.style.color='#9A9DA0'"
                    href="{{ route('login') }}">{{ __('登入') }}</a>
            </li> -->
            @endif

            @if (Route::has('registerUser'))
            <!-- <li>
                <a onMouseOver="this.style.color='#F0F11C'" onMouseOut="this.style.color='#9A9DA0'"
                    href="{{route('registerUser')}}">{{ __('註冊') }}</a>
            </li> -->
            @endif

            @else
            <li>
                <a onMouseOver="this.style.color='#F0F11C'" onMouseOut="this.style.color='#9A9DA0'" aria-haspopup="true"
                    aria-expanded="false" v-pre href="{{route('UserController.editUserPage', Auth::user()->id)}}">
                    {{Auth::user()->name}}
                </a>
            </li>
            <li>
                <a onMouseOver="this.style.color='#F0F11C'" onMouseOut="this.style.color='#9A9DA0'"
                    href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    {{ __('登出') }}
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </a>
            </li>
            @endguest
        </ul>
    </div>
</div>