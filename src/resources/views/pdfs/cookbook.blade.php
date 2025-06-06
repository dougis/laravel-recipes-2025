<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cookbook->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .page {
            padding: 20px;
            page-break-after: always;
        }
        .cover {
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .cover h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .cover p {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 0;
        }
        .toc h2 {
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            color: #2c3e50;
        }
        .toc-list {
            list-style-type: none;
            padding: 0;
        }
        .toc-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        .toc-page {
            font-weight: bold;
        }
        .recipe-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .recipe-meta {
            margin-bottom: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }
        .recipe-meta p {
            margin: 5px 0;
        }
        .recipe-section {
            margin-bottom: 20px;
        }
        h2 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        h3 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        ul, ol {
            padding-left: 20px;
        }
        .nutrition {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .nutrition h3 {
            border-bottom: none;
            margin-top: 0;
        }
        .nutrition-table {
            width: 100%;
            border-collapse: collapse;
        }
        .nutrition-table td {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .nutrition-table td:first-child {
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .page-number {
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
            margin-top: 20px;
        }
        .section-divider {
            font-size: 22px;
            color: #2c3e50;
            margin: 30px 0;
            text-align: center;
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="page cover">
        <h1>{{ $cookbook->name }}</h1>
        @if($cookbook->description)
            <p>{{ $cookbook->description }}</p>
        @endif
        <p class="author">Compiled by {{ $cookbook->user->name ?? 'Unknown' }}</p>
        <p class="date">{{ date('F j, Y') }}</p>
    </div>
    
    <!-- Table of Contents -->
    <div class="page toc">
        <h2>Table of Contents</h2>
        <ul class="toc-list">
            @php $page = 3; @endphp
            @foreach($cookbook->recipes as $index => $recipe)
                <li class="toc-item">
                    <span>{{ $index + 1 }}. {{ $recipe->name }}</span>
                    <span class="toc-page">{{ $page }}</span>
                </li>
                @php $page += 2; @endphp
            @endforeach
        </ul>
    </div>
    
    <!-- Recipes -->
    @foreach($cookbook->recipes as $index => $recipe)
        <div class="page">
            <div class="recipe-header">
                <h1>{{ $recipe->name }}</h1>
            </div>
            
            <div class="recipe-meta">
                @if($recipe->source && $recipe->source->name)
                    <p><strong>Source:</strong> {{ $recipe->source->name }}</p>
                @endif
                
                @if($recipe->classification && $recipe->classification->name)
                    <p><strong>Classification:</strong> {{ $recipe->classification->name }}</p>
                @endif
                
                @if($recipe->servings)
                    <p><strong>Servings:</strong> {{ $recipe->servings }}</p>
                @endif
                
                @if($recipe->meals && count($recipe->meals))
                    <p><strong>Meal Types:</strong> {{ $recipe->meals->pluck('name')->implode(', ') }}</p>
                @endif
                
                @if($recipe->courses && count($recipe->courses))
                    <p><strong>Courses:</strong> {{ $recipe->courses->pluck('name')->implode(', ') }}</p>
                @endif
                
                @if($recipe->preparations && count($recipe->preparations))
                    <p><strong>Preparation Methods:</strong> {{ $recipe->preparations->pluck('name')->implode(', ') }}</p>
                @endif
            </div>
            
            <div class="recipe-section">
                <h2>Ingredients</h2>
                <div>{!! nl2br(e($recipe->ingredients)) !!}</div>
            </div>
            
            <div class="recipe-section">
                <h2>Instructions</h2>
                <div>{!! nl2br(e($recipe->instructions)) !!}</div>
            </div>
            
            @if($recipe->notes)
                <div class="recipe-section">
                    <h2>Notes</h2>
                    <div>{!! nl2br(e($recipe->notes)) !!}</div>
                </div>
            @endif
            
            @if($recipe->calories || $recipe->fat || $recipe->cholesterol || $recipe->sodium || $recipe->protein)
                <div class="nutrition">
                    <h3>Nutritional Information</h3>
                    <table class="nutrition-table">
                        @if($recipe->calories)
                            <tr>
                                <td>Calories</td>
                                <td>{{ $recipe->calories }}</td>
                            </tr>
                        @endif
                        
                        @if($recipe->fat)
                            <tr>
                                <td>Fat</td>
                                <td>{{ $recipe->fat }}g</td>
                            </tr>
                        @endif
                        
                        @if($recipe->cholesterol)
                            <tr>
                                <td>Cholesterol</td>
                                <td>{{ $recipe->cholesterol }}mg</td>
                            </tr>
                        @endif
                        
                        @if($recipe->sodium)
                            <tr>
                                <td>Sodium</td>
                                <td>{{ $recipe->sodium }}mg</td>
                            </tr>
                        @endif
                        
                        @if($recipe->protein)
                            <tr>
                                <td>Protein</td>
                                <td>{{ $recipe->protein }}g</td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endif
            
            <div class="page-number">Page {{ $index + 3 }}</div>
        </div>
    @endforeach
    
    <!-- Back Cover -->
    <div class="page cover">
        <h2>{{ $cookbook->name }}</h2>
        <p>Generated by Laravel Recipes 2025</p>
        <p>{{ date('F j, Y') }}</p>
    </div>
</body>
</html>
