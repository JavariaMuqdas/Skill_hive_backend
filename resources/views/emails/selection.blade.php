<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selection Confirmation</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .wrapper { background-color: #f4f7f6; padding: 40px 0; }
        .container { width: 90%; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%); padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
        .content { padding: 30px; }
        .welcome-text { font-size: 18px; color: #2c3e50; font-weight: bold; }
        .job-box { background-color: #f8faff; border: 1px solid #e1e8f0; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .job-title { color: #4A90E2; font-size: 20px; display: block; margin-top: 5px; }
        .next-steps { border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; }
        .footer { background-color: #fafafa; padding: 20px; text-align: center; font-size: 13px; color: #888; border-top: 1px solid #eee; }
        .button { display: inline-block; padding: 12px 25px; background-color: #4A90E2; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Official Selection Notice</h1>
            </div>
            
            <div class="content">
                <p class="welcome-text">Congratulations, {{ $name }}!</p>
                
                <p>We are absolutely thrilled to inform you that after a thorough review of your performance and skills, you have been <strong>selected</strong> for the following position:</p>
                
                <div class="job-box">
                    <small>Position Offered:</small>
                    <strong class="job-title">{{ $jobTitle }}</strong>
                    <p style="margin-bottom: 0;">at <strong>{{ $companyName }}</strong></p>
                </div>

                <p>Your expertise and enthusiasm stood out to us, and we believe you will be a valuable addition to our team. We are excited to see the impact you will make.</p>

                <div class="next-steps">
                    <p><strong>What happens next?</strong></p>
                    <p>Our HR department is currently preparing your formal offer letter and onboarding documents. You will receive another email within 24-48 hours with the full details regarding your start date and contract.</p>
                </div>

                <p>If you have any immediate questions, feel free to reach out to us.</p>
                
                <p>Welcome aboard!</p>
            </div>

            <div class="footer">
                <p>Best Regards,<br>
                <strong>The Hiring Team</strong><br>
                {{ $companyName }}</p>
            </div>
        </div>
    </div>
</body>
</html>