<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Contact Submission</title>
</head>
<body>
    <h2>New Contact Submission</h2>
    
    <p><strong>User Type:</strong> {{ ucfirst($submission->user_type) }}</p>
    
    @if(in_array($submission->user_type, ['tutor', 'student']))
        <p><strong>First Name:</strong> {{ $submission->first_name }}</p>
        <p><strong>Last Name:</strong> {{ $submission->last_name }}</p>
        <p><strong>Email:</strong> {{ $submission->email }}</p>
        <p><strong>Mobile:</strong> {{ $submission->mobile }}</p>
    @elseif($submission->user_type === 'organisation')
        <p><strong>Organization Name:</strong> {{ $submission->organization_name }}</p>
        <p><strong>Contact Person:</strong> {{ $submission->contact_person }}</p>
        <p><strong>Email:</strong> {{ $submission->email }}</p>
    @endif
    
    @if($submission->message)
        <p><strong>Message:</strong></p>
        <p>{{ $submission->message }}</p>
    @endif
    
    <hr>
    <p><strong>Submitted At:</strong> {{ $submission->created_at->format('Y-m-d H:i:s') }}</p>
    @if($submission->ip_address)
        <p><strong>IP Address:</strong> {{ $submission->ip_address }}</p>
    @endif
    @if($submission->user_agent)
        <p><strong>User Agent:</strong> {{ $submission->user_agent }}</p>
    @endif
</body>
</html>
