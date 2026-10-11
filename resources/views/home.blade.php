@extends('layouts.home_layout')

@section('title', 'Home')

@section('home')


    <br><br><br>


    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="upload" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></symbol>
  <symbol id="file" viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></symbol>
  <symbol id="search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></symbol>
  <symbol id="award" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></symbol>
  <symbol id="check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></symbol>
  <symbol id="book" viewBox="0 0 24 24"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></symbol>
</svg>



<section class="hero">
  <span class="bubble"></span><span class="bubble"></span><span class="bubble"></span><span class="bubble"></span><span class="bubble"></span>
  <div class="wrap">

    <div style="text-align: center; justify-items: center; margin-top: 10px;">
        <div class="mb-4">
            <img src="{{ asset('images/cfas.png') }}"
                 alt="Cagayan State University - Aparri"
                 class="img-fluid"
                 style="max-width: 180px; height: auto;">
        </div>
        <h1>Research Repository System</h1><br>
        <h5>Discover, Access,<br>Preserve fisheries in one centralized repository</h5>
    </div>

  </div>
  <svg class="wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path fill="#f4f8fb" d="M0 50C180 100 360 0 600 40s420 60 600 10c90-25 180-20 240-5V90H0Z"/></svg>
</section>

<main class="wrap">

  <section class="how" id="how">
    <h2>How It Works</h2>
    <p class="sub">Six simple steps from signing in to tracking your research</p>
    <div class="steps">
      <div class="step"><div class="num">01</div><h3>LOG IN</h3><p>Sign in using your Email or Student ID Number and password.</p></div>
      <div class="step"><div class="num">02</div><h3>SEARCH</h3><p>Find available research papers and theses by searching keywords, titles, or authors.</p></div>
      <div class="step"><div class="num">03</div><h3>VIEW</h3><p>Open a research item to view its details, abstract, and available content.</p></div>
      <div class="step"><div class="num">04</div><h3>UPLOAD</h3><p>Authorized students can upload completed theses for review and approval.</p></div>
      <div class="step"><div class="num">05</div><h3>ASK AQUA</h3><p>Ask Aqua AI questions, get research assistance, generate summaries, and discover related research.</p></div>
      <div class="step"><div class="num">06</div><h3>TRACK</h3><p>Monitor your submissions, request status, notifications, and saved research from your account.</p></div>
    </div>
  </section>

  <section class="cta">
    <h2>Ready to Share Your Research?</h2>
    <p>Join fellow students in contributing to fisheries research excellence</p>
    <button class="btn gold">
                <a href="{{ route('login') }}" class="btn gold">Login to get started</a>
            </button>
  </section>
</main>




    <br><br><br><br><br><br><br>
@endsection
