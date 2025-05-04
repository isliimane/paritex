<!-- hide "visit store" button from movile view -->
<style>
    @media (max-width: 767px) {
        .icon-visible {
            display: none !important;
        }
    }


/* Icônes principales de la navbar */
.navbar .bx {
  color: rgb(9, 173, 42) !important; /* Bleu standard */
  transition: all 0.3s ease;
}

/* Icône du menu burger */
.navbar .bx-menu {
  color:  rgb(9, 173, 42)  !important;
}

/* Icône de notification */
.navbar .bx-bell {
  color: rgb(9, 173, 42)  !important;
}

/* Icône POS */
.navbar .bx-printer {
  color: rgb(9, 173, 42)  !important;
}

/* Icône "Visit Store" */
.navbar .bx-globe {
  color:   rgb(9, 173, 42)  !important;
}

/* Effet au survol des icônes */
.navbar .nav-link:hover .bx {
  color: rgb(9, 173, 42)  !important; /* Bleu plus foncé au survol */
}

/* Dropdown utilisateur - Icônes */
.dropdown-menu .bx {
  color: rgb(9, 173, 42)  !important;
  margin-right: 8px;
}

/* Icône de profil */
.dropdown-menu .bx-user {
  color: rgb(9, 173, 42) !important;
}

/* Icône des activités de connexion */
.dropdown-menu .bx-file {
  color: rgb(9, 173, 42)  !important;
}

/* Icône de déconnexion */
.dropdown-menu .bx-log-out {
  color: #e74c3c !important; /* Rouge pour la déconnexion */
}

/* Style du nom d'utilisateur dans la navbar */
.nav-link-user div {
  color: rgb(9, 173, 42)  !important;
  font-weight: 600;
}

/* Style des flags/langues */
.nav-link-flag img {
  border: 2px solid rgb(9, 173, 42) ;
  border-radius: 3px;
}

/* Style du dropdown devise */
.nav-link-flag .d-sm-none.d-lg-inline-block {
  color:rgb(255, 255, 255) !important;
  font-weight: 500;
}

/* Animation des icônes */
.navbar .bx {
  transition: transform 0.3s ease;
}

.navbar .nav-link:hover .bx {
  transform: scale(1.1);
}

/* Badge de notification */
.notification-toggle.beep::after {
  background-color: #e74c3c; /* Rouge pour les notifications non lues */
}

/* Style spécifique pour les écrans mobiles */
@media (max-width: 767px) {
  .navbar .bx {
    font-size: 1.4rem; /* Taille légèrement augmentée */
  }
  
  .nav-link-user div {
    font-size: 0.9rem;
  }
  .navbar-bg .navbar{ 
    color:rgb(62, 59, 59) !important; 
  }
}

 



.navbar-bg {
    background-color: #rgb(5, 20, 7)!important; /* Arrière-plan noir */
    
}

.main-navbar {
    background-color:rgb(5, 20, 7)!important; /* Noir légèrement transparent */
}



.navbar .nav-link div,
.navbar .nav-link i,
.navbar .dropdown-toggle div {
    color: #fff !important; /* Texte et icônes en blanc */
}

.dropdown-menu {
    background-color: #333 !important; /* Fond du menu déroulant plus foncé */
    color: #fff !important;
}

.dropdown-item {
    color: #fff !important;
}

.dropdown-item:hover {
    background-color: #444 !important; /* Couleur de survol */
}


.dropdown-list-content {
    background-color: #333 !important;
}

.dropdown-item-desc {
    color: #fff !important;
}

</style>
<!-- hide "visit store" button from movile view -->

<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline {{ $locale_language->text_direction == 'rtl' ? 'ml-auto' : 'mr-auto' }}">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="bx bx-menu"></i></a>
            </li>
        </ul>
    </form>

    <ul class="navbar-nav navbar-right">
        <!-- <li>
            <a href="{{ route('cache.clear') }}" class="btn btn-outline-danger btn-cache" tabindex="4">
                {{ __('Cache') }}<i class="bx bx-trash"></i>
            </a>
        </li> -->
      
        @if (!config('app.mobile_mode') || is_dir('resources/views/frontend'))
            <li class="icon-visible">
                <a href="{{ url('/') }}"
                    target="_blank" class="nav-link nav-link-lg" data-toggle="tooltip"
                    data-original-title="{{ __('Visit Store') }}"><i class="bx bx-globe"></i></a>
            </li>
        @endif

        <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
                class="nav-link notification-toggle nav-link-lg {{ $notificationCount > 0 ? 'beep' : '' }} "><i
                    class="bx bx-bell"></i></a>
            <div class="dropdown-menu dropdown-list dropdown-menu-right">
                <div class="dropdown-header">{{ __('Notifications') }}
                    <div class="float-right">
                        <a href="{{ route('mark.notification.seen') }}">{{ __('Mark All As Read') }}</a>
                    </div>
                </div>
                <div class="dropdown-list-content dropdown-list-icons">
                    @php
                        $user_type = authUser()->user_type != 'customer' ? authUser()->user_type : '';
                    @endphp
                    @foreach ($notifications as $notification)
                        <a href="{{ $notification->url != '' || $notification->url != null ? url($user_type . '/' . $notification->url) : 'javascript:void(0)' }}"
                            class="dropdown-item dropdown-item-unread notification-status"
                            data-notification="{{ json_encode($notification) }}">
                            <div
                                class="dropdown-item-icon {{ $notification->status == 'seen' ? 'bg-info' : 'bg-primary' }} text-white">
                                @if ($notification->status == 'seen')
                                    <i class="bx bx-check"></i>
                                @else
                                    <i class="bx bx-x"></i>
                                @endif
                            </div>
                            <div class="dropdown-item-desc">
                                {{ $notification->title }}
                                <div class="time {{ $notification->status == 'seen' ? '' : 'text-primary' }}">
                                    {{ Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</div>
                            </div>
                        </a>
                        <input type="hidden" id="path" value="{{ request()->path() }}" />
                    @endforeach
                </div>
                <div class="dropdown-footer text-center">
                    <a href="{{ route('notification.all') }}">{{ __('View All') }} <i
                            class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </li>
        <li class="dropdown">
                <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-flag">
                    {{--                $curr = authId() == 1 ? settingHelper('default_currency') : authUser()->currency_id; --}}

                    @php
                        $curr_id = 1;
                        $curr_name = 'US Dollar';
                        $curr_code = 'USD';
                        $curr_symbol = '$';
                        $currencies = App\Utility\AppSettingUtility::currencies()->where('status', 1);
                        $curr = settingHelper('default_currency');
                        $curr = $currencies->where('id', $curr)->first();
                        if ($curr) {
                            $curr_id = $curr->id;
                            $curr_name = $curr->name;
                            $curr_code = $curr->code;
                            $curr_symbol = $curr->symbol;
                        }
                    @endphp
                    <div class="d-sm-none d-lg-inline-block">{{ $curr_name }} ({{ $curr_symbol }})</div>
                </a>
                <input type="hidden" value="{{ $curr_code }}" id="active_currency">
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach ($currencies as $active_curr)
                        <a rel="alternate"
                            class="dropdown-item has-icon {{ $curr_id == $active_curr->id ? 'active' : '' }}"
                            href="{{ route('admin.change.currency', $active_curr->id) }}">
                            {{ $active_curr->name }} ({{ $active_curr->symbol }})
                        </a>
                    @endforeach
                </div>
        </li>
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-flag">
                @php
                    $lang = $active_languages->where('locale', app()->getLocale())->first();
                @endphp
                <img alt="image" src="{{ static_asset($lang->flag) }}" class="h-24 mr-1">
                <div class="d-sm-none d-lg-inline-block">{{ $lang->name }}</div>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
                @foreach ($active_languages as $active_lang)
                    <a rel="alternate" hreflang="{{ $active_lang->locale }}"
                        class="dropdown-item has-icon {{ App::getLocale() == $active_lang->locale ? 'active' : '' }}"
                        href="{{ LaravelLocalization::getLocalizedURL($active_lang->locale, null, [], true) }}">
                        <img alt="{{ $active_lang->name }}" src="{{ static_asset($active_lang->flag) }}"
                            class="language-flag">
                        {{ $active_lang->name }}
                    </a>
                @endforeach
            </div>
        </li>

        <li class="dropdown"><a href="#" data-toggle="dropdown"
                class="nav-link dropdown-toggle nav-link-lg nav-link-user">

                @if (Sentinel::getUser()->images &&
                        array_key_exists('image_40x40', Sentinel::getUser()->images) &&
                        @is_file_exists(Sentinel::getUser()->images['image_40x40']))
                    <img alt="{{ Sentinel::getUser()->first_name }}"
                        src="{{ static_asset(Sentinel::getUser()->images['image_40x40']) }}"
                        class="rounded-circle mr-1">
                @else
                    <img alt="{{ Sentinel::getUser()->first_name }}"
                        src="{{ static_asset('images/default/user32x32.jpg') }}" class="rounded-circle mr-1">
                @endif
                <div class="d-sm-none d-lg-inline-block">{{ Sentinel::getUser()->first_name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                @if (@Sentinel::getUser()->lastLogin())
                    <div class="dropdown-title">
                        {{ __('Logged in :minutes', ['minutes' => \Carbon\Carbon::parse(Sentinel::getUser()->lastLogin())->diffForHumans()]) }}
                    </div>
                @endif
                <a href="{{ route('admin.profile') }}"
                    class="dropdown-item has-icon">
                    <i class="bx bx-user"></i> {{ __('Profile') }}
                </a>
                <a href="{{ route('admin.login.activity') }}"
                    class="dropdown-item has-icon">
                    <i class='bx bx-file'></i>{{ __('Login Activities') }}
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('logout') }}" class="dropdown-item has-icon text-danger">
                    <i class="bx bx-log-out"></i> {{ __('Logout') }}
                </a>
            </div>
        </li>
    </ul>
</nav>
