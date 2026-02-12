<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $recipe->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 40px;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f59e0b;
        }
        .header h1 {
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .header .meta {
            color: #6b7280;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 24px;
            color: #f59e0b;
            margin-bottom: 15px;
            border-left: 4px solid #f59e0b;
            padding-left: 10px;
        }
        .description {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 20px;
            font-style: italic;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .info-item {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .info-item strong {
            display: block;
            color: #f59e0b;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .info-item span {
            color: #6b7280;
            font-size: 14px;
        }
        .ingredients-list, .instructions-list {
            list-style: none;
        }
        .ingredients-list li {
            padding: 10px;
            margin-bottom: 8px;
            background: #f9fafb;
            border-left: 3px solid #10b981;
            padding-left: 15px;
        }
        .instructions-list li {
            padding: 15px;
            margin-bottom: 12px;
            background: #f9fafb;
            border-left: 3px solid #3b82f6;
            padding-left: 20px;
            counter-increment: step-counter;
            position: relative;
        }
        .instructions-list {
            counter-reset: step-counter;
        }
        .instructions-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: -15px;
            top: 15px;
            background: #3b82f6;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .nutrition {
            background: #f0fdf4;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #10b981;
        }
        .nutrition-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        .nutrition-item {
            text-align: center;
        }
        .nutrition-item strong {
            display: block;
            color: #10b981;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .nutrition-item span {
            color: #6b7280;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $recipe->title }}</h1>
        <div class="meta">
            {{ $recipe->cuisine }} • {{ ucfirst($recipe->difficulty) }} •
            {{ $recipe->prep_time + $recipe->cook_time }} minutes •
            {{ $recipe->servings }} servings
        </div>
    </div>

    @if($recipe->description)
    <div class="description">
        {{ $recipe->description }}
    </div>
    @endif

    <div class="info-grid">
        <div class="info-item">
            <strong>{{ $recipe->prep_time }}</strong>
            <span>Prep Time (min)</span>
        </div>
        <div class="info-item">
            <strong>{{ $recipe->cook_time }}</strong>
            <span>Cook Time (min)</span>
        </div>
        <div class="info-item">
            <strong>{{ $recipe->servings }}</strong>
            <span>Servings</span>
        </div>
    </div>

    <div class="section">
        <h2>Ingredients</h2>
        <ul class="ingredients-list">
            @foreach($recipe->ingredients as $ingredient)
            <li>
                <strong>{{ $ingredient['amount'] ?? '' }} {{ $ingredient['unit'] ?? '' }}</strong>
                {{ $ingredient['item'] ?? '' }}
            </li>
            @endforeach
        </ul>
    </div>

    <div class="section">
        <h2>Instructions</h2>
        <ol class="instructions-list">
            @foreach($recipe->instructions as $instruction)
            <li>{{ $instruction }}</li>
            @endforeach
        </ol>
    </div>

    @if($recipe->nutritional_info)
    <div class="section">
        <h2>Nutritional Information</h2>
        <div class="nutrition">
            <div class="nutrition-grid">
                <div class="nutrition-item">
                    <strong>{{ $recipe->nutritional_info['calories'] ?? 'N/A' }}</strong>
                    <span>Calories</span>
                </div>
                <div class="nutrition-item">
                    <strong>{{ $recipe->nutritional_info['protein'] ?? 'N/A' }}g</strong>
                    <span>Protein</span>
                </div>
                <div class="nutrition-item">
                    <strong>{{ $recipe->nutritional_info['carbs'] ?? 'N/A' }}g</strong>
                    <span>Carbs</span>
                </div>
                <div class="nutrition-item">
                    <strong>{{ $recipe->nutritional_info['fat'] ?? 'N/A' }}g</strong>
                    <span>Fat</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Generated by AI-Powered Recipe Generator</p>
        <p>{{ route('recipes.show', $recipe->slug) }}</p>
    </div>
</body>
</html>
