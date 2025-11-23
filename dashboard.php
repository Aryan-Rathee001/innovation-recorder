<?php
include 'db.php';

$result = $conn->query("SELECT category, COUNT(*) as count FROM ideas GROUP BY category");
$categories = [];
$categoryData = [];
$categoryColors = [];
$colorPalette = [
    'rgba(147, 51, 234, 0.7)',   // Purple
    'rgba(59, 130, 246, 0.7)',    // Blue
    'rgba(34, 197, 94, 0.7)',     // Green
    'rgba(251, 146, 60, 0.7)',    // Orange
    'rgba(239, 68, 68, 0.7)',     // Red
    'rgba(236, 72, 153, 0.7)',    // Pink
    'rgba(14, 165, 233, 0.7)',    // Sky
    'rgba(168, 85, 247, 0.7)'     // Violet
];

$colorIndex = 0;
while($row = $result->fetch_assoc()) {
    $categories[] = $row['category'];
    $categoryData[] = $row['count'];
    $categoryColors[] = $colorPalette[$colorIndex % count($colorPalette)];
    $colorIndex++;
}

$result = $conn->query("SELECT ideas.id, ideas.title, 
    (SELECT AVG(rating) FROM ratings WHERE idea_id = ideas.id) as avg_rating,
    (SELECT COUNT(*) FROM ratings WHERE idea_id = ideas.id) as rating_count
    FROM ideas
    WHERE (SELECT COUNT(*) FROM ratings WHERE idea_id = ideas.id) > 0
    ORDER BY avg_rating DESC LIMIT 5");
$topIdeas = [];
$topRatings = [];
$ratingCounts = [];
$ratingColors = [];

while($row = $result->fetch_assoc()) {
    $title = strlen($row['title']) > 30 ? substr($row['title'], 0, 27) . '...' : $row['title'];
    $topIdeas[] = $title;
    $rating = round($row['avg_rating'], 1);
    $topRatings[] = $rating;
    $ratingCounts[] = $row['rating_count'];
    
    // Color based on rating
    if($rating >= 4.5) {
        $ratingColors[] = 'rgba(34, 197, 94, 0.8)'; // Excellent - Green
    } elseif($rating >= 4.0) {
        $ratingColors[] = 'rgba(59, 130, 246, 0.8)'; // Very Good - Blue
    } elseif($rating >= 3.5) {
        $ratingColors[] = 'rgba(251, 146, 60, 0.8)'; // Good - Orange
    } else {
        $ratingColors[] = 'rgba(239, 68, 68, 0.8)'; // Fair - Red
    }
}

$stats = $conn->query("SELECT 
    (SELECT COUNT(*) FROM ideas) as total_ideas,
    (SELECT COUNT(*) FROM ratings) as total_ratings,
    (SELECT COUNT(DISTINCT submitter) FROM ideas) as total_submitters")->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Innovation Hub</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f5f5f5; }
        main { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            background: linear-gradient(135deg, #ffffff, #f8fafc); 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            text-align: center;
            border: 1px solid rgba(147, 51, 234, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .stat-number { 
            font-size: 36px; 
            font-weight: bold; 
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .chart-card { 
            background: linear-gradient(135deg, #ffffff, #f8fafc); 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            margin-bottom: 25px;
            border: 1px solid rgba(147, 51, 234, 0.1);
        }
        .chart-card h2 { 
            color: #1e293b; 
            margin-bottom: 25px; 
            font-size: 24px;
            font-weight: 600;
        }
        .chart-container { position: relative; height: 300px; }
        .rating-info {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="index.html" class="header-logo">
                <span class="logo-icon">✨</span>
                <div>
                    <h1>INNOVATION HUB</h1>
                    <span>Where Ideas Come to Life</span>
                </div>
            </a>
            <nav>
                <a href="index.html">🏠 Home</a>
                <a href="submit_idea.html">💡 Submit Idea</a>
                <a href="view_ideas.php">🔍 View Ideas</a>
                <a href="dashboard.php">📊 Dashboard</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Ideas</h3>
                <div class="stat-number"><?php echo $stats['total_ideas']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Ratings</h3>
                <div class="stat-number"><?php echo $stats['total_ratings']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Submitters</h3>
                <div class="stat-number"><?php echo $stats['total_submitters']; ?></div>
            </div>
        </div>

        <div class="chart-card">
            <h2>📊 Ideas by Category</h2>
            <div class="chart-container">
                <?php if(count($categoryData) > 0): ?>
                    <canvas id="categoryChart"></canvas>
                <?php else: ?>
                    <p>No data yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card">
            <h2>⭐ Top Rated Ideas</h2>
            <div class="chart-container">
                <?php if(count($topRatings) > 0): ?>
                    <canvas id="topIdeasChart"></canvas>
                    <div class="rating-info">Colors: Green (4.5+), Blue (4.0+), Orange (3.5+), Red (<3.5)</div>
                <?php else: ?>
                    <p>No ratings yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>
            ✨ Instagram: <a href="https://www.instagram.com/rxthee7" target="_blank">rxthee7</a>
            | 👻 Snapchat: <a href="https://www.snapchat.com/add/rxthee7" target="_blank">rxthee7</a>
            | 📧 Email: <a href="mailto:aryanpc1195@gmail.com">aryanpc1195@gmail.com</a>
            | 📞 <a href="tel:+917814232567">+91 7814232567</a>
        </p>
    </footer>

    <script>
        const categories = <?php echo json_encode($categories); ?>;
        const catData = <?php echo json_encode($categoryData); ?>;
        const categoryColors = <?php echo json_encode($categoryColors); ?>;
        const topIdeas = <?php echo json_encode($topIdeas); ?>;
        const topRatings = <?php echo json_encode($topRatings); ?>;
        const ratingColors = <?php echo json_encode($ratingColors); ?>;
        const ratingCounts = <?php echo json_encode($ratingCounts); ?>;

        // Chart defaults
        Chart.defaults.font.family = 'Arial, sans-serif';
        Chart.defaults.plugins.legend.display = false;

        if(categories.length > 0) {
            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Number of Ideas',
                        data: catData,
                        backgroundColor: categoryColors,
                        borderColor: categoryColors.map(color => color.replace('0.7', '1')),
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: { size: 12 },
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }

        if(topIdeas.length > 0) {
            new Chart(document.getElementById('topIdeasChart'), {
                type: 'bar',
                data: {
                    labels: topIdeas.map((idea, index) => `${idea} (${ratingCounts[index]} votes)`),
                    datasets: [{
                        label: 'Average Rating',
                        data: topRatings,
                        backgroundColor: ratingColors,
                        borderColor: ratingColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    return `Rating: ${context.parsed.y}/5 (${ratingCounts[context.dataIndex]} votes)`;
                                }
                            }
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: { size: 12 },
                                stepSize: 0.5
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 11 },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
