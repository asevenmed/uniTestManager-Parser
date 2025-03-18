<?php
$jsonData = file_get_contents('test.php');
$data = json_decode($jsonData, true)['data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests Leaderboards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f9f9f9;
        }
        .leaderboard {
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            max-width: 800px;
        }
        .test-name {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .rating {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin: 5px 0;
        }
        button {
            margin: 10px 0;
            padding: 10px 20px;
            font-size: 1em;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function toggleDetails(id) {
            const moreDetails = document.getElementById('more-' + id);
            const btn = document.getElementById('btn-' + id);
            if (moreDetails.style.display === 'none') {
                moreDetails.style.display = 'block';
                btn.innerText = 'Show Less';
            } else {
                moreDetails.style.display = 'none';
                btn.innerText = 'Show More';
            }
        }
    </script>
</head>
<body>
    <h1>Tests Leaderboards</h1>
    <?php foreach ($data as $testKey => $testData): ?>
        <div class="leaderboard">
            <?php
                $lines = explode("\r\n", $testData);
                $testName = substr($lines[1], 11);
                $rating = strpos($lines[2], 'Rating:') !== false ? $lines[2] : 'Not rated';
                $results = array_slice($lines, 3);
            ?>
            <div class="test-name"><?= htmlspecialchars($testName) ?></div>
            <div class="rating"><?= htmlspecialchars($rating) ?></div>
            <ul>
                <?php foreach (array_slice($results, 0, 10) as $line): ?>
                    <li><?= htmlspecialchars($line) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php if (count($results) > 10): ?>
                <ul id="more-<?= htmlspecialchars($testKey) ?>" style="display: none;">
                    <?php foreach (array_slice($results, 10) as $line): ?>
                        <li><?= htmlspecialchars($line) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button id="btn-<?= htmlspecialchars($testKey) ?>" onclick="toggleDetails('<?= htmlspecialchars($testKey) ?>')">Show More</button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
