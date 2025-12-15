<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $tutor->user->name ?? 'Tutor' }} — Profile</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 14px; }
        .label { font-weight: bold; color: #333; }
        .value { margin-top: 4px; }
        .subjects { display: inline-block; padding: 4px 8px; background: #f3f4f6; border-radius: 4px; margin-right: 6px; margin-bottom: 6px; }
        .meta { font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tutor->user->name ?? 'Tutor' }}</h1>
        <div class="meta">Generated at: {{ $generated_at }}</div>
    </div>

    <div class="section">
        <div class="label">Headline</div>
        <div class="value">{{ $tutor->headline }}</div>
    </div>

    <div class="section">
        <div class="label">About</div>
        <div class="value">{{ $tutor->about }}</div>
    </div>

    <div class="section">
        <div class="label">Subjects</div>
        <div class="value">
            @foreach($tutor->subjects as $s)
                <div class="subjects">{{ $s->name }} @if($s->pivot->level) ({{ $s->pivot->level }}) @endif</div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <div class="label">Details</div>
        <div class="value">
            <div>Price / hour: ₹{{ number_format($tutor->price_per_hour, 2) }}</div>
            <div>City: {{ $tutor->city }}</div>
            <div>Experience: {{ $tutor->experience_years }} years</div>
            <div>Teaching mode: {{ ucfirst($tutor->teaching_mode) }}</div>
            <div>Verified: {{ $tutor->verified ? 'Yes' : 'No' }}</div>
            <div>Moderation: {{ ucfirst($tutor->moderation_status) }}</div>
            <div>Rating: {{ $tutor->rating_avg }} ({{ $tutor->rating_count }} reviews)</div>
        </div>
    </div>

    <div class="section">
        <div class="label">Contact</div>
        <div class="value">
            Email: {{ $tutor->user->email ?? '-' }}<br>
            Phone: {{ $tutor->user->phone ?? '-' }}
        </div>
    </div>

    <footer style="position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 10px; color: #999;">
    Namate24 — Tutor Profile Export
    </footer>
</body>
</html>