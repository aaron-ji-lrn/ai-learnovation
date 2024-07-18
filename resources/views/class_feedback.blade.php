<!-- resources/views/feedback.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Assessment Feedback</title>
</head>
<body>
    <div id="app">
        <h1>Assessment Feedback</h1>
        <div>
            @php
                use Michelf\Markdown;
                echo Markdown::defaultTransform($feedback);
            @endphp
        </div>
        <div>
            <h2>Students</h2>
            <ul class="list-group">
                @foreach($students as $student)
                    <li class="list-group-item">
                        <a href="student/{{ $student }}/feedback">{{ $student }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

</body>
</html>
