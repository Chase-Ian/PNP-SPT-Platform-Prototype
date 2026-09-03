{{-- resources/views/certificates/template.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 80px; }
        h1 { color: #1e3a8a; font-size: 32px; }
        .name { font-size: 28px; margin: 30px 0; font-weight: bold; }
        .course { font-size: 18px; color: #444; }
        .serial { margin-top: 60px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <h1>Certificate of Completion</h1>
    <p>Philippine National Police Learning Management System</p>
    <p>This certifies that</p>
    <div class="name">{{ $name }}</div>
    <p class="course">has successfully completed</p>
    <p class="course"><strong>{{ $course }}</strong></p>
    <p>Issued on {{ $date }}</p>
    <div class="serial">Serial ID: {{ $serial }}</div>
</body>
</html>