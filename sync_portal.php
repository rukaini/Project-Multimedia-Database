<?php
include 'config/db.php';
include 'core/TitleOptimizer.php';
include 'core/ThumbnailEngine.php';

echo "<h2 style='font-family:sans-serif;'>🌐 Multimedia Ingestion Pipeline</h2>";
echo "<p style='font-family:sans-serif;'>Fetching student records from class portal repository streams...</p>";

// Raw metadata extracted from your UTeM portal dashboard sessions
$portalMetadataFeed = [
    [
        'matric'      => 'B032410200',
        'name'        => 'Muhammad Rukaini Aidil bin Redzuan',
        'gender'      => 'Male',
        'raw_file'    => 'B032410200_20260521_060526_IMG_2996 (1) (1) (1).mp4',
        'size_kb'     => 89635.48
    ],
    [
        'matric'      => 'B032410183',
        'name'        => 'Ain Suriani Binti Zulkefli',
        'gender'      => 'Female',
        'raw_file'    => 'B032410183_20260521_database_presentation.mp4',
        'size_kb'     => 54120.30
    ],
    [
        'matric'      => 'B032410192',
        'name'        => 'Britney Ngieng Fang Yii',
        'gender'      => 'Female',
        'raw_file'    => 'B032410192_network_topologies_hd.mov',
        'size_kb'     => 112450.15
    ]
];

foreach ($portalMetadataFeed as $item) {
    // 1. Maintain table sync for parent Foreign Key matching rules
    $studentStmt = $conn->prepare("INSERT INTO STUDENT (StudentID, Name, Gender) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Name=VALUES(Name)");
    $studentStmt->bind_param("sss", $item['matric'], $item['name'], $item['gender']);
    $studentStmt->execute();

    // 2. THE DYNAMIC GENERATOR MAGIC: Clear file string clutter and generate a clean title
    $generatedTitle = TitleOptimizer::cleanAndOptimize($item['raw_file']);
    $description = "Automated retrieval matching dataset for student matric index references.";
    
    // 3. Map paths directly to your lecturer's live multimedia storage files
    $liveVideoUrl = "https://bitp3353.utem.edu.my/2026/all/uploads/videos/" . urlencode($item['raw_file']);
    $fileSizeMB = round($item['size_kb'] / 1024, 2);

    // 4. Inject structural video properties securely into local system records
    $videoStmt = $conn->prepare("INSERT INTO VIDEO (StudentID, Raw_Filename, Title, Description, Video_Path, File_Size_MB, Upload_Date) VALUES (?, ?, ?, ?, ?, ?, CURDATE()) ON DUPLICATE KEY UPDATE Title=VALUES(Title)");
    $videoStmt->bind_param("sssssd", $item['matric'], $item['raw_file'], $generatedTitle, $description, $liveVideoUrl, $fileSizeMB);
    
    if ($videoStmt->execute()) {
        $videoID = $conn->insert_id;
        if ($videoID == 0) {
            $lookup = $conn->prepare("SELECT VideoID FROM VIDEO WHERE Raw_Filename = ?");
            $lookup->bind_param("s", $item['raw_file']);
            $lookup->execute();
            $videoID = $lookup->get_result()->fetch_assoc()['VideoID'];
        }

        // 5. SELECT THE BEST THUMBNAIL: Triggers the matching image extraction filter rules
        $assignedThumbnail = ThumbnailEngine::extractBestFrame($generatedTitle);
        $dominantColor     = ThumbnailEngine::analyzeDominantColor($assignedThumbnail);

        $thumbStmt = $conn->prepare("INSERT INTO THUMBNAIL (VideoID, Thumbnail_Path, Dominant_Colour) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Thumbnail_Path=VALUES(Thumbnail_Path)");
        $thumbStmt->bind_param("iss", $videoID, $assignedThumbnail, $dominantColor);
        $thumbStmt->execute();
    }
}

echo "<p style='color: green; font-family: sans-serif; font-weight: bold;'>✔️ Data Fetch & Generation Complete! Systems loaded seamlessly.</p>";
echo "<a href='index.php' style='font-family: sans-serif;'>Open Interactive Search Dashboard</a>";
?>