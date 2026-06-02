<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TEAL: track everything you read, watch, play & hear</title>
    <meta name="description" content="TEAL is a self-hosted tracker for everything you read, watch, play and listen to, books, films, TV, anime, comics, games, board games, albums and concerts. Your data, your server, no tracking.">
    <meta name="theme-color" content="#00AFB4">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="TEAL: your self-hosted media library">
    <meta property="og:description" content="One self-hosted home for everything you read, watch, play and hear. Your data, your server, no tracking.">
    <meta property="og:image" content="{{ asset('brand/og.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&family=jetbrains-mono:400,500,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --cream: #FAF7EE;
            --paper: #FFFFFF;
            --ink: #1F3231;
            --teal: #00AFB4;
            --teal-700: #0B8F94;
            --light: #7FE3E6;
            --coral: #FF6B5C;
            --coral-700: #E8513F;
            --bd: 3px solid var(--ink);
            --sh: 6px 6px 0 var(--ink);
            --sh-sm: 4px 4px 0 var(--ink);
            --font: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
            --mono: 'JetBrains Mono', ui-monospace, monospace;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--ink);
            font-family: var(--font);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            /* faint grid texture */
            background-image:
                linear-gradient(rgba(31,50,49,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(31,50,49,0.035) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        a { color: inherit; }
        .mono { font-family: var(--mono); }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }

        /* ── Buttons ─────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: .5rem;
            font-family: var(--font); font-weight: 700; font-size: .98rem;
            padding: .8rem 1.3rem; border: var(--bd); background: var(--paper);
            color: var(--ink); text-decoration: none; cursor: pointer;
            box-shadow: var(--sh-sm); transition: transform .08s ease, box-shadow .08s ease;
        }
        .btn:hover { transform: translate(-2px,-2px); box-shadow: var(--sh); }
        .btn:active { transform: translate(2px,2px); box-shadow: 2px 2px 0 var(--ink); }
        .btn--coral { background: var(--coral); color: #fff; }
        .btn--teal { background: var(--teal); color: #fff; }
        .btn--sm { padding: .55rem .9rem; font-size: .88rem; box-shadow: 3px 3px 0 var(--ink); }

        /* ── Wordmark (seal = the T) ──────────────── */
        .wordmark { display: inline-flex; align-items: center; text-decoration: none; font-weight: 700; letter-spacing: -.02em; line-height: 1; }
        .wordmark img { height: 2em; width: auto; margin: -0.1em -0.34em -0.1em -0.26em; }
        .wordmark span { display: inline-block; }

        /* ── Nav ─────────────────────────────────── */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: var(--cream); border-bottom: var(--bd);
        }
        .nav .wrap { display: flex; align-items: center; justify-content: space-between; height: 72px; }
        .nav .wordmark { font-size: 1.7rem; }
        .nav-actions { display: flex; align-items: center; gap: .6rem; }

        /* ── Hero ────────────────────────────────── */
        .hero { padding: 64px 0 28px; }
        .hero .wrap { display: grid; grid-template-columns: 1.15fr .85fr; gap: 48px; align-items: center; }
        .eyebrow {
            display: inline-block; font-family: var(--mono); font-size: .8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .12em;
            background: var(--ink); color: var(--light); padding: .35rem .7rem; margin-bottom: 22px;
        }
        h1 { font-size: clamp(2.4rem, 5.2vw, 4rem); line-height: 1.02; letter-spacing: -.03em; margin: 0 0 18px; font-weight: 700; }
        h1 .u { background: var(--coral); color: #fff; padding: 0 .12em; box-decoration-break: clone; -webkit-box-decoration-break: clone; }
        h1 .u2 { background: var(--teal); color: #fff; padding: 0 .12em; box-decoration-break: clone; -webkit-box-decoration-break: clone; }
        .lead { font-size: 1.18rem; max-width: 34ch; color: var(--ink); opacity: .9; margin: 0 0 28px; }
        .hero-cta { display: flex; flex-wrap: wrap; gap: 14px; }
        .trust { margin-top: 22px; font-family: var(--mono); font-size: .82rem; opacity: .7; display: flex; gap: 1rem; flex-wrap: wrap; }
        .trust b { color: var(--teal-700); }

        .hero-art {
            border: var(--bd); box-shadow: var(--sh); background: var(--light);
            padding: 18px; position: relative;
        }
        .hero-art::before {
            content: "TEAL"; position: absolute; top: -14px; left: 18px;
            font-family: var(--mono); font-weight: 700; font-size: .72rem; letter-spacing: .2em;
            background: var(--ink); color: #fff; padding: .25rem .6rem;
        }
        .hero-art img { width: 100%; display: block; filter: drop-shadow(3px 4px 0 rgba(31,50,49,.18)); }

        /* ── Section scaffolding ─────────────────── */
        section { padding: 56px 0; border-top: var(--bd); }
        .kicker { font-family: var(--mono); font-size: .82rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--teal-700); margin: 0 0 10px; }
        h2 { font-size: clamp(1.7rem, 3.4vw, 2.5rem); letter-spacing: -.02em; margin: 0 0 8px; font-weight: 700; }
        .sub { font-size: 1.05rem; opacity: .82; max-width: 56ch; margin: 0 0 32px; }

        /* ── Media types grid ────────────────────── */
        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
        .card {
            background: var(--paper); border: var(--bd); box-shadow: var(--sh-sm);
            padding: 20px; transition: transform .1s ease, box-shadow .1s ease;
        }
        .card:hover { transform: translate(-2px,-2px); box-shadow: var(--sh); }
        .card .ic { font-size: 1.8rem; line-height: 1; }
        .card h3 { margin: 12px 0 4px; font-size: 1.12rem; }
        .card p { margin: 0; font-size: .9rem; opacity: .72; }
        .card:nth-child(4n+1) { background: #EBFBFB; }
        .card:nth-child(4n+3) { background: #FFF1EE; }

        /* ── Features ────────────────────────────── */
        .features { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .feat { border: var(--bd); box-shadow: var(--sh-sm); padding: 24px; background: var(--paper); }
        .feat .tag { display: inline-block; font-family: var(--mono); font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; padding: .3rem .55rem; border: 2px solid var(--ink); margin-bottom: 14px; }
        .feat:nth-child(1) .tag { background: var(--teal); color: #fff; }
        .feat:nth-child(2) .tag { background: var(--coral); color: #fff; }
        .feat:nth-child(3) .tag { background: var(--ink); color: var(--light); }
        .feat:nth-child(4) .tag { background: var(--light); }
        .feat h3 { margin: 0 0 8px; font-size: 1.3rem; }
        .feat p { margin: 0; opacity: .82; }

        /* ── Self-host block ─────────────────────── */
        .host { background: var(--ink); color: var(--cream); border-top: var(--bd); border-bottom: var(--bd); }
        .host .wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; }
        .host h2 { color: var(--cream); }
        .host .kicker { color: var(--light); }
        .host .sub { color: var(--cream); opacity: .8; }
        .terminal { background: #0F1E1D; border: 3px solid var(--teal); box-shadow: 6px 6px 0 var(--teal-700); }
        .terminal .bar { display: flex; gap: 7px; padding: 10px 14px; border-bottom: 1px solid rgba(127,227,230,.25); }
        .terminal .bar i { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .terminal .bar i:nth-child(1){ background:#FF6B5C } .terminal .bar i:nth-child(2){ background:#FFD23F } .terminal .bar i:nth-child(3){ background:#00AFB4 }
        .terminal pre { margin: 0; padding: 18px; font-family: var(--mono); font-size: .9rem; color: var(--light); overflow-x: auto; line-height: 1.7; }
        .terminal .c { color: #6f8c8a; }
        .terminal .g { color: #FFD23F; }

        /* ── Screenshot band ─────────────────────── */
        .shot { text-align: center; }
        .shot .frame {
            border: var(--bd); box-shadow: var(--sh); background: #0F1E1D; margin-top: 26px;
            overflow: hidden; max-width: 980px; margin-left: auto; margin-right: auto;
        }
        .shot .chrome { display: flex; align-items: center; gap: 8px; padding: 11px 14px; background: var(--ink); }
        .shot .chrome i { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .shot .chrome i:nth-child(1){ background:#FF6B5C } .shot .chrome i:nth-child(2){ background:#FFD23F } .shot .chrome i:nth-child(3){ background:#00AFB4 }
        .shot .chrome .url { margin-left: 10px; font-family: var(--mono); font-size: .76rem; color: #9fb3b1; background:#0F1E1D; padding:.25rem .7rem; border-radius:4px; }
        .shot .frame img { width: 100%; display: block; }
        .shot .cap { margin-top: 16px; font-family: var(--mono); font-size: .85rem; opacity: .7; }

        /* ── Ethos / manifesto ───────────────────── */
        .ethos { position: relative; overflow: hidden; background: var(--coral); border-top: var(--bd); border-bottom: var(--bd); }
        .ethos .wrap { position: relative; z-index: 1; }
        .ethos .text { max-width: 60ch; }
        .ethos .seal-bg {
            position: absolute; right: 1%; bottom: -56px; width: 320px; height: auto;
            opacity: .14; filter: brightness(0) invert(1); pointer-events: none; z-index: 0;
        }
        .ethos h2 { color: #fff; font-size: clamp(1.8rem, 3.6vw, 2.6rem); margin: 0 0 12px; }
        .ethos p { color: #fff; font-size: 1.12rem; margin: 0; }
        .ethos .kicker { color: var(--ink); }
        .ethos b { background: var(--ink); color: var(--light); padding: 0 .25em; }
        @media (max-width: 880px) { .ethos .seal-bg { width: 200px; bottom: -36px; opacity: .12; } }

        /* ── Maker / footer ──────────────────────── */
        .maker .wrap { display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .maker p { margin: 0; max-width: 48ch; }
        .maker .lead-in { font-family: var(--mono); font-size: .8rem; letter-spacing: .12em; text-transform: uppercase; color: var(--teal-700); }
        footer { border-top: var(--bd); background: var(--ink); color: var(--cream); padding: 40px 0; }
        footer .wrap { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        footer .wordmark { color: var(--cream); font-size: 1.5rem; }
        footer a { font-family: var(--mono); font-size: .85rem; opacity: .82; text-decoration: none; margin-left: 18px; }
        footer a:hover { opacity: 1; color: var(--light); }

        @media (max-width: 880px) {
            .hero .wrap, .host .wrap { grid-template-columns: 1fr; }
            .hero-art { order: -1; max-width: 340px; }
            .cards { grid-template-columns: repeat(2, 1fr); }
            .features { grid-template-columns: 1fr; }
        }
        @media (prefers-reduced-motion: reduce) {
            .btn, .card { transition: none; }
            .btn:hover, .card:hover { transform: none; }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="wrap">
            <a href="/" class="wordmark" aria-label="TEAL home">
                <img src="/brand/seal-glyph.svg" alt=""><span>EAL</span>
            </a>
            <div class="nav-actions">
                <a class="btn btn--sm" href="https://github.com/dotMavriQ/teal" target="_blank" rel="noopener">GitHub</a>
                @auth
                    <a class="btn btn--sm btn--teal" href="{{ url('/dashboard') }}">Open app →</a>
                @else
                    <a class="btn btn--sm" href="{{ route('login') }}">Log in</a>
                    @if (Route::has('register'))
                        <a class="btn btn--sm btn--coral" href="{{ route('register') }}">Get started</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="wrap">
            <div>
                <span class="eyebrow">Self-hosted · Open source · Yours</span>
                <h1>Track everything you <span class="u2">read</span>, <span class="u2">watch</span>, <span class="u">play</span> &amp; <span class="u">hear</span>.</h1>
                <p class="lead">TEAL is one self-hosted home for your whole media life, eight libraries, your data, your server. No accounts you don't own. No tracking. Ever.</p>
                <div class="hero-cta">
                    <a class="btn btn--coral" href="https://github.com/dotMavriQ/teal" target="_blank" rel="noopener">Self-host it →</a>
                    @if (Route::has('register'))
                        <a class="btn" href="{{ route('register') }}">Try a demo account</a>
                    @endif
                </div>
                <div class="trust">
                    <span><b>AGPL-3.0</b> copyleft</span>
                    <span><b>Docker</b> in ~2 min</span>
                    <span><b>8</b> libraries, one place</span>
                </div>
            </div>
            <div class="hero-art">
                <img src="/brand/seal-hero.svg" alt="The TEAL seal, a seal forming the letter T">
            </div>
        </div>
    </header>

    <section>
        <div class="wrap">
            <p class="kicker">What is TEAL</p>
            <h2>The Essential Aggregator Library.</h2>
            <p class="sub">A personal media tracker for people who consume a lot and want one tidy, private record of it. Books to board games, films to setlists, search, import, rate, and revisit, all from a server you control.</p>
            <div class="cards">
                <div class="card"><div class="ic">📚</div><h3>Books</h3><p>Import from Goodreads, shelves &amp; reading queue.</p></div>
                <div class="card"><div class="ic">🎬</div><h3>Movies &amp; TV</h3><p>TMDB + IMDb, episodes and watchlists.</p></div>
                <div class="card"><div class="ic">🍥</div><h3>Anime</h3><p>MAL imports, ratings, status tracking.</p></div>
                <div class="card"><div class="ic">💥</div><h3>Comics</h3><p>ComicVine volumes &amp; issues.</p></div>
                <div class="card"><div class="ic">🎮</div><h3>Games</h3><p>IGDB metadata, backlog to mastered.</p></div>
                <div class="card"><div class="ic">🎲</div><h3>Board games</h3><p>BoardGameGeek collection &amp; wishlist.</p></div>
                <div class="card"><div class="ic">💿</div><h3>Albums</h3><p>Discogs releases, listening log.</p></div>
                <div class="card"><div class="ic">🎤</div><h3>Concerts</h3><p>setlist.fm shows you've been to.</p></div>
            </div>
        </div>
    </section>

    <section class="shot">
        <div class="wrap">
            <p class="kicker">See it in action</p>
            <h2>Your whole library, one tidy grid.</h2>
            <p class="sub" style="margin-left:auto;margin-right:auto">Search, filter, rate and revisit, the same calm interface across every kind of media.</p>
            <div class="frame">
                <div class="chrome"><i></i><i></i><i></i><span class="url">teal.yourserver.tld/books</span></div>
                <img src="/brand/screenshot.webp" alt="The TEAL app showing a book library grid on real data" loading="lazy">
            </div>
            <p class="cap">↑ a real TEAL library, running on real data</p>
        </div>
    </section>

    <section>
        <div class="wrap">
            <p class="kicker">Why TEAL</p>
            <h2>Built for keeping, not for harvesting.</h2>
            <div class="features">
                <div class="feat">
                    <span class="tag">Private by default</span>
                    <h3>Your data stays yours</h3>
                    <p>Runs on your machine, in your Postgres. No third party sees what you read or watch, the only thing TEAL phones is the metadata API you ask it to.</p>
                </div>
                <div class="feat">
                    <span class="tag">Import everything</span>
                    <h3>Bring your history with you</h3>
                    <p>Goodreads, IMDb, MyAnimeList exports drop straight in. Search TMDB, OpenLibrary, IGDB, Discogs, ComicVine, BGG and setlist.fm to fill the rest.</p>
                </div>
                <div class="feat">
                    <span class="tag">One place</span>
                    <h3>Eight libraries, no tab-juggling</h3>
                    <p>Stop spreading your taste across a dozen apps. Books, screens, games and music live under one roof with consistent search, filters and ratings.</p>
                </div>
                <div class="feat">
                    <span class="tag">No tracking</span>
                    <h3>No accounts you don't control</h3>
                    <p>No analytics, no ad SDKs, no telemetry. It's a single Laravel app you own, fork it, theme it, host it on a Raspberry Pi if you like.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="ethos">
        <div class="wrap">
            <div class="text">
                <p class="kicker">Why it's built this way</p>
                <h2>Human-made &amp; copyleft.</h2>
                <p>TEAL is hand-built by someone who actually uses it, and it's <b>AGPL-3.0</b>, copyleft, so it stays free: run it, fork it, improve it, share your changes back. A small tool made to be kept, not monetised.</p>
            </div>
        </div>
        <img class="seal-bg" src="/brand/seal-glyph.svg" alt="" aria-hidden="true">
    </section>

    <section class="host">
        <div class="wrap">
            <div>
                <p class="kicker">Self-host in minutes</p>
                <h2>Clone, set keys, up.</h2>
                <p class="sub">A single Docker stack, app, Postgres, queue. No Redis, no fuss. Point a domain at it and you're done.</p>
                <a class="btn btn--coral" href="https://github.com/dotMavriQ/teal" target="_blank" rel="noopener">Read the setup →</a>
            </div>
            <div class="terminal" aria-hidden="true">
                <div class="bar"><i></i><i></i><i></i></div>
                <pre><span class="c"># grab it</span>
git clone https://github.com/dotMavriQ/teal
<span class="c"># add your API keys</span>
cp .env.production.example .env
<span class="c"># up you go</span>
docker compose up <span class="g">-d</span></pre>
            </div>
        </div>
    </section>

    <section class="maker">
        <div class="wrap">
            <div>
                <p class="lead-in">Made &amp; dogfooded by</p>
                <p style="font-size:1.4rem;font-weight:700;margin:.2rem 0 .4rem">dotMavriQ</p>
                <p>TEAL is a real tool I use every day and keep sharpening in the open, free to download, free to fork. If it earns a spot on your server, that's the whole reward.</p>
            </div>
            <div class="nav-actions">
                <a class="btn" href="https://github.com/dotMavriQ/teal" target="_blank" rel="noopener">Star on GitHub</a>
                @guest
                    <a class="btn btn--teal" href="{{ route('login') }}">Log in →</a>
                @endguest
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">
            <a href="/" class="wordmark"><img src="/brand/seal-glyph.svg" alt=""><span>EAL</span></a>
            <div>
                <a href="https://github.com/dotMavriQ/teal" target="_blank" rel="noopener">GitHub</a>
                <a href="{{ route('login') }}">Log in</a>
                <a href="https://github.com/dotMavriQ/teal/blob/main/LICENSE" target="_blank" rel="noopener">AGPL-3.0</a>
            </div>
        </div>
    </footer>
</body>
</html>
