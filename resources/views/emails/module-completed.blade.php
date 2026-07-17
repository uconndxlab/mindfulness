@extends('emails.layouts.base')

@section('title', 'Your '.config('app.name').' Journey Report')

@section('skip_footer')
@endsection

@section('content')
    @php
        $accentColor = $module->color ?? '#642F83';
        $flowerColor = strtolower($module->flowerColorName());
        $contactEmail = config('mail.contact_email');
    @endphp

    <h1 style="color: {{ $accentColor }}; font-size: 24px; margin-bottom: 20px;">
        Your {{ config('app.name') }} Journey Report - Part {{ $partOrder }} ({{ $module->flowerColorName() }} Flower)
    </h1>

    <p style="margin-bottom: 15px;">
        Congratulations on completing Part {{ $partOrder }} of the {{ config('app.name') }} program and reaching a fully grown
        {{ $flowerColor }} flower! This is a milestone that needs celebration! Be proud of the progress you have made and
        the time you took for yourself and your family in this {{ config('app.name') }} journey!
    </p>

    <p style="margin-bottom: 20px;">
        Let's take a look at your journey so far. What did you tell us about your levels of pleasant and unpleasant
        emotions, presence in parenting, and quality of awareness in the Quick Check-Ins and Rate My Awareness? Below is a
        summary of your scores.
    </p>

    @if (!empty($chartData['emotions']))
        <h2 style="color: {{ $accentColor }}; font-size: 18px; margin: 24px 0 12px;">1. Rate My Emotions</h2>
        <img src="{{ $message->embedData($chartData['emotions'], 'emotions.png', 'image/png') }}" alt="Rate My Emotions" width="560" style="max-width: 100%; height: auto; display: block; margin-bottom: 24px;">
    @endif

    @if (!empty($chartData['presence']))
        <h2 style="color: {{ $accentColor }}; font-size: 18px; margin: 24px 0 12px;">2. Rate My Presence in Parenting</h2>
        <img src="{{ $message->embedData($chartData['presence'], 'presence.png', 'image/png') }}" alt="Rate My Presence in Parenting" width="560" style="max-width: 100%; height: auto; display: block; margin-bottom: 24px;">
    @endif

    @if (!empty($chartData['awareness_quality']))
        <h2 style="color: {{ $accentColor }}; font-size: 18px; margin: 24px 0 12px;">3. Quality of awareness</h2>
        <img src="{{ $message->embedData($chartData['awareness_quality'], 'awareness-quality.png', 'image/png') }}" alt="Daily Check-Ins and Final Awareness Score" width="560" style="max-width: 100%; height: auto; display: block; margin-bottom: 24px;">
    @endif

    <h2 style="color: {{ $accentColor }}; font-size: 18px; margin: 24px 0 12px;">What do these scores mean?</h2>

    <p style="margin-bottom: 15px;">
        These scores offer one snapshot of your experience, but they don't tell the whole story. Many people notice gradual
        improvements over time, while others experience ups and downs from week to week—especially during a stressful life
        transition such as divorce. Changes in mindfulness often begin with noticing thoughts and emotions more clearly,
        which may not always be reflected in higher scores right away.
    </p>

    <p style="margin-bottom: 10px;">As you review your journey, we encourage you to reflect on these questions:</p>

    <ul style="margin: 0 0 20px 20px; padding: 0;">
        <li style="margin-bottom: 8px;">Did you pause before reacting in a difficult moment?</li>
        <li style="margin-bottom: 8px;">Did you become more aware of your thoughts or emotions?</li>
        <li style="margin-bottom: 8px;">Did you show yourself or your child a little more patience or compassion?</li>
        <li style="margin-bottom: 8px;">Did you remember to use a {{ config('app.name') }} practice when you needed it?</li>
    </ul>

    <p style="margin-bottom: 20px;">
        If so, they are all meaningful signs of personal growth, even if your scores changed only a little—or not at all.
    </p>

    <p style="margin-bottom: 10px; font-weight: bold;">Capture your story beyond the scores.</p>

    <p style="margin-bottom: 20px;">
        Consider writing a brief journal entry about what you've learned, challenges you've faced, or moments that stood out
        to you. Visit your journal here:
        <a href="{{ url('/journal') }}" style="color: {{ $accentColor }};">{{ url('/journal') }}</a>
    </p>

    <p style="margin-bottom: 20px; font-weight: bold; color: {{ $accentColor }};">
        Do you have any questions? Please feel free to email us:
        <a href="mailto:{{ $contactEmail }}" style="color: {{ $accentColor }};">{{ $contactEmail }}</a>
    </p>

    <p style="margin-bottom: 15px;">
        Keep carrying the {{ config('app.name') }} practices with you as you continue in this journey!
    </p>

    <p style="margin-bottom: 5px;">Warmly,</p>
    <p style="margin-bottom: 15px;">The {{ config('app.name') }} Team</p>
@endsection
