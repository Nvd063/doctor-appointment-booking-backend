<!DOCTYPE html>
<html>
<head>
    <title>Appointment Confirmed</title>
</head>
<body style="font-family: Arial, sans-serif;">
    
    <h2>Hello, {{ $appointment->patient->name }}!</h2>
    
    <p>Your appointment has been successfully booked.</p>
    
    <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px;">
        <p><strong>Doctor:</strong> Dr. {{ $appointment->doctor->name }}</p>
        <p><strong>Date:</strong> {{ $appointment->appointment_date }}</p>
        <p><strong>Time:</strong> {{ $appointment->appointment_time }}</p>
    </div>

    <p>Thank you for choosing MediBook!</p>
</body>
</html>