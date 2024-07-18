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
            <h2>Class</h2>
            <a href="/assessments/1/feedback">Back to class feedback</a>
        </div>
    </div>
</body>
</html>
