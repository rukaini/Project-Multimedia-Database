-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 09:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mdproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `StudentID` varchar(15) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Gender` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`StudentID`, `Name`, `Gender`) VALUES
('B032410001', 'Abdul Malik Bin Mustapha', 'Male'),
('B032410002', 'Muhammad Haikal Bin Mahadzir', 'Male'),
('B032410185', 'Muhammad Aidil Amani Bin Abdul Rahman', 'Male'),
('B032410186', 'Adam Bin Azmi', 'Male'),
('B032410200', 'Muhammad Rukaini Aidil bin Redzuan', 'Male'),
('B032410999', 'Amina Yusuf', 'Female');

-- --------------------------------------------------------

--
-- Table structure for table `thumbnail`
--

CREATE TABLE `thumbnail` (
  `ThumbnailID` int(11) NOT NULL,
  `VideoID` int(11) DEFAULT NULL,
  `Thumbnail_Path` varchar(255) NOT NULL,
  `Is_Generated` tinyint(1) DEFAULT 1,
  `Dominant_Colour` varchar(20) NOT NULL,
  `Frame_Captured_At` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thumbnail`
--

INSERT INTO `thumbnail` (`ThumbnailID`, `VideoID`, `Thumbnail_Path`, `Is_Generated`, `Dominant_Colour`, `Frame_Captured_At`) VALUES
(11, 12, 'uploads/thumbnails/thumb1.jpg.png', 1, 'Green', '2s'),
(12, 13, 'uploads/thumbnails/thumb4.jpg.png', 1, 'Yellow', '2s'),
(13, 14, 'uploads/thumbnails/thumb4.jpg.png', 1, 'Yellow', '2s'),
(14, 15, 'uploads/thumbnails/thumb4.jpg.png', 1, 'Green', '2s'),
(15, 16, 'uploads/thumbnails/thumb2.jpg.png', 1, 'Yellow', '2s'),
(16, 17, 'uploads/thumbnails/thumb4.jpg.png', 1, 'Yellow', '2s'),
(17, 18, 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=500', 1, 'Yellow', '2s'),
(18, 19, 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=500', 1, 'Green', '2s');

-- --------------------------------------------------------

--
-- Table structure for table `video`
--

CREATE TABLE `video` (
  `VideoID` int(11) NOT NULL,
  `StudentID` varchar(15) DEFAULT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Renamed_File_Name` varchar(255) NOT NULL,
  `Video_Path` varchar(255) NOT NULL,
  `File_Size_MB` decimal(10,2) NOT NULL,
  `Duration_Seconds` int(11) NOT NULL,
  `Length_Category` varchar(10) NOT NULL,
  `Resolution` varchar(10) NOT NULL,
  `Upload_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `video`
--

INSERT INTO `video` (`VideoID`, `StudentID`, `Title`, `Description`, `Renamed_File_Name`, `Video_Path`, `File_Size_MB`, `Duration_Seconds`, `Length_Category`, `Resolution`, `Upload_Date`) VALUES
(12, 'B032410200', 'Test', 'Student presentation review path archive file.', 'B032410200_MDB_20260614_183903.mp4', 'uploads/videos/B032410200_MDB_20260614_183903.mp4', 31.22, 412, '0', '1080p', '2026-06-15'),
(13, 'B032410200', 'Test', 'Student presentation review path archive file.', 'B032410200_MDB_20260614_183923.mp4', 'uploads/videos/B032410200_MDB_20260614_183923.mp4', 19.51, 347, '0', '1080p', '2026-06-15'),
(14, 'B032410200', 'Test', 'Student presentation review path archive file.', 'B032410200_MDB_20260614_184010.mp4', 'uploads/videos/B032410200_MDB_20260614_184010.mp4', 19.51, 336, '0', '1080p', '2026-06-15'),
(15, 'B032410200', 'Test', 'Student presentation review path archive file.', 'B032410200_MDB_20260614_185231.mp4', 'uploads/videos/B032410200_MDB_20260614_185231.mp4', 3.14, 322, '0', '1080p', '2026-06-15'),
(16, 'B032410200', 'Test', 'Student presentation review path archive file.', 'B032410200_MDB_20260614_185248.mp4', 'uploads/videos/B032410200_MDB_20260614_185248.mp4', 5.20, 377, '0', '1080p', '2026-06-15'),
(17, 'B032410200', 'Test', 'Student presentation review path archive file.', 'B032410200_MDB_20260614_185308.mkv', 'uploads/videos/B032410200_MDB_20260614_185308.mkv', 4.30, 206, '0', '1080p', '2026-06-15'),
(18, 'B032410200', 'Database Normalization Explained', 'A deep dive into 1NF, 2NF, and 3NF database structures.', 'B032410200_20260521_060526_IMG_2996 (1) (1) (1).mp4', 'https://bitp3353.utem.edu.my/2026/all/uploads/videos/B032410200_20260521_060526_IMG_2996+%281%29+%281%29+%281%29.mp4', 89.63, 642, '0', '1080p', '2026-06-17'),
(19, 'B032410185', 'Network Topologies Deep Dive', 'Comprehensive look into Star, Mesh, and Hybrid computer networking.', 'B032410185_network_presentation.mp4', 'https://bitp3353.utem.edu.my/2026/all/uploads/videos/B032410185_network_presentation.mp4', 74.20, 1264, '0', '1080p', '2026-06-17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`StudentID`);

--
-- Indexes for table `thumbnail`
--
ALTER TABLE `thumbnail`
  ADD PRIMARY KEY (`ThumbnailID`),
  ADD KEY `VideoID` (`VideoID`);

--
-- Indexes for table `video`
--
ALTER TABLE `video`
  ADD PRIMARY KEY (`VideoID`),
  ADD KEY `StudentID` (`StudentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `thumbnail`
--
ALTER TABLE `thumbnail`
  MODIFY `ThumbnailID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `video`
--
ALTER TABLE `video`
  MODIFY `VideoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `thumbnail`
--
ALTER TABLE `thumbnail`
  ADD CONSTRAINT `thumbnail_ibfk_1` FOREIGN KEY (`VideoID`) REFERENCES `video` (`VideoID`) ON DELETE CASCADE;

--
-- Constraints for table `video`
--
ALTER TABLE `video`
  ADD CONSTRAINT `video_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
