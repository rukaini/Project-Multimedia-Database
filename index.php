<?php
include 'config/db.php';

// Initialize search and filter parameter states
$tbr_keyword    = isset($_GET['tbr_keyword']) ? trim($_GET['tbr_keyword']) : '';
$abr_max_size   = isset($_GET['abr_max_size']) ? floatval($_GET['abr_max_size']) : 1000.00;
$abr_resolution = isset($_GET['abr_resolution']) ? $_GET['abr_resolution'] : 'Any';
$abr_gender     = isset($_GET['abr_gender']) ? $_GET['abr_gender'] : 'Any';
$cbr_color      = isset($_GET['cbr_color']) ? $_GET['cbr_color'] : 'All';

// Constructing the dynamic SQL Search Matrix base query
$query = "SELECT v.*, t.Thumbnail_Path, t.Dominant_Colour, s.Name, s.Gender 
          FROM VIDEO v 
          JOIN THUMBNAIL t ON v.VideoID = t.VideoID 
          JOIN STUDENT s ON v.StudentID = s.StudentID 
          WHERE v.File_Size_MB <= ?";

$params = [$abr_max_size];
$types = "d";

// Apply Text-Based Retrieval (TBR) Filters
if (!empty($tbr_keyword)) {
    $query .= " AND (v.Title LIKE ? OR v.Description LIKE ? OR v.StudentID LIKE ? OR s.Name LIKE ?)";
    $likeKeyword = "%" . $tbr_keyword . "%";
    array_push($params, $likeKeyword, $likeKeyword, $likeKeyword, $likeKeyword);
    $types .= "ssss";
}

// Apply Attribute-Based Retrieval (ABR) Filters
if ($abr_resolution !== 'Any') {
    $query .= " AND v.Resolution = ?";
    array_push($params, $abr_resolution);
    $types .= "s";
}
if ($abr_gender !== 'Any') {
    $query .= " AND s.Gender = ?";
    array_push($params, $abr_gender);
    $types .= "s";
}

// Apply Content-Based Retrieval (CBR) Filters
if ($cbr_color !== 'All') {
    $query .= " AND t.Dominant_Colour = ?";
    array_push($params, $cbr_color);
    $types .= "s";
}

$query .= " ORDER BY v.VideoID DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidFolio - Multimedia Search and Retrieval System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <style>
        body { background-color: #0c0e17; color: #f4f6fa; }
        .sidebar { background-color: #111422; border-right: 1px solid #1e2238; min-height: 100vh; }
        .card-video { background-color: #161a2e; border: 1px solid #232946; border-radius: 8px; transition: transform 0.2s ease, box-shadow 0.2s ease; overflow: hidden; }
        .card-video:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.4); border-color: #3b4475; }
        .thumb-container { height: 170px; background-color: #090a10; position: relative; display: flex; align-items: center; justify-content: center; }
        .color-bubble { width: 24px; height: 24px; border-radius: 50%; display: inline-block; cursor: pointer; border: 2px solid transparent; transition: scale 0.2s; }
        .color-bubble:hover { transform: scale(1.15); }
        .color-bubble.active { border-color: #ffffff; box-shadow: 0 0 8px rgba(255,255,255,0.5); }
        .search-bar { background-color: #181d36 !important; border: 1px solid #283057 !important; color: #ffffff !important; }
        .search-bar::placeholder { color: #56649c; }
        .modal-content { background-color: #141829; border: 1px solid #2c3459; color: #ffffff; }
    </style>
</head>
<body>

<div class="page">
    <div class="page-wrapper">
        <div class="row g-0">
            
            <div class="col-md-3 sidebar p-4">
                <div class="d-flex align-items-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-primary me-2" width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" /><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /></svg>
                    <h2 class="m-0 font-weight-bold tracking-tight text-white">VidFolio</h2>
                </div>
                <div class="text-muted small text-uppercase font-monospace mb-4">System Context v1.3</div>

                <form id="filterForm" method="GET" action="index.php">
                    <input type="hidden" name="tbr_keyword" value="<?php echo htmlspecialchars($tbr_keyword); ?>">
                    <input type="hidden" id="cbr_color_input" name="cbr_color" value="<?php echo htmlspecialchars($cbr_color); ?>">

                    <div class="mb-4">
                        <label class="form-label text-white-50 d-flex justify-content-between">
                            <span>Attribute-Based (ABR)</span>
                        </label>
                        <div class="text-white small mb-1 fw-bold">Max File Size: <span id="sizeVal"><?php echo $abr_max_size; ?></span> MB</div>
                        <input type="range" class="form-range" min="1" max="1000" step="10" id="sizeRange" name="abr_max_size" value="<?php echo $abr_max_size; ?>" oninput="updateSizeLabel(this.value)" onchange="this.form.submit()">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white-50 small text-uppercase font-monospace">Resolution</label>
                        <select name="abr_resolution" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                            <option value="Any" <?php if($abr_resolution == 'Any') echo 'selected'; ?>>Any</option>
                            <option value="1080p" <?php if($abr_resolution == '1080p') echo 'selected'; ?>>1080p (Full HD)</option>
                            <option value="720p" <?php if($abr_resolution == '720p') echo 'selected'; ?>>720p (HD)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white-50 small text-uppercase font-monospace">Student Gender</label>
                        <select name="abr_gender" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                            <option value="Any" <?php if($abr_gender == 'Any') echo 'selected'; ?>>Any</option>
                            <option value="Male" <?php if($abr_gender == 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if($abr_gender == 'Female') echo 'selected'; ?>>Female</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white-50 small text-uppercase font-monospace">Content-Based (CBR)</label>
                        <div class="d-flex gap-2 align-items-center mt-2">
                            <span class="color-bubble d-flex align-items-center justify-content-center border border-secondary text-white font-monospace small <?php if($cbr_color == 'All') echo 'active'; ?>" style="background-color: #202438; font-size: 9px;" onclick="selectColor('All')">All</span>
                            <span class="color-bubble bg-blue <?php if($cbr_color == 'Blue') echo 'active'; ?>" onclick="selectColor('Blue')"></span>
                            <span class="color-bubble bg-yellow <?php if($cbr_color == 'Yellow') echo 'active'; ?>" onclick="selectColor('Yellow')"></span>
                            <span class="color-bubble bg-green <?php if($cbr_color == 'Green') echo 'active'; ?>" onclick="selectColor('Green')"></span>
                        </div>
                    </div>

                    <div class="d-grid mt-5">
                        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary font-weight-bold">Reset Filter Stack</a>
                    </div>
                </form>
            </div>

            <div class="col-md-9 p-5">
                
                <div class="row align-items-center mb-5">
                    <div class="col">
                        <form method="GET" action="index.php">
                            <input type="hidden" name="abr_max_size" value="<?php echo $abr_max_size; ?>">
                            <input type="hidden" name="abr_resolution" value="<?php echo $abr_resolution; ?>">
                            <input type="hidden" name="abr_gender" value="<?php echo $abr_gender; ?>">
                            <input type="hidden" name="cbr_color" value="<?php echo $cbr_color; ?>">
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="text-muted" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                </span>
                                <input type="text" name="tbr_keyword" class="form-control form-control-lg search-bar font-weight-medium py-3" placeholder="Search by title, description, keywords, or matric number..." value="<?php echo htmlspecialchars($tbr_keyword); ?>">
                            </div>
                        </form>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-lg fw-bold px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            Upload Video
                        </button>
                    </div>
                </div>

                <h3 class="mb-2 text-white font-weight-bold">Video Library</h3>
                <p class="text-muted mb-4">Browse student portfolio videos inside the repository.</p>

                <div class="row row-cards">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-sm-6 col-lg-4">
                                <div class="card card-video h-100" style="cursor: pointer;" onclick="playVideo('<?php echo htmlspecialchars($row['Video_Path']); ?>', '<?php echo htmlspecialchars(addslashes($row['Title'])); ?>')">
                                    
                                    <div class="thumb-container" style="border-bottom: 1px solid #23293d; background-color: #090a10; position: relative; overflow: hidden; height: 170px; pointer-events: none;">
                                        
                                        <video class="preview-video-source" 
                                               src="<?php echo htmlspecialchars($row['Video_Path']); ?>" 
                                               preload="metadata" 
                                               muted 
                                               data-duration="<?php echo intval($row['Duration_Seconds']); ?>"
                                               style="display: none;"></video>
                                        
                                        <div class="video-thumbnail-canvas-target w-100 h-100" style="background-position: center; background-size: cover;">
                                            <div class="d-flex align-items-center justify-content-center h-100 opacity-25">
                                                <div class="spinner-border text-light spinner-border-sm" role="status"></div>
                                            </div>
                                        </div>
                                        
                                        <span class="badge bg-dark bg-opacity-70 text-white border border-secondary position-absolute top-0 start-0 m-3"><?php echo $row['Resolution']; ?></span>
                                        <span class="badge bg-black text-white position-absolute bottom-0 end-0 m-3 font-monospace" style="font-size:11px; font-weight: bold; padding: 2px 5px; border-radius: 4px;"><?php echo gmdate("i:s", $row['Duration_Seconds']); ?></span>
                                    </div>

                                    <div class="p-3">
                                        <h4 class="text-white text-truncate mb-1 font-weight-bold" title="<?php echo htmlspecialchars($row['Title']); ?>">
                                            <?php echo htmlspecialchars($row['Title']); ?>
                                        </h4>
                                        <p class="text-muted small text-truncate mb-3"><?php echo htmlspecialchars($row['Description']); ?></p>
                                        
                                        <div class="text-white-50 small mb-1" style="font-size: 11px;">Author: <span class="text-white"><?php echo htmlspecialchars($row['Name']); ?></span></div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-muted font-monospace" style="font-size:11px;">Matric ID: <span class="badge bg-secondary bg-opacity-25 text-white font-weight-normal font-monospace px-2"><?php echo htmlspecialchars($row['StudentID']); ?></span></div>
                                            <span class="badge bg-dark border border-secondary text-muted px-2" style="font-size: 9px; font-weight: normal; letter-spacing: 0.3px;">● CBR: <?php echo $row['Dominant_Colour']; ?></span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <div class="empty bg-dark bg-opacity-20 p-5 rounded border border-secondary border-dashed">
                                <p class="empty-title text-white mb-2">No matching tracks found</p>
                                <p class="empty-subtitle text-muted">Try expanding your search query or color stack filters.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-bottom border-secondary bg-dark rounded-top">
                <h5 class="modal-title text-white fw-bold">🚀 Ingestion Pipeline Gateway</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-dark text-white rounded-bottom">
                <form action="process_video.php" method="POST" enctype="multipart/form-data" class="p-1">
                    
                    <div class="mb-3">
                        <label class="form-label text-white-50 fw-bold small text-uppercase tracking-wider">Video Title / Topic Name</label>
                        <input type="text" name="video_title" class="form-control bg-dark text-white border-secondary" placeholder="e.g., Database Normalization 2NF Lab Session" required style="box-shadow: none;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white-50 fw-bold small text-uppercase tracking-wider">Target Matric Number / ID</label>
                        <input type="text" name="student_id" class="form-control bg-dark text-white border-secondary" placeholder="e.g., B032410200" required style="box-shadow: none;">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white-50 fw-bold small text-uppercase tracking-wider">Select Video Asset File (MP4, MOV)</label>
                        <input type="file" name="video_file" class="form-control bg-dark text-white border-secondary" accept="video/mp4,video/quicktime" required style="box-shadow: none;">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold tracking-wide py-2 text-uppercase">
                            Process Asset Intake
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="playerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background-color: #0b0c14; border-color: #252a45;">
            <div class="modal-header border-bottom border-dark py-2">
                <h5 class="modal-title text-white fw-bold" id="playerVideoTitle">🎬 Playing Asset Stream</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopVideoPlayer()"></button>
            </div>
            <div class="modal-body p-0 bg-black">
                <div class="ratio ratio-16x9">
                    <video id="portfolioVideoPlayer" controls class="w-100 h-100" style="outline: none; background-color: #000000;">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
<script>
function updateSizeLabel(val) {
    document.getElementById('sizeVal').textContent = val;
}

function selectColor(color) {
    document.getElementById('cbr_color_input').value = color;
    document.getElementById('filterForm').submit();
}

function playVideo(videoPath, videoTitle) {
    const videoPlayer = document.getElementById('portfolioVideoPlayer');
    document.getElementById('playerVideoTitle').textContent = "🎬 Playing: " + videoTitle;
    
    videoPlayer.src = videoPath;
    videoPlayer.load();
    
    const playbackModal = new bootstrap.Modal(document.getElementById('playerModal'));
    playbackModal.show();
    
    videoPlayer.play().catch(function(err) {
        console.log("Browser autofocus constraints intercepted auto-execution:", err);
    });
}

function stopVideoPlayer() {
    const videoPlayer = document.getElementById('portfolioVideoPlayer');
    videoPlayer.pause();
    videoPlayer.src = ""; // Flush memory buffer allocations immediately on close window actions
}

document.getElementById('playerModal').addEventListener('hidden.bs.modal', function () {
    stopVideoPlayer();
});

// Advanced Video Contrast Analyzer Framework - Stable Lifecyle Grade
document.addEventListener("DOMContentLoaded", function() {
    const videos = document.querySelectorAll(".preview-video-source");

    videos.forEach(video => {
        // THE FIX: Wrap the execution pipeline inside a loadedmetadata listener 
        // This forces the browser to wait until the fresh video file size and boundaries are fully active.
        video.addEventListener("loadedmetadata", function initializeAnalysis() {
            video.removeEventListener("loadedmetadata", initializeAnalysis); // Clean up event execution

            const totalDuration = parseInt(video.getAttribute("data-duration")) || video.duration || 10;
            
            // Define multi-point timeline sampling targets (skipping intro/outro segments)
            const samplePoints = [
                totalDuration * 0.2,
                totalDuration * 0.4,
                totalDuration * 0.6,
                totalDuration * 0.8
            ];
            
            let currentSampleIdx = 0;
            let highestContrastValue = -1;
            let bestFrameDataURL = "";

            const canvas = document.createElement("canvas");
            canvas.width = 320;
            canvas.height = 180;
            const ctx = canvas.getContext("2d", { willReadFrequently: true });

            // Trigger the analysis loop sequence
            video.currentTime = samplePoints[currentSampleIdx];

            video.addEventListener("seeked", function analyzeFrame() {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                try {
                    const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const pixels = imgData.data;
                    
                    let totalLuminance = 0;
                    let sampleSize = 0;
                    
                    for (let i = 0; i < pixels.length; i += 16) {
                        const r = pixels[i];
                        const g = pixels[i+1];
                        const b = pixels[i+2];
                        
                        // ITU BT.709 Perceptual Brightness formula structure
                        const luminance = (0.2126 * r) + (0.7152 * g) + (0.0722 * b);
                        totalLuminance += luminance;
                        sampleSize++;
                    }
                    
                    const meanLuminance = totalLuminance / sampleSize;
                    
                    let varianceSum = 0;
                    for (let i = 0; i < pixels.length; i += 16) {
                        const r = pixels[i];
                        const g = pixels[i+1];
                        const b = pixels[i+2];
                        const luminance = (0.2126 * r) + (0.7152 * g) + (0.0722 * b);
                        varianceSum += Math.pow(luminance - meanLuminance, 2);
                    }
                    
                    const rmsContrast = Math.sqrt(varianceSum / sampleSize);

                    if (rmsContrast > highestContrastValue) {
                        highestContrastValue = rmsContrast;
                        bestFrameDataURL = canvas.toDataURL("image/jpeg", 0.85);
                    }
                } catch (e) {
                    console.warn("Cross-Origin security policies limited raw context inspection loops:", e);
                }

                currentSampleIdx++;
                if (currentSampleIdx < samplePoints.length) {
                    video.currentTime = samplePoints[currentSampleIdx];
                } else {
                    video.removeEventListener("seeked", analyzeFrame);
                    const targetDisplayContainer = video.nextElementSibling;
                    if (bestFrameDataURL) {
                        targetDisplayContainer.innerHTML = ""; 
                        targetDisplayContainer.style.backgroundImage = `url(${bestFrameDataURL})`;
                    }
                }
            });
        });

        // Safety trigger fallback: If the browser already had the item cached, kickstart the event manually
        if (video.readyState >= 1) {
            video.dispatchEvent(new Event('loadedmetadata'));
        }
    });
});
</script>
</body>
</html>