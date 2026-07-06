<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Invitation</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; border: 1px solid #eee; padding: 20px; border-radius: 10px; }
        .header { border-bottom: 2px solid #4A90E2; padding-bottom: 10px; margin-bottom: 20px; }
        .details { background-color: #f9f9f9; padding: 15px; border-left: 4px solid #4A90E2; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 0.9em; color: #777; }
        .highlight { color: #4A90E2; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Interview Invitation</h2>
        </div>

        <p>Dear <strong>{{ $name }}</strong>,</p>

        <p>Thank you for applying for the <span class="highlight">{{ $jobTitle }}</span> position at <strong>{{ $companyName }}</strong>. We have reviewed your profile and are pleased to invite you for an interview to discuss how your skills align with our team's needs.</p>

        <p>Here are the details for your interview session:</p>

        <div class="details">
            <p>📅 <strong>Date:</strong> {{ $interviewDate }}</p>
            <p>🕒 <strong>Time:</strong> {{ $interviewTime }}</p>
            <p>📍 <strong>Location/Link:</strong> {{ $location }}</p>
            <p>👤 <strong>Interviewer:</strong> {{ $interviewerName }}</p>
        </div>

        <p>Please confirm your availability for this slot by replying to this email. If you have any questions or need to reschedule, feel free to reach out.</p>

        <p>We look forward to speaking with you!</p>

        <div class="footer">
            <p>Best Regards,<br>
            <strong>{{ $hrName }}</strong><br>
            HR Department | {{ $companyName }}</p>
        </div>
    </div>
</body>
</html>