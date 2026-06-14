<?php
include 'config/db.php';

// Initialize default search variables
$tbr_keyword = isset($_GET['tbr_keyword']) ? trim($_GET['tbr_keyword']) : '';
$abr_resolution = isset($_GET['abr_resolution']) ? $_GET['abr_resolution'] : 'Any';
$abr_gender = isset($_GET['abr_gender']) ? $_GET['abr_gender'] : 'Any';
$abr_max_size = isset($_GET['abr_max_size']) ? floatval($_GET['abr_max_size']) : 1000.00; 
$cbr_color = isset($_GET['cbr_color']) ? $_GET['cbr_color'] : 'All';

// Dynamic SQL Query Builder Engine
$query = "SELECT V.*, S.Name, S.Gender, T.Thumbnail_Path, T.Dominant_Colour 
          FROM VIDEO V
          JOIN STUDENT S ON V.StudentID = S.StudentID
          LEFT JOIN THUMBNAIL T ON V.VideoID = T.VideoID
          WHERE 1=1";

$params = [];
$types = "";

if (!empty($tbr_keyword)) {
    $query .= " AND (V.Title LIKE ? OR V.Description LIKE ? OR S.Name LIKE ? OR V.StudentID = ?)";
    $search_like = "%" . $tbr_keyword . "%";
    array_push($params, $search_like, $search_like, $search_like, $tbr_keyword);
    $types .= "ssss";
}
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidFolio - Student Video Portfolio System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #0c0d12; color: #f8f9fa; }
        .bg-sidebar { background-color: #131520; border-right: 1px solid #222538; }
        .bg-card { background-color: #131520; border: 1px solid #222538; transition: transform 0.2s; }
        .bg-card:hover { transform: translateY(-3px); }
        .search-input { background-color: #1b1e2e !important; border: 1px solid #2c314d !important; color: #fff !important; }
        .search-input::placeholder { color: #6c757d; }
        .color-bubble { display: inline-block; width: 28px; height: 28px; border-radius: 50%; margin-right: 8px; cursor: pointer; border: 2px solid transparent; position: relative; }
        .color-bubble.active { border-color: #ffffff; transform: scale(1.1); }
        .thumb-preview { height: 160px; display: flex; align-items: center; justify-content: center; font-weight: bold; position: relative; overflow: hidden; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row min-vh-100">
        
        <nav class="col-md-3 col-lg-2 p-4 bg-sidebar d-flex flex-column gap-4">
            <div>
                <h4 class="text-primary fw-bold d-flex align-items-center gap-2 mb-0">
                    <span class="fs-3">📘</span> VidFolio
                </h4>
                <small class="text-muted">Student Portfolio Dashboard</small>
            </div>

            <form method="GET" action="search.php" id="filterForm">
                <input type="hidden" name="tbr_keyword" value="<?php echo htmlspecialchars($tbr_keyword, ENT_QUOTES); ?>">

                <div class="mb-4">
                    <h6 class="text-uppercase tracking-wider text-muted small fw-bold mb-3">Attribute Filters (ABR)</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Max File Size: <span class="text-white fw-bold"><?php echo $abr_max_size; ?> MB</span></label>
                        <input type="range" name="abr_max_size" class="form-range" min="50" max="1000" step="50" value="<?php echo $abr_max_size; ?>" onchange="this.form.submit()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-secondary">Resolution</label>
                        <select name="abr_resolution" class="form-select search-input text-white" onchange="this.form.submit()">
                            <option value="Any" <?php if($abr_resolution == 'Any') echo 'selected'; ?>>Any Resolution</option>
                            <option value="720p" <?php if($abr_resolution == '720p') echo 'selected'; ?>>720p</option>
                            <option value="1080p" <?php if($abr_resolution == '1080p') echo 'selected'; ?>>1080p</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-secondary">Uploader Gender</label>
                        <select name="abr_gender" class="form-select search-input text-white" onchange="this.form.submit()">
                            <option value="Any" <?php if($abr_gender == 'Any') echo 'selected'; ?>>Any Gender</option>
                            <option value="Male" <?php if($abr_gender == 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if($abr_gender == 'Female') echo 'selected'; ?>>Female</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase tracking-wider text-muted small fw-bold mb-3">Simulated Color Match (CBR)</h6>
                    <input type="hidden" name="cbr_color" id="cbr_color_input" value="<?php echo $cbr_color; ?>">
                    
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        <span class="color-bubble bg-secondary d-flex align-items-center justify-content-center text-white small <?php if($cbr_color == 'All') echo 'active'; ?>" style="font-size:10px;" onclick="selectColor('All')">All</span>
                        <span class="color-bubble <?php if($cbr_color == 'Blue') echo 'active'; ?>" style="background-color: #2563eb;" onclick="selectColor('Blue')"></span>
                        <span class="color-bubble <?php if($cbr_color == 'Yellow') echo 'active'; ?>" style="background-color: #eab308;" onclick="selectColor('Yellow')"></span>
                        <span class="color-bubble <?php if($cbr_color == 'Green') echo 'active'; ?>" style="background-color: #16a34a;" onclick="selectColor('Green')"></span>
                    </div>
                </div>

                <a href="search.php" class="btn btn-sm btn-outline-danger w-100 mt-2">Reset All Filters</a>
            </form>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-5 py-4">
            
            <form method="GET" action="search.php" class="mb-4">
                <input type="hidden" name="abr_resolution" value="<?php echo $abr_resolution; ?>">
                <input type="hidden" name="abr_gender" value="<?php echo $abr_gender; ?>">
                <input type="hidden" name="abr_max_size" value="<?php echo $abr_max_size; ?>">
                <input type="hidden" name="cbr_color" value="<?php echo $cbr_color; ?>">

                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text search-input border-end-0 text-muted">🔍</span>
                    <input type="text" name="tbr_keyword" class="form-control search-input border-start-0" placeholder="Search portfolio library items by title, description, or student matric number..." value="<?php echo htmlspecialchars($tbr_keyword, ENT_QUOTES); ?>">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Search</button>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-secondary">Video Library Displays (<?php echo $result->num_rows; ?> results found)</h5>
                <button class="btn btn-sm btn-success fw-bold px-3 shadow" data-bs-toggle="modal" data-bs-target="#uploadModal">+ Upload Video</button>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card h-100 bg-card text-white shadow-sm overflow-hidden">
                                <div class="thumb-preview" style="background-color: <?php echo ($row['Dominant_Colour']=='Blue')?'#1e3a8a':(($row['Dominant_Colour']=='Yellow')?'#854d0e':'#14532d'); ?>;">
                                    <span class="badge bg-dark bg-opacity-70 position-absolute top-0 start-0 m-2 border border-secondary"><?php echo $row['Resolution']; ?></span>
                                    <span class="badge bg-black position-absolute bottom-0 end-0 m-2 font-monospace" style="font-size:11px;"><?php echo gmdate("i:s", $row['Duration_Seconds']); ?></span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="card-title fw-bold text-white mb-1 text-truncate"><?php echo htmlspecialchars($row['Title']); ?></h6>
                                        <p class="text-muted small text-truncate mb-2"><?php echo htmlspecialchars($row['Description']); ?></p>
                                    </div>
                                    <div class="border-top border-dark pt-2 mt-2" style="font-size: 11px; color:#a8a8b3;">
                                        <div><strong>By:</strong> <?php echo htmlspecialchars($row['Name']); ?></div>
                                        <div><strong>ID:</strong> <code><?php echo $row['StudentID']; ?></code></div>
                                        <div class="mt-2"><span class="badge bg-dark text-secondary p-1">● Color Match: <?php echo $row['Dominant_Colour']; ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 w-100 text-center py-5 rounded bg-sidebar border border-secondary mt-3">
                        <p class="text-muted mb-0">No student videos matched your combined search filters stack.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-dark">
        <div class="modal-content bg-sidebar border border-secondary text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title fw-bold">📤 Upload Portfolio Video</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-submit="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="process_video.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Matric Number / ID</label>
                        <input type="text" name="student_id" class="form-control search-input text-white" placeholder="e.g. B032410200" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Video Title</label>
                        <input type="text" name="raw_title" class="form-control search-input text-white" placeholder="Enter presentation title..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary">Choose Portfolio File (MP4, MOV, WEBM)</label>
                        <input type="file" name="video_file" class="form-control search-input text-white" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-bold mt-2">Submit and Process System Files</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectColor(color) {
    document.getElementById('cbr_color_input').value = color;
    document.getElementById('filterForm').submit();
}
</script>
</body>
</html>