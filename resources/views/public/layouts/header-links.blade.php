<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">



    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
       (function(c,l,a,r,i,t,y){
           c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
           t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
           y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
       })(window, document, "clarity", "script", "su2vqghfhw");
    </script>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TPBJ5F0GJP"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-TPBJ5F0GJP');
    </script>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PVDT4VHH');</script>

    <!-- Google Ads -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17541624328"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-17541624328');
    </script>



        @auth
            <script>
                window.Laravel = {
                    userId: {{ auth()->id() }},
                    soundUrl: "{{ asset('sound/new-message.mp3') }}",
                    iconUrl: "{{ asset('images/logo.png') }}",
                    unreadUrl: "{{ route('unread.messages.count') }}"
                };
            </script>
        @endauth

    <!-- Apple Smart App Banner — shown automatically by Safari on iOS -->
    <meta name="apple-itunes-app" content="app-id=6753354778">
</head>
<body class="bg-body text-gray-700 text-sm">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PVDT4VHH"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

    @include('public.components.app-install-banner')

    <main id="main-content">
