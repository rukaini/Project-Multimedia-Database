<?php
include 'config/db.php';
include 'core/ThumbnailEngine.php';
include 'core/TitleOptimizer.php';

// Check if the form was actually submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. CAPTURE ACTUAL USER INPUTS DYNAMICALLY
    $student_id  = trim($_POST['student_id']);
    $raw_title   = trim($_POST['raw_title']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : 'No description provided.';
    
    // Default fallback values for attributes (Haikal & Adam can expand these later with automatic extraction)
    $file_size   = rand(45, 180); // Simulates MB size dynamically for testing
    $duration    = rand(180, 720); // Simulates length in seconds
    $resolution  = '1080p';

    // 2. RUN THE TITLE OPTIMIZER LAYER
    $optimizedTitle = TitleOptimizer::cleanAndOptimize($raw_title);

    // 3. SECURE SYSTEM RENAME AND FILE UPLOAD TRACKING
    $timestamp = date("Ymd_His");
    $renamedFilename = $student_id . "_MDB_" . $timestamp . ".mp4";
    $targetVideoPath = "uploads/videos/" . $renamedFilename;

    // Simulate saving the video binary asset to disk
    if (!file_exists('uploads/videos')) { mkdir('uploads/videos', 0777, true); }
    touch($targetVideoPath); 

    // 4. GENERATE DYNAMIC PORTFOLIO IMAGES
    if (!file_exists('uploads/thumbnails')) { mkdir('uploads/thumbnails', 0777, true); }
    $targetThumbnail = "uploads/thumbnails/" . $student_id . "_MDB_" . $timestamp . "_thumb.jpg";
    
    // Choose a color theme matching your CBR specification layer
    $detectedColor = ThumbnailEngine::analyzeDominantColor($targetThumbnail);

    // 5. INGEST DATA ROW SECURELY INTO SYSTEM DATABASE TABLES
    $lengthCategory = ($duration > 600) ? 'Long' : (($duration > 300) ? 'Medium' : 'Short');

    $videoStmt = $conn->prepare("INSERT INTO VIDEO (StudentID, Title, Description, Renamed_File_Name, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
    $videoStmt->bind_param("sssssdiis", $student_id, $optimizedTitle, $description, $renamedFilename, $targetVideoPath, $file_size, $duration, $lengthCategory, $resolution);

    if ($videoStmt->execute()) {
        $newVideoID = $conn->insert_id;
        
        // Link thumbnail row straight via FK constraint
        $thumbStmt = $conn->prepare("INSERT INTO THUMBNAIL (VideoID, Thumbnail_Path, Is_Generated, Dominant_Colour, Frame_Captured_At) VALUES (?, ?, 1, ?, '2s')");
        $thumbStmt->bind_param("iss", $newVideoID, $targetThumbnail, $detectedColor);
        $thumbStmt->execute();
        
        // Redirect right back to dashboard automatically upon successful processing
        header("Location: index.php");
        exit();
    } else {
        echo "Database Ingestion Failure: " . $conn->error;
    }
} else {
    echo "Direct access denied. Please utilize the dashboard intake modal form link.";
}
?>