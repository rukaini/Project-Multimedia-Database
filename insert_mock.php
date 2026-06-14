<?php
include 'db.php';

echo "<h2>Populating Mock Data for mdproject...</h2>";

// 1. Insert Sample Students (Your Team)
$students = [
    ['B032410200', 'Muhammad Rukaini Aidil Bin Redzuan', 'Male'],
    ['B032410185', 'Muhammad Aidil Amani Bin Abdul Rahman', 'Male'],
    ['B032410001', 'Abdul Malik Bin Mustapha', 'Male'],
    ['B032410002', 'Muhammad Haikal Bin Mahadzir', 'Male'],
    ['B032410186', 'Adam Bin Azmi', 'Male'],
    ['B032410999', 'Amina Yusuf', 'Female'] // From your UI Upload popup screenshot
];

foreach ($students as $s) {
    $stmt = $conn->prepare("INSERT IGNORE INTO STUDENT (StudentID, Name, Gender) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $s[0], $s[1], $s[2]);
    $stmt->execute();
}
echo "✓ Students inserted successfully.<br>";

// 2. Insert Sample Videos & Thumbnails
// Video 1: Database Normalization
$conn->query("INSERT IGNORE INTO VIDEO (VideoID, StudentID, Title, Description, Renamed_File_Name, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) 
VALUES (1, 'B032410200', 'Database Normalization Explained', 'A deep dive into 1NF, 2NF, and 3NF database structures.', 'B032410200_MDB_20260614_1200.mp4', 'uploads/videos/video1.mp4', 114.00, 642, 'Medium', '1080p', '2026-06-07')");

$conn->query("INSERT IGNORE INTO THUMBNAIL (ThumbnailID, VideoID, Thumbnail_Path, Is_Generated, Dominant_Colour, Frame_Captured_At) 
VALUES (1, 1, 'uploads/thumbnails/thumb1.jpg', 1, 'Blue', '2s')");

// Video 2: Sorting Algorithms
$conn->query("INSERT IGNORE INTO VIDEO (VideoID, StudentID, Title, Description, Renamed_File_Name, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) 
VALUES (2, 'B032410042', 'Sorting Algorithms Visualized', 'Visualizing QuickSort, MergeSort, and BubbleSort efficiency.', 'B032410042_MDB_20260614_1300.mp4', 'uploads/videos/video2.mp4', 96.00, 318, 'Short', '720p', '2026-06-05')");

$conn->query("INSERT IGNORE INTO THUMBNAIL (ThumbnailID, VideoID, Thumbnail_Path, Is_Generated, Dominant_Colour, Frame_Captured_At) 
VALUES (2, 2, 'uploads/thumbnails/thumb2.jpg', 1, 'Yellow', '2s')");

// Video 3: Network Topologies
$conn->query("INSERT IGNORE INTO VIDEO (VideoID, StudentID, Title, Description, Renamed_File_Name, Video_Path, File_Size_MB, Duration_Seconds, Length_Category, Resolution, Upload_Date) 
VALUES (3, 'B032410185', 'Network Topologies Deep Dive', 'Comprehensive look into Star, Mesh, and Hybrid computer networks.', 'B032410185_MDB_20260614_1400.mp4', 'uploads/videos/video3.mp4', 512.00, 1264, 'Long', '1080p', '2026-06-02')");

$conn->query("INSERT IGNORE INTO THUMBNAIL (ThumbnailID, VideoID, Thumbnail_Path, Is_Generated, Dominant_Colour, Frame_Captured_At) 
VALUES (3, 3, 'uploads/thumbnails/thumb3.jpg', 1, 'Green', '10%')");

echo "✓ Core videos and thumbnails injected successfully.<br><br><strong>Ready for search layer testing!</strong>";
?>
