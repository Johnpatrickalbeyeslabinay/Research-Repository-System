<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Research Repository System | CSU Aparri - College of Fisheries and Aquatic Science</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{
  --blue:#0a5c9e;
  --blue-dark:#08416f;
  --deep:#1D3C9D;
  --accent:#6bbcff;
  --aqua:#3aa0ee;
  --bg:#f4f8fb;
  --ink:#0d1b2a;
  --gray:#4a5d70;
  --line:#dbe6ef;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:"Inter",system-ui,sans-serif;color:var(--ink);background:var(--bg);min-height:100vh;display:flex;flex-direction:column}
a{text-decoration:none;color:inherit}
:focus-visible{outline:3px solid #2b8be0;outline-offset:3px}
h1,h2{font-family:"Playfair Display",Georgia,serif}
svg.i{width:1em;height:1em;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.wrap{max-width:1200px;margin:0 auto;padding:0 24px}

/* Header (matches index) */
.header{position:sticky;top:0;z-index:20;background:#fff;border-top:4px solid var(--accent);border-bottom:1px solid var(--line);box-shadow:0 4px 16px rgba(10,92,158,.1)}
.header .bar{display:flex;align-items:center;justify-content:space-between;gap:16px;height:78px}
.brand{display:flex;align-items:center;gap:12px;color:#fff}
.logo{width:70px;height:70px;border-radius:50%;object-fit:contain;flex:none;}
footer .logo{width:70px;height:70px;box-shadow:0 0 0 3px var(--accent)}
.brand b{display:block;font-size:19px;font-weight:600;line-height:1.2}
.brand small{font-size:13px;color:var(--accent)}
.header .brand b{font-family:"Playfair Display",Georgia,serif;font-weight:700;color:var(--deep)}
.header .brand small{color:var(--gray)}
.actions{display:flex;gap:10px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font:600 15px "Inter",sans-serif;height:46px;padding:0 22px;border-radius:999px;border:2px solid transparent;cursor:pointer;transition:transform .15s,background .15s,color .15s}
.btn:hover{transform:translateY(-2px)}
.btn.outline{border-color:rgba(255,255,255,.55);color:#fff;background:transparent}
.btn.outline:hover{background:rgba(255,255,255,.12)}
.btn.gold{background:var(--accent);color:var(--deep)}
.btn.gold:hover{background:#1D3C9D}
.header .btn.outline{border-color:var(--blue);color:var(--blue)}
.header .btn.outline:hover{background:var(--blue);color:#fff}
.header .btn.gold{background:var(--blue);color:#fff}
.header .btn.gold:hover{background:var(--blue-dark)}

/* Hero */
.hero{position:relative;overflow:hidden;color:#fff;background:radial-gradient(circle at 85% 15%,rgba(58,160,238,.45) 0%,transparent 45%),linear-gradient(160deg,var(--blue) 0%,var(--deep) 100%)}
.hero .grid{display:grid;grid-template-columns:1.1fr .9fr;gap:48px;align-items:center;padding:80px 0 150px;position:relative;z-index:2}
.pill{display:inline-flex;align-items:center;gap:8px;background:rgba(107,188,255,.14);border:1px solid rgba(107,188,255,.55);color:var(--accent);padding:6px 14px;border-radius:999px;font-size:14px;font-weight:500;margin-bottom:22px}
.hero h1{font-size:clamp(36px,5.2vw,58px);line-height:1.1;margin-bottom:22px}
.hero h1 span{color:var(--accent)}
.hero p.lead{font-size:19px;line-height:1.65;color:#dcebf7;max-width:540px;margin-bottom:32px}
.hero .cta-row{display:flex;gap:12px;flex-wrap:wrap}
.chips{display:flex;gap:10px;flex-wrap:wrap;margin-top:32px}
.chips li{list-style:none;display:flex;align-items:center;gap:6px;font-size:14px;color:#dcebf7}
.chips svg{color:var(--accent);font-size:16px}

/* Sample submission card */
.paper{background:#fff;color:var(--ink);border-radius:18px;padding:24px;box-shadow:0 24px 50px rgba(0,0,0,.35);border-top:6px solid var(--accent);transform:rotate(1.5deg)}
.paper .tag{font-size:13px;color:var(--gray);margin-bottom:6px}
.paper h3{font-size:18px;line-height:1.35;margin-bottom:18px}
.track{list-style:none;position:relative;padding-left:30px}
.track::before{content:"";position:absolute;left:9px;top:6px;bottom:10px;width:2px;background:var(--line)}
.track li{position:relative;padding:0 0 16px;font-size:15px;color:var(--gray)}
.track li::before{content:"";position:absolute;left:-30px;top:2px;width:20px;height:20px;border-radius:50%;background:#fff;border:2px solid var(--line)}
.track li.done{color:var(--ink)}
.track li.done::before{background:var(--blue);border-color:var(--blue)}
.track li.now{color:var(--blue);font-weight:600}
.track li.now::before{background:var(--accent);border-color:var(--blue);animation:pulse 1.8s infinite}
.track li:last-child{padding-bottom:0}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(107,188,255,.8)}100%{box-shadow:0 0 0 12px rgba(107,188,255,0)}}

/* Bubbles */
.bubble{position:absolute;bottom:-30px;border-radius:50%;border:2px solid rgba(255,255,255,.28);animation:rise linear infinite;z-index:1}
.bubble:nth-child(1){left:6%;width:18px;height:18px;animation-duration:11s}
.bubble:nth-child(2){left:22%;width:30px;height:30px;animation-duration:15s;animation-delay:3s}
.bubble:nth-child(3){left:48%;width:14px;height:14px;animation-duration:10s;animation-delay:1s}
.bubble:nth-child(4){left:70%;width:24px;height:24px;animation-duration:14s;animation-delay:5s}
.bubble:nth-child(5){left:90%;width:16px;height:16px;animation-duration:12s;animation-delay:2s}
@keyframes rise{to{transform:translateY(-560px);opacity:0}}
.wave{position:absolute;left:0;bottom:-1px;width:100%;height:90px;display:block;z-index:2}

/* Features */
.features{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;margin-top:-30px;position:relative;z-index:3}
.card{background:#fff;border-radius:16px;padding:28px 24px;border:1px solid var(--line);border-top:5px solid var(--accent);box-shadow:0 6px 18px rgba(10,92,158,.08);transition:transform .2s,box-shadow .2s}
.card:hover{transform:translateY(-6px);box-shadow:0 16px 30px rgba(10,92,158,.16)}
.card .icon{width:54px;height:54px;border-radius:14px;background:linear-gradient(135deg,var(--blue),var(--aqua));color:#fff;display:grid;place-items:center;font-size:26px;margin-bottom:22px}
.card h3{font-size:19px;margin-bottom:8px;color:var(--deep)}
.card p{font-size:16px;line-height:1.6;color:var(--gray)}

/* How it works */
.how{margin-top:90px;text-align:center}
.how h2{font-size:clamp(30px,4vw,40px);color:var(--blue);margin-bottom:10px}
.how .sub{font-size:18px;color:var(--gray);margin-bottom:56px}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;text-align:left}
.step{position:relative;background:#fff;border:1px solid var(--line);border-top:5px solid var(--accent);border-radius:16px;padding:26px 24px 28px;box-shadow:0 6px 18px rgba(10,92,158,.08);transition:transform .2s,box-shadow .2s}
.step:hover{transform:translateY(-6px);box-shadow:0 16px 30px rgba(10,92,158,.16)}
.step .num{display:inline-flex;align-items:center;justify-content:center;min-width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,var(--blue),var(--aqua));color:#fff;font:700 22px "Playfair Display",serif;margin-bottom:18px}
.step h3{font-size:14px;letter-spacing:.14em;margin-bottom:10px;color:var(--blue)}
.step p{font-size:16px;line-height:1.65;color:var(--gray)}

/* CTA */
.cta{position:relative;overflow:hidden;margin:96px 0;border-radius:24px;padding:72px 24px;text-align:center;color:#fff;background:radial-gradient(circle at 15% 20%,rgba(58,160,238,.3),transparent 40%),radial-gradient(circle at 90% 90%,rgba(107,188,255,.16),transparent 40%),linear-gradient(135deg,var(--blue),var(--deep));box-shadow:0 24px 50px rgba(10,92,158,.3)}
.cta h2{position:relative;font-size:clamp(30px,4vw,42px);margin-bottom:14px}
.cta p{position:relative;font-size:19px;color:#dcebf7;margin-bottom:32px}
.cta .btn{position:relative;height:56px;padding:0 38px;font-size:18px}

/* Footer */
footer{margin-top:auto;background:var(--deep);border-top:4px solid var(--accent);color:#cfe3f4;padding:48px 0 28px}
.foot{display:flex;justify-content:center;align-items:center;gap:28px;flex-wrap:wrap}
.foot .brand b{font-size:20px}
.copy{margin-top:28px;padding-top:20px;border-top:1px solid rgba(107,188,255,.3);text-align:center;font-size:14px;color:#9fbfd9}

@media (max-width:1000px){.steps{grid-template-columns:repeat(2,1fr)}.features{grid-template-columns:repeat(2,1fr)}.hero .grid{grid-template-columns:1fr;padding-top:56px}.paper{max-width:460px;transform:none}}
@media (max-width:760px){
  .header .bar{height:auto;padding:12px 0;flex-wrap:wrap}
  .actions{width:100%}.actions .btn{flex:1;height:42px;padding:0 10px;font-size:14px}
  .features{grid-template-columns:1fr}
  .steps{grid-template-columns:1fr}
  .foot{justify-content:flex-start}
  .wave{height:50px}.hero .grid{padding-bottom:100px}
}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}html{scroll-behavior:auto}}
</style>
</head>
<body>


<header class="header">
  <div class="wrap bar">
    <a class="brand" href="landing.html">
      <img class="logo" src="{{ asset('images/logo.png') }}" alt="College of Fisheries and Aquatic Science logo">
      <span><b>Research Repository System</b><small>CSU Aparri - College of Fisheries and Aquatic Science</small></span>
    </a>
    <!-- login and register buttons -->
            <div class="ms-auto d-flex gap-2 home-nav-actions">
                @if (Route::has('public'))
                    <a href="{{ route('public') }}" class="btn outline">Browse Repository</a>
                @else
                    <a href="#" class="btn">Browse Repository</a>
                @endif

                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn gold">Student Login</a>
                @else
                    <a href="#" class="btn btn-outline-light text-white fw-bold px-2 py-1">Student Login</a>
                @endif
            </div>

  </div>
</header>


  <!-- Design sa content.blade.php -->
    <div class="container mt-4">
        @yield('home')
    </div>



<footer>
  <div class="wrap">
    <div class="foot">
      <div class="brand">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="College of Fisheries and Aquatic Science logo">
        <span><b>Cagayan State University - Aparri Campus</b><small>College of Fisheries and Aquatic Science Research Repository</small></span>
      </div>
    </div>
    <p class="copy">Research Repository System, CSU Aparri - College of Fisheries and Aquatic Science</p>
  </div>
</footer>

</body>
</html>
