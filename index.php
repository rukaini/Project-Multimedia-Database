<?php
include 'config/db.php';
include 'includes/header.php';

// Capture system retrieval request fields safely
$tbr_keyword = isset($_GET['tbr_keyword']) ? trim($_GET['tbr_keyword']) : '';
$abr_resolution = isset($_GET['abr_resolution']) ? $_GET['abr_resolution'] : 'Any';
$abr_gender = isset($_GET['abr_gender']) ? $_GET['abr_gender'] : 'Any';
$abr_max_size = isset($_GET['abr_max_size']) ? floatval($_GET['abr_max_size']) : 1000.00; 
$cbr_color = isset($_GET['cbr_color']) ? $_GET['cbr_color'] : 'All';

// SQL Architecture Builder Layer
$query = "SELECT V.*, S.Name, S.Gender, T.Thumbnail_Path, T.Dominant_Colour 
          FROM VIDEO V
          JOIN STUDENT S ON V.StudentID = S.StudentID
          LEFT JOIN THUMBNAIL T ON V.VideoID = T.VideoID
          WHERE 1=1";

$params = []; $types = "";

if (!empty($tbr_keyword)) {
    $query .= " AND (V.Title LIKE ? OR V.Description LIKE ? OR S.Name LIKE ? OR V.StudentID = ?)";
    $like = "%" . $tbr_keyword . "%";
    array_push($params, $like, $like, $like, $tbr_keyword); $types .= "ssss";
}
if ($abr_resolution !== 'Any') { $query .= " AND V.Resolution = ?"; array_push($params, $abr_resolution); $types .= "s"; }
if ($abr_gender !== 'Any') { $query .= " AND S.Gender = ?"; array_push($params, $abr_gender); $types .= "s"; }
if ($abr_max_size > 0) { $query .= " AND V.File_Size_MB <= ?"; array_push($params, $abr_max_size); $types .= "d"; }
if ($cbr_color !== 'All') { $query .= " AND T.Dominant_Colour = ?"; array_push($params, $cbr_color); $types .= "s"; }

$stmt = $conn->prepare($query);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page">
    <div class="page-wrapper">
        <div class="container-fluid p-0 d-flex min-vh-100">
            
            <aside class="sidebar-premium col-md-3 col-lg-2 p-4 d-flex flex-column gap-4">
                <div>
                    <h3 class="text-primary fw-bold mb-1 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-youtube" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M2 8a4 4 0 0 1 4 -4h12a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-12a4 4 0 0 1 -4 -4v-8z" /><path d="M10 9l5 3l-5 3z" /></svg>
                        VidFolio
                    </h3>
                    <span class="text-muted small font-monospace tracking-wider">SYSTEM CONTEXT v1.2</span>
                </div>

                <form method="GET" action="index.php" id="filterForm">
                    <input type="hidden" name="tbr_keyword" value="<?php echo htmlspecialchars($tbr_keyword, ENT_QUOTES); ?>">

                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold tracking-wide small text-uppercase mb-2">Attribute-Based (ABR)</label>
                        
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Max File Size: <span class="text-white fw-bold"><?php echo $abr_max_size; ?> MB</span></label>
                            <input type="range" name="abr_max_size" class="form-range" min="50" max="1000" step="50" value="<?php echo $abr_max_size; ?>" onchange="this.form.submit()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-secondary">Resolution</label>
                            <select name="abr_resolution" class="form-select form-dark" onchange="this.form.submit()">
                                <option value="Any" <?php if($abr_resolution == 'Any') echo 'selected'; ?>>Any</option>
                                <option value="720p" <?php if($abr_resolution == '720p') echo 'selected'; ?>>720p</option>
                                <option value="1080p" <?php if($abr_resolution == '1080p') echo 'selected'; ?>>1080p</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-secondary">Student Gender</label>
                            <select name="abr_gender" class="form-select form-dark" onchange="this.form.submit()">
                                <option value="Any" <?php if($abr_gender == 'Any') echo 'selected'; ?>>Any</option>
                                <option value="Male" <?php if($abr_gender == 'Male') echo 'selected'; ?>>Male</option>
                                <option value="Female" <?php if($abr_gender == 'Female') echo 'selected'; ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold tracking-wide small text-uppercase mb-2">Content-Based (CBR)</label>
                        <input type="hidden" name="cbr_color" id="cbr_color_input" value="<?php echo $cbr_color; ?>">
                        
                        <div class="d-flex flex-wrap gap-1 align-items-center mt-2">
                            <span class="color-selector bg-secondary d-flex align-items-center justify-content-center small text-white <?php if($cbr_color == 'All') echo 'active'; ?>" style="font-size:10px;" onclick="selectColor('All')">All</span>
                            <span class="color-selector <?php if($cbr_color == 'Blue') echo 'active'; ?>" style="background-color: #206bc4;" onclick="selectColor('Blue')"></span>
                            <span class="color-selector <?php if($cbr_color == 'Yellow') echo 'active'; ?>" style="background-color: #f59f00;" onclick="selectColor('Yellow')"></span>
                            <span class="color-selector <?php if($cbr_color == 'Green') echo 'active'; ?>" style="background-color: #2fb344;" onclick="selectColor('Green')"></span>
                        </div>
                    </div>

                    <a href="index.php" class="btn btn-outline-dark btn-sm w-100 text-white border-secondary mt-2">Reset Filter Stack</a>
                </form>
            </aside>

            <main class="col p-5 overflow-auto">
                <form method="GET" action="index.php" class="mb-5">
                    <input type="hidden" name="abr_resolution" value="<?php echo $abr_resolution; ?>">
                    <input type="hidden" name="abr_gender" value="<?php echo $abr_gender; ?>">
                    <input type="hidden" name="abr_max_size" value="<?php echo $abr_max_size; ?>">
                    <input type="hidden" name="cbr_color" value="<?php echo $cbr_color; ?>">

                    <div class="input-icon input-group-lg shadow-sm">
                        <input type="text" name="tbr_keyword" class="form-control form-dark ps-5" placeholder="Search by title, description, keywords, or matric number..." value="<?php echo htmlspecialchars($tbr_keyword, ENT_QUOTES); ?>">
                        <span class="input-icon-addon text-muted ps-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1 fw-bold">Video Library</h2>
                        <p class="text-secondary small mb-0">Browse student portfolio videos inside the repository.</p>
                    </div>
                    <button class="btn btn-primary btn-md fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        Upload Video
                    </button>
                </div>

                <div class="row row-cards g-4">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="col-sm-6 col-md-4 col-xl-3">
                                <div class="card card-portfolio shadow-sm h-100" style="cursor: pointer;" 
                                     onclick="playVideo('<?php echo htmlspecialchars($row['Video_Path'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['Title'], ENT_QUOTES); ?>')">
                                    
                                    <div class="thumb-container" style="border-bottom: 1px solid #23293d; background-color: #0f111a;">
                                        <?php if (!empty($row['Thumbnail_Path']) && file_exists($row['Thumbnail_Path'])): ?>
                                            <img src="<?php echo $row['Thumbnail_Path']; ?>" alt="Video Thumbnail" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="text-white opacity-25" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4v16a1 1 0 0 0 1.524 .852l11 -8a1 1 0 0 0 0 -1.704l-11 -8a1 1 0 0 0 -1.524 .852z" /></svg>
                                        <?php endif; ?>
                                        <span class="badge bg-dark bg-opacity-70 text-white border border-secondary position-absolute top-0 start-0 m-3"><?php echo $row['Resolution']; ?></span>
                                        <span class="badge bg-black text-white position-absolute bottom-0 end-0 m-3 font-monospace" style="font-size:11px;"><?php echo gmdate("i:s", $row['Duration_Seconds']); ?></span>
                                    </div>

                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="mb-3">
                                            <h4 class="text-white fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($row['Title']); ?></h4>
                                            <p class="text-secondary small text-truncate mb-0"><?php echo htmlspecialchars($row['Description']); ?></p>
                                        </div>
                                        <div class="pt-3" style="border-top: 1px solid #23293d; font-size:11px; color:#94a3b8;">
                                            <div class="mb-1 text-white-50"><strong>Author:</strong> <?php echo htmlspecialchars($row['Name']); ?></div>
                                            <div class="mb-2"><strong>Matric ID:</strong> <code><?php echo $row['StudentID']; ?></code></div>
                                            <div><span class="badge bg-dark text-muted p-2 rounded border border-secondary">● CBR: <?php echo $row['Dominant_Colour']; ?></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 border border-dashed rounded bg-dark bg-opacity-50">
                            <p class="text-muted mb-0">No portfolios match your filter parameters matrix.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</div>

<div class="modal fade" id="playerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary text-white rounded-3 shadow-lg">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title fw-bold" id="playerVideoTitle">Streaming Portfolio Asset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopVideo()"></button>
            </div>
            <div class="modal-body p-0 bg-black d-flex justify-content-center align-items-center" style="min-height: 400px;">
                <video id="portfolioVideoPlayer" controls width="100%" class="rounded-bottom">
                    <source id="videoSource" src="" type="video/mp4">
                    Your current browser workspace environment fails to execute native HTML5 video layers.
                </video>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary p-2 rounded-3 text-white">
            <div class="modal-header border-bottom border-secondary pb-2">
                <h5 class="modal-title fw-bold">📤 Ingestion Pipeline Gateway</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="process_video.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold">Target Matric Number / ID</label>
                        <input type="text" name="student_id" class="form-control form-dark" placeholder="e.g. B032410200" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold">Video Presentation Title</label>
                        <input type="text" name="raw_title" class="form-control form-dark" placeholder="Enter video title..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold">Select File (MP4, MOV, WEBM)</label>
                        <input type="file" name="video_file" class="form-control form-dark" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold mt-2 py-2">Process Asset Intake</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectColor(color) {
    document.getElementById('cbr_color_input').value = color;
    document.getElementById('filterForm').submit();
}

// Controller logic to dynamically load and play the video track asset
// Upgraded Controller: Swaps and loads multimedia video streams instantly
function playVideo(videoPath, videoTitle) {
    const playerModal = new bootstrap.Modal(document.getElementById('playerModal'));
    const videoElement = document.getElementById('portfolioVideoPlayer');
    const titleElement = document.getElementById('playerVideoTitle');

    titleElement.textContent = "🎬 Playing: " + videoTitle;
    
    // THE FIX: Apply the video source path string directly to the main video selector object
    videoElement.src = videoPath;
    
    videoElement.load();
    playerModal.show();
    
    // Automatically attempt execution play stream
    videoElement.play().catch(function(error) {
        console.log("Browser playback focus rules require user interaction baseline:", error);
    });
}

// Ensure the binary video soundtrack stops instantly if the user clicks close
function stopVideo() {
    const videoElement = document.getElementById('portfolioVideoPlayer');
    videoElement.pause();
    videoElement.src = "";
    videoElement.load();
}

// Extra listener protection layer if they click the background overlay mask to dismiss
document.getElementById('playerModal').addEventListener('hidden.bs.modal', function () {
    stopVideo();
});
</script>

<?php include 'includes/footer.php'; ?>