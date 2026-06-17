<?php
include 'config/db.php';
include 'core/ThumbnailEngine.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
    
    if (empty($student_id) || !isset($_FILES['video_file'])) {
        die("<h2>❌ Stream Error</h2><p>Data stream context incomplete.</p><a href='index.php'>Return</a>");
    }

    $rawFilename = $_FILES['video_file']['name'];
    // Prevent name collisions or duplication overlaps using random increments
$uniqueToken = uniqid();
$fileExtension = pathinfo($rawFilename, PATHINFO_EXTENSION);
$renamedFilename = $student_id . "_" . $uniqueToken . "." . $fileExtension;

// THE AUTOMATION: Generate a title that captures structural keywords or assigns indexing
$generatedTitle = ThumbnailEngine::generateContentTitle($student_id, $rawFilename);
    $description = "Automated retrieval matching dataset processed via dynamic content-based visual frame pipelines.";

    // Prevent file name overwriting using unique token append keys
    $uniqueToken = uniqid();
    $fileExtension = pathinfo($rawFilename, PATHINFO_EXTENSION);
    $renamedFilename = $student_id . "_" . $uniqueToken . "." . $fileExtension;
    
    $targetVideoPath = "uploads/videos/" . $renamedFilename;

    if (!file_exists('uploads/videos')) { 
        mkdir('uploads/videos', 0777, true); 
    }

    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetVideoPath)) {
        
        // 2. CHOOSE THE SUITABLE THUMBNAIL: Pulls the correct unique image matching the content context
        $targetThumbnail = ThumbnailEngine::extractBestFrame($generatedTitle);
        $detectedColor   = ThumbnailEngine::analyzeDominantColor($targetThumbnail);

        $file_size = round($_FILES['video_file']['size'] / (1024 * 1024), 2);
        $duration  = rand(200, 500); // Simulated duration metrics
        $resolution = '1080p';
        $lengthCategory = 'Medium';

        $videoStmt = $conn->prepare("INSERT INTO VIDEO (StudentID, Raw_Filename, Title, Description, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $videoStmt->bind_param("sssssdiis", $student_id, $rawFilename, $generatedTitle, $description, $targetVideoPath, $file_size, $duration, $lengthCategory, $resolution);

        if ($videoStmt->execute()) {
            $newVideoID = $conn->insert_id;
            
            $thumbStmt = $conn->prepare("INSERT INTO THUMBNAIL (VideoID, Thumbnail_Path, Dominant_Colour) VALUES (?, ?, ?)");
            $thumbStmt->bind_param("iss", $newVideoID, $targetThumbnail, $detectedColor);
            $thumbStmt->execute();
            
            header("Location: index.php");
            exit();
        }
    }
}
?>