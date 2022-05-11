<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $title ?? 'Care portal' }}</title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,600" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Vuetify -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">


    <link rel="stylesheet" type="text/css" href="/css/style.css?v=5" />
	<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">

    <!-- FAVICON -->
    <link rel="icon" href="/img/favicon.png" type="image/x-icon">

    <!-- JAVASCRIPTS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="//cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>

</head>

<body class="@if(! empty($person))loading @else signup-page @endif">
    @if(! empty($person))
        <header id="mobile-header">
            <a href="#" class="menu">menu</a>
            <h1 class="logo">Care portal</h1>
        </header>
    @endif

    @if(! empty($person))
	<header id="main-menu">
		<div class="drawer">
			<h1 class="logo">Care portal</h1>
            <div class="drawer-wrapper">
            @if(! empty($person->IsClient))
                <h6>Cliënt</h6>
                <ul>
                    <li>
                        <a href="/agenda/client" class="">
                            <i>event_note</i> Agenda page
                        </a>
                    </li>
                    <li>
                        <a href="/question/category"
                           class="">
                            <i>add_circle</i> New Question
                        </a>
                    </li>
                </ul>
            @endif
            @if(! empty($person->IsCareGiver))
                <h6>Caregivers</h6>

                <ul>
                    <li>
                        <a href="/agenda/caregiver"
                           class="">
                            <i>date_range</i> Agenda Page
                        </a>
                    </li>
                    <li>
                        <a href="/openQuestion-caregiver"
                           class="">
                            <i>all_inbox</i> Open Questions
                        </a>
                    </li>
                    <li>
                        <a href="/clienten-caregiver" class="">
                            <i>contacts</i> Clients
                        </a>
                    </li>
                </ul>
            @endif

            <h6>My Account</h6>
            @if(! empty($person))
                <ul>
                    <li>
                        <a href="/logout" class="">
                            <i>logout</i> Logout
                        </a>
                    </li>
                </ul>
            @endif
        </div>
    </div>
    <div class="drawer-closer"></div>

</header>
@endif

    @if(isset($app_care_portal))
        <div id="app" style="display: none">
    @endif
    @if(isset($appheader))
            {{$appheader}}
    @else
        <header class="@if(! empty($person))headline @else signup-logo @endif">
            @if(isset($progressbar))
                {{$progressbar}}
            @endif
            <div class="wrap">
                @isset($picture)
                    <div class="person">
                        <div class="profile-pic client">{{ $picture }}</div>
                @endisset
                        @isset($pretitle)
                            <h6>{{ $pretitle ?? '' }}</h6>
                        @endisset
                        @if(! empty($person))
                            <div class="title">
                                <h2>{{ $title ?? '' }}</h2>
                                @if(isset($titleContent))
                                    {{ $titleContent }}
                                @endif
                            </div>
                        @else
                            <h1 class="logo">Care portal</h1>
                        @endif
                @isset($picture)
                    </div>
                @endisset
            </div>
        </header>
    @endif

        {{ $slot }}
    @if(! empty($person))
        </main>
    @endif
    @if(isset($app_care_portal))
        </div>
    @endif
</body>
</html>
