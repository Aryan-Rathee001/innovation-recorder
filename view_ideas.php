<?php
include 'db.php';

function getResources($category) {
    $resources = [
        'Technology' => [
            ['name' => 'GitHub Trending', 'type' => 'GitHub', 'url' => 'https://github.com/trending'],
            ['name' => 'Awesome Open Source', 'type' => 'GitHub', 'url' => 'https://github.com/topics/awesome'],
            ['name' => 'r/programming', 'type' => 'Reddit', 'url' => 'https://www.reddit.com/r/programming/']
        ],
        'Healthcare' => [
            ['name' => 'Open mHealth', 'type' => 'GitHub', 'url' => 'https://github.com/openmhealth'],
            ['name' => 'FHIR Standard', 'type' => 'Docs', 'url' => 'https://www.hl7.org/fhir/overview.html'],
            ['name' => 'r/healthIT', 'type' => 'Reddit', 'url' => 'https://www.reddit.com/r/healthIT/']
        ],
        'Education' => [
            ['name' => 'Open edX', 'type' => 'GitHub', 'url' => 'https://github.com/openedx/'],
            ['name' => 'FreeCodeCamp', 'type' => 'Projects', 'url' => 'https://www.freecodecamp.org/learn/'],
            ['name' => 'r/learnprogramming', 'type' => 'Reddit', 'url' => 'https://www.reddit.com/r/learnprogramming/']
        ],
        'Finance' => [
            ['name' => 'QuantConnect', 'type' => 'GitHub', 'url' => 'https://github.com/QuantConnect/Lean'],
            ['name' => 'Awesome Quant', 'type' => 'GitHub', 'url' => 'https://github.com/wilsonfreitas/awesome-quant'],
            ['name' => 'r/financialindependence', 'type' => 'Reddit', 'url' => 'https://www.reddit.com/r/financialindependence/']
        ],
        'Others' => [
            ['name' => 'Product Hunt', 'type' => 'Discovery', 'url' => 'https://www.producthunt.com/'],
            ['name' => 'Indie Hackers', 'type' => 'Community', 'url' => 'https://www.indiehackers.com/'],
            ['name' => 'r/startups', 'type' => 'Reddit', 'url' => 'https://www.reddit.com/r/startups/']
        ]
    ];
    return $resources[$category] ?? $resources['Others'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Ideas - Innovation Hub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f5f5f5; }
        main { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .ideas-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .idea-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
        .idea-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .idea-title { font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .idea-description { color: #666; margin-bottom: 15px; }
        .idea-footer { display: flex; justify-content: space-between; align-items: center; }
        .idea-category { background: #007bff; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .idea-rating { color: #ffc107; font-weight: bold; }
        .search-box { width: 100%; max-width: 400px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background: white; max-width: 600px; margin: 50px auto; padding: 20px; border-radius: 8px; }
        .close-btn { float: right; font-size: 24px; cursor: pointer; }
        .resources-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
        .resource-item { background: #f8f9fa; padding: 10px; border-radius: 4px; text-align: center; }
        .resource-item:hover { background: #e9ecef; }
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
        <input type="text" id="search" class="search-box" placeholder="Search ideas..." onkeyup="searchIdeas()">
        
        <div class="ideas-container" id="ideasContainer">
            <?php
            $sql = "SELECT ideas.id, title, description, category, submitter, submitted_at,
                   (SELECT AVG(rating) FROM ratings WHERE idea_id = ideas.id) as avg_rating,
                   (SELECT COUNT(*) FROM ratings WHERE idea_id = ideas.id) as rating_count
                   FROM ideas ORDER BY submitted_at DESC";

            $result = $conn->query($sql);
            $ideas = [];
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $idea = [
                        'id' => intval($row['id']),
                        'title' => htmlspecialchars($row['title']),
                        'description' => htmlspecialchars($row['description']),
                        'category' => htmlspecialchars($row['category']),
                        'submitter' => htmlspecialchars($row['submitter']),
                        'submitted_at' => $row['submitted_at'],
                        'avg_rating' => $row['avg_rating'] ? round($row['avg_rating'], 1) : 0,
                        'rating_count' => intval($row['rating_count'])
                    ];
                    $ideas[] = $idea;
                    
                    $stars = '';
                    if ($idea['avg_rating'] > 0) {
                        $full = floor($idea['avg_rating']);
                        $stars = str_repeat('★', $full) . str_repeat('☆', 5 - $full);
                    } else {
                        $stars = 'No ratings';
                    }
                    
                    echo '<div class="idea-card" onclick="showDetails('.$idea['id'].')">';
                    echo '<div class="idea-title">'.$idea['title'].'</div>';
                    echo '<div class="idea-description">'.$idea['description'].'</div>';
                    echo '<div class="idea-footer">';
                    echo '<span class="idea-category">'.$idea['category'].'</span>';
                    echo '<span class="idea-rating">'.$stars.' '.$idea['avg_rating'].'/5</span>';
                    echo '</div>';
                    echo '<div style="margin-top:10px; color:#666; font-size:13px;">By: '.$idea['submitter'].'</div>';
                    echo '</div>';
                }
                
                $ratingsSql = "SELECT idea_id, rater_name, rating, comment, rated_at FROM ratings ORDER BY rated_at DESC";
                $ratingsResult = $conn->query($ratingsSql);
                $allRatings = [];
                if ($ratingsResult) {
                    while($r = $ratingsResult->fetch_assoc()) {
                        if (!isset($allRatings[$r['idea_id']])) {
                            $allRatings[$r['idea_id']] = [];
                        }
                        $allRatings[$r['idea_id']][] = $r;
                    }
                }
            } else {
                echo '<div style="grid-column:1/-1; text-align:center; padding:40px;">No ideas found.</div>';
            }
            
            $conn->close();
            ?>
        </div>
    </main>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <footer>
        <p>
            ✨ Instagram: <a href="https://www.instagram.com/rxthee7" target="_blank">rxthee7</a>
            | 👻 Snapchat: <a href="https://www.snapchat.com/add/rxthee7" target="_blank">rxthee7</a>
            | 📧 Email: <a href="mailto:aryanpc1195@gmail.com">aryanpc1195@gmail.com</a>
            | 📞 <a href="tel:+917814232567">+91 7814232567</a>
        </p>
    </footer>

    <script>
        const ideas = <?php echo json_encode($ideas ?? []); ?>;
        const ratings = <?php echo json_encode($allRatings ?? []); ?>;
        const resources = <?php echo json_encode([
            'Technology' => getResources('Technology'),
            'Healthcare' => getResources('Healthcare'),
            'Education' => getResources('Education'),
            'Finance' => getResources('Finance'),
            'Others' => getResources('Others')
        ]); ?>;
        
        function showDetails(id) {
            const idea = ideas.find(i => i.id === id);
            if (!idea) return;
            
            const ideaRatings = ratings[id] || [];
            const ideaResources = resources[idea.category] || resources['Others'];
            
            let html = '<h2>' + idea.title + '</h2>';
            html += '<p>' + idea.description.replace(/\n/g, '<br>') + '</p>';
            
            html += '<h3>Resources</h3>';
            html += '<div class="resources-list">';
            ideaResources.forEach(res => {
                const url = res.url || '#';
                html += '<a class="resource-item" href="' + url + '" target="_blank" style="text-decoration:none; color:inherit;">';
                html += '<div>' + res.name + '</div>';
                html += '<small>' + res.type + '</small>';
                html += '</a>';
            });
            html += '</div>';
            
            html += '<h3>Ratings (' + idea.rating_count + ')</h3>';
            if (ideaRatings.length > 0) {
                ideaRatings.forEach(r => {
                    html += '<div style="background:#f8f9fa; padding:10px; margin:5px 0; border-radius:4px;">';
                    html += '<strong>' + r.rater_name + '</strong> - ' + '★'.repeat(r.rating) + '☆'.repeat(5-r.rating);
                    if (r.comment) html += '<p>' + r.comment + '</p>';
                    html += '</div>';
                });
            } else {
                html += '<p>No ratings yet.</p>';
            }
            
            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('detailModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }
        
        function searchIdeas() {
            const search = document.getElementById('search').value.toLowerCase();
            const cards = document.querySelectorAll('.idea-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(search) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
