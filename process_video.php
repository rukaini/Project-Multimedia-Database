<?php
include 'config/db.php';
include 'core/ThumbnailEngine.php';
include 'core/TitleOptimizer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Safety size trigger guard for massive files
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        die("<h2>❌ Upload Interrupted</h2><p>The asset size exceeds the server context boundaries. Max upload ceiling is configured at 500M.</p><a href='index.php'>Return</a>");
    }

    $student_id  = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
    $raw_title   = isset($_POST['raw_title']) ? trim($_POST['raw_title']) : '';
    $description = "Student presentation review path archive file.";

    if (empty($student_id) || empty($raw_title) || !isset($_FILES['video_file'])) {
        die("<h2>❌ Validation Error</h2><p>Required fields or video file data streams are empty.</p><a href='index.php'>Return</a>");
    }

    // 1. Run string optimizer architecture standardizer
    $optimizedTitle = TitleOptimizer::cleanAndOptimize($raw_title);

    // 2. Generate clean, corporate-style unique file name strings
    $timestamp = date("Ymd_His");
    $fileExtension = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
    $renamedFilename = $student_id . "_MDB_" . $timestamp . "." . $fileExtension;
    
    $targetVideoDir  = "uploads/videos/";
    $targetVideoPath = $targetVideoDir . $renamedFilename;

    if (!file_exists($targetVideoDir)) { 
        mkdir($targetVideoDir, 0777, true); 
    }

    // 3. THE MAGIC: Save the REAL video file binary onto your hard drive partition
    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetVideoPath)) {
        
        // 4. AUTOMATIC EXTRACTION: Tell the engine to cut a real frame out of the video file
        $targetThumbnail = ThumbnailEngine::extractBestFrame($targetVideoPath);
        $detectedColor   = ThumbnailEngine::analyzeDominantColor($targetThumbnail);

        // Attribute matrix specifications
        $file_size  = round($_FILES['video_file']['size'] / (1024 * 1024), 2); // Actual MB size
        $duration   = rand(180, 420); // Placeholder duration until metadata readers are integrated
        $resolution = '1080p';
        $lengthCategory = ($duration > 300) ? 'Medium' : 'Short';

        // 5. Inject parameters cleanly into MySQL rows via prepared statements
        $videoStmt = $conn->prepare("INSERT INTO VIDEO (StudentID, Title, Description, Renamed_File_Name, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $videoStmt->bind_param("sssssdiis", $student_id, $optimizedTitle, $description, $renamedFilename, $targetVideoPath, $file_size, $duration, $lengthCategory, $resolution);

        if ($videoStmt->execute()) {
            $newVideoID = $conn->insert_id;
            
            $thumbStmt = $conn->prepare("INSERT INTO THUMBNAIL (VideoID, Thumbnail_Path, Is_Generated, Dominant_Colour, Frame_Captured_At) VALUES (?, ?, 1, ?, '2s')");
            $thumbStmt->bind_param("iss", $newVideoID, $targetThumbnail, $detectedColor);
            $thumbStmt->execute();
            
            header("Location: index.php");
            exit();
        } else {
            echo "❌ Database pipeline ingestion failed: " . $conn->error;
        }
    } else {
        echo "❌ Hard drive storage access failure. Unable to move binary chunk to uploads directory.";
    }
}
?>