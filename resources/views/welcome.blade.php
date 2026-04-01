<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="TEAL - The Essential Aggregator Library. A self-hosted media tracker for films, TV, books, anime, comics, games, board games, and music.">
        <meta name="theme-color" content="#0f172a">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:title" content="TEAL | The Essential Aggregator Library">
        <meta property="og:description" content="Self-hosted personal media tracker. Track films, TV, books, anime, comics, games, board games, and music with API-powered search, imports, and gallery views.">
        <meta property="og:image" content="{{ asset('android-chrome-512x512.png') }}">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="TEAL | The Essential Aggregator Library">
        <meta name="twitter:description" content="Self-hosted personal media tracker. Track films, TV, books, anime, comics, games, board games, and music with API-powered search, imports, and gallery views.">
        <meta name="twitter:image" content="{{ asset('android-chrome-512x512.png') }}">

        <title>TEAL | The Essential Aggregator Library</title>

        <!-- Preconnect for performance -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&family=jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Landing page specific — Inter for marketing, JetBrains Mono for accents */
            .font-display { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
            .font-mono-accent { font-family: 'JetBrains Mono', ui-monospace, monospace; }

            /* Smooth scroll */
            html { scroll-behavior: smooth; }

            /* Hero gradient mesh */
            .hero-gradient {
                background:
                    radial-gradient(ellipse 80% 50% at 50% -20%, rgba(13, 148, 136, 0.15), transparent),
                    radial-gradient(ellipse 60% 40% at 80% 60%, rgba(13, 148, 136, 0.06), transparent);
            }

            /* Product shot perspective */
            .product-frame {
                perspective: 1200px;
            }
            .product-shot {
                transform: rotateY(-2deg) rotateX(1deg);
                transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            }
            .product-shot:hover {
                transform: rotateY(0deg) rotateX(0deg) scale(1.01);
            }

            /* Subtle glow behind product shot */
            .product-glow {
                background: radial-gradient(ellipse at center, rgba(13, 148, 136, 0.2) 0%, transparent 70%);
                filter: blur(60px);
            }

            /* Feature card hover lift */
            .feature-card {
                transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.3s ease;
            }
            .feature-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            }

            /* Fade-in on scroll (enhanced with JS) */
            .fade-section {
                opacity: 0;
                transform: translateY(24px);
                transition: opacity 0.7s cubic-bezier(0.23, 1, 0.32, 1), transform 0.7s cubic-bezier(0.23, 1, 0.32, 1);
            }
            .fade-section.visible {
                opacity: 1;
                transform: translateY(0);
            }

            /* Stat counter subtle pulse */
            .stat-number {
                font-variant-numeric: tabular-nums;
            }

            /* Skip link */
            .skip-link {
                position: absolute;
                left: -9999px;
                top: 0;
                z-index: 100;
                padding: 0.75rem 1.5rem;
                background: rgb(13, 148, 136);
                color: white;
                font-weight: 600;
                border-radius: 0 0 0.5rem 0;
            }
            .skip-link:focus {
                left: 0;
            }

            /* Noise texture overlay for depth */
            .noise::after {
                content: '';
                position: absolute;
                inset: 0;
                opacity: 0.02;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
                pointer-events: none;
            }
        </style>
    </head>
    <body class="font-display antialiased bg-slate-950 text-slate-100 min-h-screen overflow-x-hidden">
        <!-- Skip to content (WCAG 2.1 AA) -->
        <a href="#main-content" class="skip-link">Skip to main content</a>

        <!-- ================================================================
             NAVIGATION
             ================================================================ -->
        <header class="fixed top-0 inset-x-0 z-50 transition-all duration-300" id="site-header" role="banner">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <nav class="flex items-center justify-between h-16 lg:h-20" aria-label="Primary navigation">
                    <!-- Logo -->
                    <a href="/" class="flex items-center gap-2.5 group" aria-label="TEAL home">
                        <span class="font-mono-accent text-white font-medium text-lg tracking-tight group-hover:text-slate-200 transition-colors">teal<span class="text-teal-400">.</span></span>
                    </a>

                    <!-- Nav links -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                   class="inline-flex items-center gap-2 rounded-full bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950">
                                    Dashboard
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center rounded-full bg-white/10 backdrop-blur-sm border border-white/10 px-5 py-2 text-sm font-semibold text-white hover:bg-white/15 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950">
                                    Sign in
                                </a>
                            @endauth
                        </div>
                    @endif
                </nav>
            </div>
        </header>

        <main id="main-content" role="main">

            <!-- ================================================================
                 HERO SECTION
                 ================================================================ -->
            <section class="relative hero-gradient noise min-h-[100svh] flex items-center" aria-labelledby="hero-heading">
                <div class="mx-auto max-w-7xl px-6 lg:px-8 pt-28 pb-20 lg:pt-36 lg:pb-28 w-full">
                    <div class="grid lg:grid-cols-2 gap-16 lg:gap-20 items-center">

                        <!-- Left column: Copy -->
                        <div class="max-w-2xl">
                            <!-- Eyebrow -->
                            <div class="inline-flex items-center gap-2 rounded-full bg-teal-500/10 border border-teal-500/20 px-4 py-1.5 mb-8">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                                </span>
                                <span class="font-mono-accent text-xs font-medium text-teal-300 tracking-wide">Self-hosted &amp; open source</span>
                            </div>

                            <h1 id="hero-heading" class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-[1.1] text-white">
                                Your entire media life,
                                <span class="text-teal-400">unified.</span>
                            </h1>

                            <p class="mt-6 text-lg sm:text-xl leading-relaxed text-slate-400 max-w-xl">
                                Films, books, series, and anime tracked in one place. No algorithms deciding what you see. No corporation owning your data. Just you and your collection.
                            </p>

                            <!-- CTA group -->
                            <div class="mt-10 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <a href="https://github.com/dotMavriQ/TEAL-Laravel"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2.5 rounded-full bg-teal-500 px-7 py-3.5 text-base font-semibold text-slate-950 hover:bg-teal-400 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 shadow-lg shadow-teal-500/20">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                                    View on GitHub
                                </a>
                                <a href="#features"
                                   class="inline-flex items-center gap-2 text-sm font-medium text-slate-400 hover:text-white transition-colors group focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 rounded-full px-4 py-3">
                                    See what's inside
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Right column: Product shot -->
                        <div class="product-frame relative hidden lg:block" aria-hidden="true">
                            <div class="product-glow absolute inset-0 -m-8"></div>
                            <div class="product-shot relative rounded-xl overflow-hidden border border-white/10 shadow-2xl shadow-black/50 ring-1 ring-white/5">
                                <img
                                    src="{{ asset('images/teal-screenshot.png') }}"
                                    alt=""
                                    class="w-full h-auto"
                                    loading="eager"
                                    width="1200"
                                    height="675"
                                >
                                <!-- Reflection gradient -->
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-white/[0.03]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile product shot -->
                    <div class="mt-16 lg:hidden product-frame relative" aria-hidden="true">
                        <div class="product-glow absolute inset-0 -m-4"></div>
                        <div class="product-shot relative rounded-xl overflow-hidden border border-white/10 shadow-2xl shadow-black/50">
                            <img
                                src="{{ asset('images/teal-screenshot.png') }}"
                                alt=""
                                class="w-full h-auto"
                                loading="eager"
                                width="1200"
                                height="675"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-white/[0.03]"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 SOCIAL PROOF / NUMBERS
                 ================================================================ -->
            <section class="relative border-y border-white/5 bg-slate-950/80 backdrop-blur-sm" aria-label="Statistics">
                <div class="mx-auto max-w-7xl px-6 lg:px-8 py-12 lg:py-16">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                        <div class="text-center">
                            <p class="stat-number text-3xl lg:text-4xl font-bold text-white">4</p>
                            <p class="mt-1 text-sm text-slate-500 font-medium">Media categories</p>
                        </div>
                        <div class="text-center">
                            <p class="stat-number text-3xl lg:text-4xl font-bold text-white">1-click</p>
                            <p class="mt-1 text-sm text-slate-500 font-medium">Import from the giants</p>
                        </div>
                        <div class="text-center">
                            <p class="stat-number text-3xl lg:text-4xl font-bold text-white">100%</p>
                            <p class="mt-1 text-sm text-slate-500 font-medium">Self-hosted</p>
                        </div>
                        <div class="text-center">
                            <p class="stat-number text-3xl lg:text-4xl font-bold text-white">0</p>
                            <p class="mt-1 text-sm text-slate-500 font-medium">Tracking scripts</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 FEATURES GRID
                 ================================================================ -->
            <section id="features" class="relative py-24 lg:py-32" aria-labelledby="features-heading">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <!-- Section header -->
                    <div class="fade-section max-w-2xl mx-auto text-center mb-16 lg:mb-20">
                        <p class="font-mono-accent text-sm font-medium text-teal-400 tracking-wide uppercase mb-4">Features</p>
                        <h2 id="features-heading" class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                            What you get
                        </h2>
                        <p class="mt-4 text-lg text-slate-400 leading-relaxed">
                            Built for people who care about what they watch and read, and where that data lives.
                        </p>
                    </div>

                    <!-- Feature cards -->
                    <div class="fade-section grid sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">

                        <!-- Film & TV -->
                        <article class="feature-card rounded-2xl bg-white/[0.03] border border-white/[0.06] p-7 lg:p-8">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Films &amp; Television</h3>
                            <p class="text-sm leading-relaxed text-slate-400">Gallery and list views. Rate on a 1&ndash;10 scale, filter by status, sort by date watched. Posters and synopses are fetched automatically.</p>
                        </article>

                        <!-- Books -->
                        <article class="feature-card rounded-2xl bg-white/[0.03] border border-white/[0.06] p-7 lg:p-8">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Books &amp; Reading</h3>
                            <p class="text-sm leading-relaxed text-slate-400">Five-star ratings, reading progress tracking, shelves, and a dedicated reading queue. Cover art and metadata fetched automatically.</p>
                        </article>

                        <!-- Anime -->
                        <article class="feature-card rounded-2xl bg-white/[0.03] border border-white/[0.06] p-7 lg:p-8">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Anime</h3>
                            <p class="text-sm leading-relaxed text-slate-400">Import your MAL list, then enrich with episode counts, synopses, and artwork from Jikan and other sources.</p>
                        </article>

                        <!-- Import -->
                        <article class="feature-card rounded-2xl bg-white/[0.03] border border-white/[0.06] p-7 lg:p-8">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Import from the giants</h3>
                            <p class="text-sm leading-relaxed text-slate-400">Already tracking elsewhere? Bring your history with you. CSV and XML imports from the platforms you've been using. Your whole library, moved over in minutes.</p>
                        </article>

                        <!-- Metadata -->
                        <article class="feature-card rounded-2xl bg-white/[0.03] border border-white/[0.06] p-7 lg:p-8">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Automatic enrichment</h3>
                            <p class="text-sm leading-relaxed text-slate-400">Add a title and TEAL fills in the rest. Posters, synopses, ratings, episode counts, and page numbers pulled from trusted metadata sources.</p>
                        </article>

                        <!-- Self-hosted -->
                        <article class="feature-card rounded-2xl bg-white/[0.03] border border-white/[0.06] p-7 lg:p-8">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center mb-5">
                                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Fully self-hosted</h3>
                            <p class="text-sm leading-relaxed text-slate-400">Deploy on your own server with Docker. PostgreSQL and FrankenPHP included. Your data stays on your hardware.</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 HOW IT WORKS
                 ================================================================ -->
            <section class="relative py-24 lg:py-32 border-t border-white/5" aria-labelledby="how-heading">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="fade-section max-w-2xl mx-auto text-center mb-16 lg:mb-20">
                        <p class="font-mono-accent text-sm font-medium text-teal-400 tracking-wide uppercase mb-4">How it works</p>
                        <h2 id="how-heading" class="text-3xl sm:text-4xl font-bold tracking-tight text-white">
                            Three steps. That's it.
                        </h2>
                    </div>

                    <div class="fade-section grid md:grid-cols-3 gap-8 lg:gap-12 max-w-4xl mx-auto">
                        <!-- Step 1 -->
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-teal-500/10 border border-teal-500/20 mb-5">
                                <span class="font-mono-accent text-sm font-bold text-teal-400">01</span>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Deploy</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">One <code class="font-mono-accent text-xs text-teal-300/80 bg-teal-500/10 px-1.5 py-0.5 rounded">docker compose up</code> and you're running. PostgreSQL, the app, and a queue worker, all configured out of the box.</p>
                        </div>

                        <!-- Step 2 -->
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-teal-500/10 border border-teal-500/20 mb-5">
                                <span class="font-mono-accent text-sm font-bold text-teal-400">02</span>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Import</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">Bring your existing data. Export from the platforms you've been using, upload to TEAL, and your library is ready.</p>
                        </div>

                        <!-- Step 3 -->
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-teal-500/10 border border-teal-500/20 mb-5">
                                <span class="font-mono-accent text-sm font-bold text-teal-400">03</span>
                            </div>
                            <h3 class="text-base font-semibold text-white mb-2">Own it</h3>
                            <p class="text-sm text-slate-400 leading-relaxed">Rate, review, organize. Your collection grows with you, backed by a real database on hardware you control.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 SELF-HOST OR GET ACCESS
                 ================================================================ -->
            <section class="relative py-24 lg:py-32 border-t border-white/5" aria-labelledby="cta-heading">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="fade-section relative rounded-3xl overflow-hidden">
                        <!-- Background -->
                        <div class="absolute inset-0 bg-gradient-to-br from-teal-950/80 via-slate-900/90 to-slate-950 border border-white/[0.06] rounded-3xl"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(13,148,136,0.15),transparent_60%)]"></div>

                        <div class="relative px-8 py-16 sm:px-16 sm:py-20 lg:px-24 lg:py-24">
                            <div class="grid md:grid-cols-2 gap-12 lg:gap-16 items-start">
                                <!-- Self-host column -->
                                <div class="text-center md:text-left">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-teal-500/10 mb-5">
                                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                        </svg>
                                    </div>
                                    <h2 id="cta-heading" class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                                        Run it yourself
                                    </h2>
                                    <p class="mt-3 text-base text-slate-400 leading-relaxed">
                                        TEAL is open source and designed to be self-hosted. Clone the repo, run <code class="font-mono-accent text-xs text-teal-300/80 bg-teal-500/10 px-1.5 py-0.5 rounded">docker compose up</code>, and you own the whole stack.
                                    </p>
                                    <div class="mt-6">
                                        <a href="https://github.com/dotMavriQ/TEAL-Laravel"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-slate-100 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 shadow-lg">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                                            View on GitHub
                                        </a>
                                    </div>
                                </div>

                                <!-- Hosted access column -->
                                <div class="text-center md:text-left">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-teal-500/10 mb-5">
                                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                                        Try this instance
                                    </h3>
                                    <p class="mt-3 text-base text-slate-400 leading-relaxed">
                                        Not ready to self-host? Monthly supporters on Liberapay get access to this live instance. It helps keep the project going.
                                    </p>
                                    <div class="mt-6">
                                        <a href="https://liberapay.com/dotmavriq"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-2 rounded-full bg-teal-500 px-6 py-3 text-sm font-semibold text-slate-950 hover:bg-teal-400 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 shadow-lg shadow-teal-500/20">
                                            Support on Liberapay
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <!-- ================================================================
             FOOTER
             ================================================================ -->
        <footer class="border-t border-white/5 py-10 lg:py-12" role="contentinfo">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="font-mono-accent text-sm font-medium text-white/60">teal<span class="text-teal-400/60">.</span></span>
                        <span class="text-slate-600 text-sm">&middot;</span>
                        <span class="text-sm text-slate-600">The Essential Aggregator Library</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            onclick="document.getElementById('mastodon-modal').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-teal-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 rounded"
                            aria-label="Share on Mastodon"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.327 8.566c0-4.339-2.843-5.61-2.843-5.61-1.433-.658-3.894-.935-6.451-.956h-.063c-2.557.021-5.016.298-6.45.956 0 0-2.843 1.272-2.843 5.61 0 .993-.019 2.181.012 3.441.103 4.243.778 8.425 4.701 9.463 1.809.479 3.362.579 4.612.51 2.268-.126 3.541-.809 3.541-.809l-.075-1.646s-1.621.511-3.441.449c-1.804-.062-3.707-.194-3.999-2.409a4.523 4.523 0 01-.04-.621s1.77.432 4.014.535c1.372.063 2.658-.08 3.965-.236 2.506-.299 4.688-1.843 4.962-3.254.433-2.222.397-5.424.397-5.424zm-3.353 5.59h-2.081V9.057c0-1.075-.452-1.62-1.357-1.62-1 0-1.501.647-1.501 1.927v2.791h-2.069V9.364c0-1.28-.501-1.927-1.502-1.927-.905 0-1.357.546-1.357 1.62v5.099H6.026V8.903c0-1.074.273-1.927.823-2.558.566-.631 1.307-.955 2.228-.955 1.065 0 1.872.41 2.405 1.228l.518.869.519-.869c.533-.818 1.339-1.228 2.405-1.228.92 0 1.662.324 2.228.955.549.631.822 1.484.822 2.558v5.253z"/></svg>
                            Share
                        </button>
                        <span class="text-xs text-slate-600">Open source. Self-hosted. Yours.</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Mastodon share modal -->
        <div id="mastodon-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="mastodon-modal-title">
            <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="document.getElementById('mastodon-modal').classList.add('hidden')"></div>
            <div class="relative bg-slate-900 border border-white/10 rounded-2xl shadow-2xl shadow-black/50 p-8 max-w-sm w-full mx-4">
                <button
                    type="button"
                    onclick="document.getElementById('mastodon-modal').classList.add('hidden')"
                    class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors"
                    aria-label="Close"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.327 8.566c0-4.339-2.843-5.61-2.843-5.61-1.433-.658-3.894-.935-6.451-.956h-.063c-2.557.021-5.016.298-6.45.956 0 0-2.843 1.272-2.843 5.61 0 .993-.019 2.181.012 3.441.103 4.243.778 8.425 4.701 9.463 1.809.479 3.362.579 4.612.51 2.268-.126 3.541-.809 3.541-.809l-.075-1.646s-1.621.511-3.441.449c-1.804-.062-3.707-.194-3.999-2.409a4.523 4.523 0 01-.04-.621s1.77.432 4.014.535c1.372.063 2.658-.08 3.965-.236 2.506-.299 4.688-1.843 4.962-3.254.433-2.222.397-5.424.397-5.424zm-3.353 5.59h-2.081V9.057c0-1.075-.452-1.62-1.357-1.62-1 0-1.501.647-1.501 1.927v2.791h-2.069V9.364c0-1.28-.501-1.927-1.502-1.927-.905 0-1.357.546-1.357 1.62v5.099H6.026V8.903c0-1.074.273-1.927.823-2.558.566-.631 1.307-.955 2.228-.955 1.065 0 1.872.41 2.405 1.228l.518.869.519-.869c.533-.818 1.339-1.228 2.405-1.228.92 0 1.662.324 2.228.955.549.631.822 1.484.822 2.558v5.253z"/></svg>
                    </div>
                    <h3 id="mastodon-modal-title" class="text-lg font-semibold text-white">Share on Mastodon</h3>
                </div>
                <form id="mastodon-form" onsubmit="event.preventDefault(); shareMastodon();">
                    <label for="mastodon-instance" class="block text-sm font-medium text-slate-400 mb-2">Your instance</label>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500 text-sm shrink-0">https://</span>
                        <input
                            type="text"
                            id="mastodon-instance"
                            placeholder="mastodon.social"
                            autocomplete="url"
                            autocapitalize="none"
                            spellcheck="false"
                            class="flex-1 min-w-0 rounded-lg bg-slate-800 border border-white/10 px-3 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            required
                        >
                    </div>
                    <button
                        type="submit"
                        class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-full bg-teal-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-teal-400 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900"
                    >
                        Share
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </form>
                <p class="mt-3 text-xs text-slate-600 text-center">Your instance URL is not stored.</p>
            </div>
        </div>

        <!-- ================================================================
             SCROLL ANIMATIONS & HEADER BLUR
             ================================================================ -->
        <script>
            // Mastodon share handler
            function shareMastodon() {
                const input = document.getElementById('mastodon-instance');
                let instance = input.value.trim().replace(/^https?:\/\//, '').replace(/\/+$/, '');
                if (!instance) return;
                const text = encodeURIComponent('TEAL - Track your entire media life in one self-hosted platform\n' + window.location.origin);
                window.open('https://' + instance + '/share?text=' + text, '_blank', 'noopener,noreferrer');
                document.getElementById('mastodon-modal').classList.add('hidden');
            }

            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    document.getElementById('mastodon-modal').classList.add('hidden');
                }
            });

            // Intersection Observer for fade-in sections
            document.addEventListener('DOMContentLoaded', () => {
                const observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('visible');
                                observer.unobserve(entry.target);
                            }
                        });
                    },
                    { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
                );

                document.querySelectorAll('.fade-section').forEach((el) => observer.observe(el));

                // Header background on scroll
                const header = document.getElementById('site-header');
                const onScroll = () => {
                    if (window.scrollY > 40) {
                        header.classList.add('bg-slate-950/80', 'backdrop-blur-xl', 'border-b', 'border-white/5');
                    } else {
                        header.classList.remove('bg-slate-950/80', 'backdrop-blur-xl', 'border-b', 'border-white/5');
                    }
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();

                // Respect prefers-reduced-motion
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.querySelectorAll('.fade-section').forEach((el) => {
                        el.style.transition = 'none';
                        el.classList.add('visible');
                    });
                    document.querySelectorAll('.product-shot').forEach((el) => {
                        el.style.transition = 'none';
                        el.style.transform = 'none';
                    });
                }
            });
        </script>
    </body>
</html>
