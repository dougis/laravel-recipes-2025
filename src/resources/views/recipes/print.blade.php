<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Recipe: {{ $recipe->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .recipe-header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .recipe-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .recipe-meta {
            color: #666;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .ingredients, .instructions {
            white-space: pre-line;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="recipe-header">
        <h1 class="recipe-title">{{ $recipe->name }}</h1>
        <div class="recipe-meta">
            @if($recipe->servings)
                Serves: {{ $recipe->servings }} |
            @endif
            @if($recipe->prep_time)
                Prep Time: {{ $recipe->prep_time }} minutes |
            @endif
            @if($recipe->cook_time)
                Cook Time: {{ $recipe->cook_time }} minutes
            @endif
        </div>
    </div>

    @if($recipe->description)
        <div class="section">
            <h2 class="section-title">Description</h2>
            <p>{{ $recipe->description }}</p>
        </div>
    @endif

    <div class="section">
        <h2 class="section-title">Ingredients</h2>
        <div class="ingredients">{{ $recipe->ingredients }}</div>
    </div>

    <div class="section">
        <h2 class="section-title">Instructions</h2>
        <div class="instructions">{{ $recipe->instructions }}</div>
    </div>

    @if($recipe->notes)
        <div class="section">
            <h2 class="section-title">Notes</h2>
            <div class="notes">{{ $recipe->notes }}</div>
        </div>
    @endif
</body>
</html>