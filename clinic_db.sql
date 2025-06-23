-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 02:34 PM
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
-- Database: `clinic_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admintbl`
--

CREATE TABLE `admintbl` (
  `AdminID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admintbl`
--

INSERT INTO `admintbl` (`AdminID`, `FirstName`, `LastName`, `Email`, `Password`, `CreatedAt`) VALUES
(1, 'Admin', 'User', 'admin@example.com', '$2y$10$Neu/giACdCgvCbGqWeNYmuP4SUexI62ErRuu6/UQtds24oORlMkkS', '2025-06-15 12:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `appointmenttbl`
--

CREATE TABLE `appointmenttbl` (
  `AppointmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `AssistantID` int(11) NOT NULL,
  `RoomNumber` varchar(50) DEFAULT NULL,
  `AppointmentSchedule` datetime NOT NULL,
  `Status` enum('Pending','Completed','OnGoing','Cancelled') DEFAULT 'Pending',
  `PaymentMethod` enum('Cash','Online') DEFAULT NULL,
  `ReasonForAppointment` text DEFAULT NULL,
  `Prescription` text DEFAULT NULL,
  `Quantity` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointmenttbl`
--

INSERT INTO `appointmenttbl` (`AppointmentID`, `PatientID`, `AssistantID`, `RoomNumber`, `AppointmentSchedule`, `Status`, `PaymentMethod`, `ReasonForAppointment`, `Prescription`, `Quantity`, `CreatedAt`) VALUES
(1, 1, 1, 'Room 101', '2025-07-15 10:00:00', 'Pending', 'Online', 'Routine Check-up', NULL, NULL, '2025-06-15 12:06:10'),
(2, 2, 2, 'Room 203', '2025-07-16 14:30:00', 'OnGoing', 'Cash', 'Child Vaccination', NULL, NULL, '2025-06-15 12:06:10'),
(3, 1, 2, 'Room 203', '2025-07-10 11:00:00', 'Completed', 'Online', 'Flu Symptoms', 'Amoxicillin', '250mg, 3 times a day for 7 days', '2025-06-15 12:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `assistantscheduletbl`
--

CREATE TABLE `assistantscheduletbl` (
  `ScheduleID` int(11) NOT NULL,
  `AssistantID` int(11) NOT NULL,
  `DayOfWeek` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `IsAvailable` tinyint(1) NOT NULL DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assistantscheduletbl`
--

INSERT INTO `assistantscheduletbl` (`ScheduleID`, `AssistantID`, `DayOfWeek`, `StartTime`, `EndTime`, `IsAvailable`, `CreatedAt`) VALUES
(1, 1, 'Monday', '09:00:00', '12:00:00', 1, '2025-06-15 12:06:10'),
(2, 1, 'Monday', '13:00:00', '17:00:00', 1, '2025-06-15 12:06:10'),
(3, 1, 'Wednesday', '09:00:00', '12:00:00', 1, '2025-06-15 12:06:10'),
(4, 1, 'Wednesday', '13:00:00', '17:00:00', 1, '2025-06-15 12:06:10'),
(5, 1, 'Friday', '09:00:00', '16:00:00', 1, '2025-06-15 12:06:10'),
(6, 2, 'Tuesday', '10:00:00', '13:00:00', 1, '2025-06-15 12:06:10'),
(7, 2, 'Thursday', '10:00:00', '13:00:00', 1, '2025-06-15 12:06:10'),
(8, 2, 'Thursday', '14:00:00', '18:00:00', 1, '2025-06-15 12:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `assistanttbl`
--

CREATE TABLE `assistanttbl` (
  `AssistantID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Specialization` varchar(100) DEFAULT NULL,
  `SessionFee` decimal(10,2) DEFAULT 0.00,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assistanttbl`
--

INSERT INTO `assistanttbl` (`AssistantID`, `FirstName`, `LastName`, `Specialization`, `SessionFee`, `Email`, `Password`, `CreatedAt`) VALUES
(1, 'Dr. Alice', 'Johnson', 'General Practitioner', 800.00, 'alice.johnson@example.com', '$2y$10$Neu/giACdCgvCbGqWeNYmuP4SUexI62ErRuu6/UQtds24oORlMkkS', '2025-06-15 12:06:10'),
(2, 'Dr. Bob', 'Williams', 'Pediatrician', 1000.00, 'bob.williams@example.com', '$2y$10$Neu/giACdCgvCbGqWeNYmuP4SUexI62ErRuu6/UQtds24oORlMkkS', '2025-06-15 12:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `patienttbl`
--

CREATE TABLE `patienttbl` (
  `PatientID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Age` int(3) DEFAULT NULL,
  `Gender` enum('Male','Female','Other') DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `MedicalHistory` text DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patienttbl`
--

INSERT INTO `patienttbl` (`PatientID`, `FirstName`, `LastName`, `Age`, `Gender`, `Address`, `ContactNumber`, `MedicalHistory`, `Email`, `Password`, `CreatedAt`) VALUES
(1, 'John', 'Doe', 30, 'Male', '123 Main St, Anytown', '123-456-7890', 'Mild asthma, seasonal allergies', 'john.doe@example.com', '$2y$10$Neu/giACdCgvCbGqWeNYmuP4SUexI62ErRuu6/UQtds24oORlMkkS', '2025-06-15 12:06:10'),
(2, 'Jane', 'Smith', 25, 'Female', '456 Oak Ave, Somewhere', '098-765-4321', 'No significant medical history', 'jane.smith@example.com', '$2y$10$Neu/giACdCgvCbGqWeNYmuP4SUexI62ErRuu6/UQtds24oORlMkkS', '2025-06-15 12:06:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admintbl`
--
ALTER TABLE `admintbl`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `appointmenttbl`
--
ALTER TABLE `appointmenttbl`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `AssistantID` (`AssistantID`);

--
-- Indexes for table `assistantscheduletbl`
--
ALTER TABLE `assistantscheduletbl`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `AssistantID` (`AssistantID`);

--
-- Indexes for table `assistanttbl`
--
ALTER TABLE `assistanttbl`
  ADD PRIMARY KEY (`AssistantID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `patienttbl`
--
ALTER TABLE `patienttbl`
  ADD PRIMARY KEY (`PatientID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admintbl`
--
ALTER TABLE `admintbl`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointmenttbl`
--
ALTER TABLE `appointmenttbl`
  MODIFY `AppointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assistantscheduletbl`
--
ALTER TABLE `assistantscheduletbl`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `assistanttbl`
--
ALTER TABLE `assistanttbl`
  MODIFY `AssistantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patienttbl`
--
ALTER TABLE `patienttbl`
  MODIFY `PatientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointmenttbl`
--
ALTER TABLE `appointmenttbl`
  ADD CONSTRAINT `appointmenttbl_ibfk_1` FOREIGN KEY (`PatientID`) REFERENCES `patienttbl` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointmenttbl_ibfk_2` FOREIGN KEY (`AssistantID`) REFERENCES `assistanttbl` (`AssistantID`) ON DELETE CASCADE;

--
-- Constraints for table `assistantscheduletbl`
--
ALTER TABLE `assistantscheduletbl`
  ADD CONSTRAINT `assistantscheduletbl_ibfk_1` FOREIGN KEY (`AssistantID`) REFERENCES `assistanttbl` (`AssistantID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
