<?php
include 'db.php';

// Initialize default search variables
$tbr_keyword = isset($_GET['tbr_keyword']) ? trim($_GET['tbr_keyword']) : '';
$abr_resolution = isset($_GET['abr_resolution']) ? $_GET['abr_resolution'] : 'Any';
$abr_gender = isset($_GET['abr_gender']) ? $_GET['abr_gender'] : 'Any';
$abr_max_size = isset($_GET['abr_max_size']) ? floatval($_GET['abr_max_size']) : 1000.00; 
$cbr_color = isset($_GET['cbr_color']) ? $_GET['cbr_color'] : 'All';

// Dynamic SQL Query Builder (TBR, ABR, and CBR Integrated)
$query = "SELECT V.*, S.Name, S.Gender, T.Thumbnail_Path, T.Dominant_Colour 
          FROM VIDEO V
          JOIN STUDENT S ON V.StudentID = S.StudentID
          LEFT JOIN THUMBNAIL T ON V.VideoID = T.VideoID
          WHERE 1=1";

$params = [];
$types = "";

// 1. Text-Based Retrieval (TBR)
if (!empty($tbr_keyword)) {
    $query .= " AND (V.Title LIKE ? OR V.Description LIKE ? OR S.Name LIKE ? OR V.StudentID = ?)";
    $search_like = "%" . $tbr_keyword . "%";
    array_push($params, $search_like, $search_like, $search_like, $tbr_keyword);
    $types .= "ssss";
}

// 2. Attribute-Based Retrieval (ABR)
if ($abr_resolution !== 'Any') {
    $query .= " AND V.Resolution = ?";
    array_push($params, $abr_resolution);
    $types .= "s";
}
if ($abr_gender !== 'Any') {
    $query .= " AND S.Gender = ?";
    array_push($params, $abr_gender);
    $types .= "s";
}
if ($abr_max_size > 0) {
    $query .= " AND V.File_Size_MB <= ?";
    array_push($params, $abr_max_size);
    $types .= "d";
}

// 3. Simulated Content-Based Retrieval (CBR)
if ($cbr_color !== 'All') {
    $query .= " AND T.Dominant_Colour = ?";
    array_push($params, $cbr_color);
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VidFolio - Search Engine Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #121214; color: #e1e1e6; margin: 0; padding: 20px; }
        .dashboard { display: flex; gap: 20px; margin-top: 20px; }
        .sidebar { width: 280px; background: #1a1a1e; padding: 20px; border-radius: 8px; border: 1px solid #29292e; }
        .main-content { flex-grow: 1; }
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input[type="text"] { flex-grow: 1; padding: 12px; border-radius: 6px; border: 1px solid #29292e; background: #202024; color: #fff; }
        .search-box button, .reset-btn { padding: 12px 20px; background: #00875f; border: none; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .reset-btn { background: #8257e5; display: block; text-decoration: none; text-align: center; margin-top: 15px; }
        .filter-group { margin-bottom: 20px; }
        .filter-group label { display: block; margin-bottom: 8px; font-size: 14px; color: #a8a8b3; }
        .filter-group select, .filter-group input[type="range"] { width: 100%; padding: 8px; border-radius: 4px; background: #202024; color: #fff; border: 1px solid #29292e; }
        .color-bubble { display: inline-block; width: 24px; height: 24px; border-radius: 50%; margin-right: 8px; cursor: pointer; border: 2px solid transparent; text-align: center; font-size: 10px; line-height: 24px; }
        .color-bubble.active { border-color: #fff; }
        .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .video-card { background: #1a1a1e; border-radius: 8px; overflow: hidden; border: 1px solid #29292e; }
        .thumbnail-placeholder { height: 150px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .video-info { padding: 15px; }
        .video-title { margin: 0 0 8px 0; font-size: 16px; }
        .video-meta { font-size: 12px; color: #a8a8b3; line-height: 1.4; }
    </style>
</head>
<body>

    <h1>🎬 VidFolio Search Engine</h1>

    <form method="GET" action="search.php">
        <div class="search-box">
            <input type="text" name="tbr_keyword" placeholder="🔍 Search by video title, description, or student details..." value="<?php echo htmlspecialchars($tbr_keyword, ENT_QUOTES); ?>">
            <button type="submit">Search</button>
        </div>

        <div class="dashboard">
            <div class="sidebar">
                <h3>Attribute Filters (ABR)</h3>
                <div class="filter-group">
                    <label>Resolution</label>
                    <select name="abr_resolution" onchange="this.form.submit()">
                        <option value="Any" <?php if($abr_resolution == 'Any') echo 'selected'; ?>>Any</option>
                        <option value="720p" <?php if($abr_resolution == '720p') echo 'selected'; ?>>720p</option>
                        <option value="1080p" <?php if($abr_resolution == '1080p') echo 'selected'; ?>>1080p</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Gender</label>
                    <select name="abr_gender" onchange="this.form.submit()">
                        <option value="Any" <?php if($abr_gender == 'Any') echo 'selected'; ?>>Any</option>
                        <option value="Male" <?php if($abr_gender == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($abr_gender == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Max Size: <?php echo $abr_max_size; ?> MB</label>
                    <input type="range" name="abr_max_size" min="50" max="1000" step="50" value="<?php echo $abr_max_size; ?>" onchange="this.form.submit()">
                </div>

                <h3>Simulated Color (CBR)</h3>
                <input type="hidden" name="cbr_color" id="cbr_color_input" value="<?php echo $cbr_color; ?>">
                <span class="color-bubble <?php if($cbr_color == 'All') echo 'active'; ?>" style="background: #444;" onclick="selectColor('All')">All</span>
                <span class="color-bubble <?php if($cbr_color == 'Blue') echo 'active'; ?>" style="background: #2563eb;" onclick="selectColor('Blue')"></span>
                <span class="color-bubble <?php if($cbr_color == 'Yellow') echo 'active'; ?>" style="background: #eab308;" onclick="selectColor('Yellow')"></span>
                <span class="color-bubble <?php if($cbr_color == 'Green') echo 'active'; ?>" style="background: #16a34a;" onclick="selectColor('Green')"></span>
                
                <a href="search.php" class="reset-btn">Reset Filters</a>
            </div>

            <div class="main-content">
                <h3>Results (<?php echo $result->num_rows; ?>)</h3>
                <div class="video-grid">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="video-card">
                                <div class="thumbnail-placeholder" style="background: <?php echo ($row['Dominant_Colour']=='Blue')?'#1e3a8a':(($row['Dominant_Colour']=='Yellow')?'#854d0e':'#14532d'); ?>;">
                                    <span><?php echo $row['Resolution']; ?></span>
                                </div>
                                <div class="video-info">
                                    <h4 class="video-title"><?php echo htmlspecialchars($row['Title'], ENT_QUOTES); ?></h4>
                                    <div class="video-meta">
                                        <strong>By:</strong> <?php echo htmlspecialchars($row['Name'], ENT_QUOTES); ?><br>
                                        <strong>Size:</strong> <?php echo $row['File_Size_MB']; ?> MB<br>
                                        <strong>Dominant Color:</strong> <?php echo $row['Dominant_Colour']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No results match your criteria.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>

    <script>
    function selectColor(color) {
        document.getElementById('cbr_color_input').value = color;
        document.forms[0].submit();
    }
    </script>
</body>
</html>