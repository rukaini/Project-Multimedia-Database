<?php
include 'config/db.php';
include 'core/ThumbnailEngine.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
    // Capture the descriptive input field from the frontend form
    $user_title = isset($_POST['video_title']) ? trim($_POST['video_title']) : '';
    
    if (empty($student_id) || !isset($_FILES['video_file'])) {
        die("<h2>❌ Stream Error</h2><p>Data stream context incomplete.</p><a href='index.php'>Return</a>");
    }

    $rawFilename = $_FILES['video_file']['name'];
    
    // 1. CONTEXTUAL CAPTURE: Generate a title matching exactly what the student inputted or uploaded
    $generatedTitle = ThumbnailEngine::generateContentTitle($user_title, $rawFilename);
    $description = "Student presentation assignment record tracking log entry.";

    // Prevent file name overwriting by injecting microsecond unique token append keys
    $uniqueToken = uniqid();
    $fileExtension = pathinfo($rawFilename, PATHINFO_EXTENSION);
    $renamedFilename = $student_id . "_" . $uniqueToken . "." . $fileExtension;
    
    $targetVideoPath = "uploads/videos/" . $renamedFilename;

    if (!file_exists('uploads/videos')) { 
        mkdir('uploads/videos', 0777, true); 
    }

    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetVideoPath)) {
        
        // 2. DYNAMIC MAPPING: Link the unique character layout image precisely to the clean title row
        $targetThumbnail = ThumbnailEngine::extractBestFrame($generatedTitle);
        $detectedColor   = ThumbnailEngine::analyzeDominantColor($targetThumbnail);

        // Metadata matrix specifications
        $file_size = round($_FILES['video_file']['size'] / (1024 * 1024), 2);
        $duration  = rand(240, 560); // Dynamic length simulation metric logic
        $resolution = '1080p';
        $lengthCategory = ($duration > 300) ? 'Medium' : 'Short';

        // Prepare and execute parameter insertion safely via prepared statements
        $videoStmt = $conn->prepare("INSERT INTO VIDEO (StudentID, Raw_Filename, Title, Description, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $videoStmt->bind_param("sssssdiis", $student_id, $rawFilename, $generatedTitle, $description, $targetVideoPath, $file_size, $duration, $lengthCategory, $resolution);

        if ($videoStmt->execute()) {
            $newVideoID = $conn->insert_id;
            
            $thumbStmt = $conn->prepare("INSERT INTO THUMBNAIL (VideoID, Thumbnail_Path, Dominant_Colour) VALUES (?, ?, ?)");
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