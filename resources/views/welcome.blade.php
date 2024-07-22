<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Learnovation: AI Holistic Feedback</title>
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        @vite('resources/css/app.css')

    </head>
    <body>
        <div class="container mx-auto w-5/6 mt-12">

            <div class="text-4xl font-bold leading-normal  inline box-decoration-clone bg-orange-500 text-gray-100 p-4 [filter:url('#goo')]" contenteditable="true">Student Performance Insight <br/>with  Learnosity - AI holistic feedback</div>

            <div class="text-xl  mt-8 font-bold leading-normal box-decoration-clone bg-gray-200 text-gray-700 p-4 [filter:url('#goo')]" contenteditable="true">Unlock the power of advanced AI technology to transform the way you assess and support your students. Our cutting-edge application delivers comprehensive, personalized assessments and holistic feedback, enabling educators to understand each student's unique strengths and areas for improvement.</div>

            <div div class="mt-6">
                <a href="/generate" class=" flex justify-center items-center group relative h-12 w-48 overflow-hidden rounded-2xl bg-yellow-400 text-lg font-bold font-bold text-slate-800 border-amber-50">
                    Try it now!
                    <div class="absolute inset-0 h-full w-full scale-0 rounded-2xl transition-all duration-300 group-hover:scale-100 group-hover:bg-white/30"></div>
                </a>
            </div>

            <!-- Filter: https://css-tricks.com/gooey-effect/ -->
            <svg style="visibility: hidden; position: absolute;" width="0" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1">
                <defs>
                    <filter id="goo"><feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
                        <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo" />
                        <feComposite in="SourceGraphic" in2="goo" operator="atop"/>
                    </filter>
                </defs>
            </svg>
        </div>
    </body>
</html>
