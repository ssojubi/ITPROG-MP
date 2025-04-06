-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Mar 18, 2025 at 10:20 AM
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
-- Database: `dbmoviesystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `cinemas`
--

CREATE TABLE `cinemas` (
  `cinema_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cinemas`
--

INSERT INTO `cinemas` (`cinema_id`, `name`, `description`, `image`) VALUES
(1, 'Premiere Club', 'Premium movie experience with luxurious seating and sound. Made for a much more private and intimate cinema viewing experience.', 'tempur.jpg'),
(2, 'Directors Club', 'Experience world-class cinema. Dive deeper into the world of cinematic visuals.', 'director.jpg'),
(3, 'IMAX', 'Enjoy cutting-edge technology, surround sound environment and comfort at IMAX Cinemas.', 'imax.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration` varchar(150) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `rating` varchar(10) NOT NULL,
  `description` text NOT NULL,
  `poster` varchar(255) NOT NULL,
  `trailer_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`movie_id`, `title`, `duration`, `genre`, `rating`, `description`, `poster`, `trailer_link`) VALUES
(1, 'Ballerina', '1 hour 45 mins', 'Action/Thriller', 'SPG', 'An assassin trained in the traditions of the Ruska Roma organization sets out to seek revenge after her fathers death.', 'ballerina.jpg', 'https://www.youtube.com/embed/0FSwsrFpkbw?si=5_21o5qaKl59_-qh'),
(2, 'Captain America : Brave New World', '2 hour 30 mins', 'Adventure/Superhero/Action/Thriller/Sci-Fi', 'PG', 'Sam Wilson, the new Captain America, finds himself in the middle of an international incident and must discover the motive behind a nefarious global plan.', 'cap-poster.png', 'https://www.youtube.com/embed/1pHDWnXmK7Y?si=3baic73lyHYRr0C9'),
(3, 'Superman', '2 hour 15 mins', 'Superhero/Action/Adventure/Sci-Fi', 'PG', 'Follows the titular superhero as he reconciles his heritage with his human upbringing. He is the embodiment of truth, justice and the human way in a world that views this as old-fashioned.', 'super-poster.jpg', 'https://www.youtube.com/embed/uhUht6vAsMY?si=MNOUbtecIujYhMSo'),
(4, 'How to Train Your Dragon', '1 hour 45 mins', 'Teen Fantasy/Action/Adventure/Fantasy', 'PG', 'As an ancient threat endangers both Vikings and dragons alike on the isle of Berk, the friendship between Hiccup, an inventive Viking, and Toothless, a Night Fury dragon, becomes the key to both species forging a new future together.', 'httyd-poster.jpg', 'https://www.youtube.com/embed/22w7z_lT6YM?si=lpYWZhlTJG0JJVWI'),
(5, 'Alien: Romulus', '2 hour 45 mins', 'Monster Horror/Space Sci-fi/Horror/Thriller', 'R', 'While scavenging the deep ends of a derelict space station, a group of young space colonists come face to face with the most terrifying life form in the universe.', 'red-poster.jpg', 'https://www.youtube.com/embed/OzY2r2JXsDM?si=f4w2Nwqmz50oz6HD'),
(6, 'Thunderbolts*', '2 hour 30 mins', 'Superhero/Action/Adventure/Crime/Sci-fi', 'PG', 'After finding themselves ensnared in a death trap, an unconventional team of antiheroes must embark on a dangerous mission that will force them to confront the darkest corners of their pasts.', 'thunder-poster.jpeg', 'https://www.youtube.com/embed/bqnRzjPfb5A?si=jklfYJQIACDrKfrE'),
(7, 'A Complete Unknown', '1 hour 50 mins', 'Docudrama/Period Drama/Biography/Music', 'R', 'In 1961, an unknown 19-year-old Bob Dylan arrives in New York City with his guitar and forges relationships with musical icons on his meteoric rise, culminating in a groundbreaking performance that reverberates around the world.', 'acu-poster.jpg', 'https://www.youtube.com/embed/FdV-Cs5o8mc?si=_08rUmAnjEhIamy9'),
(8, 'Hit Man', '1 hour 55 mins', 'Docudrama/Romantic Comedy/Crime/Romance', 'R', 'A professor moonlighting as a hit man of sorts for his city police department, descends into dangerous, dubious territory when he finds himself attracted to a woman who enlists his services.', 'hit-poster.jpg', 'https://www.youtube.com/embed/DXwa8DKIK7g?si=PShVb8sohhskWNd_'),
(9, 'Jojo Rabbit', '1 hour 48 mins', 'Coming-of-Age/Dark Comedy/Drama/War', 'PG-13', 'A young German boy in the Hitler Youth, whose hero and imaginary friend is the countrys dictator, is shocked to discover that his mother is hiding a Jewish girl in their home.', 'jojo.jpg', 'https://www.youtube.com/embed/tL4McUzXfFI?si=b3LQSHTzl9TrP613'),
(10, 'The Monkey', '1 hour 39 mins', 'Dark Comedy/Splatter Horror/Horror', 'R', 'When twin brothers Bill and Hal find their fathers old monkey toy in the attic, a series of gruesome deaths start. The siblings decide to throw the toy away and move on with their lives, growing apart over the years.', 'monk-poster.jpg', 'https://www.youtube.com/embed/husMGbXEIho?si=heiRRGOfKUcmMJ7e');

-- --------------------------------------------------------

--
-- Table structure for table `showtimes`
--

CREATE TABLE `showtimes` (
  `showtime_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `cinema_id` int(11) NOT NULL,
  `time` time NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `showtimes`
--

INSERT INTO `showtimes` (`showtime_id`, `movie_id`, `cinema_id`, `time`, `price`) VALUES
(1, 1, 1, '04:30:00', 250.00),
(2, 1, 2, '06:00:00', 500.00),
(3, 1, 3, '07:30:00', 350.00),
(4, 2, 1, '08:00:00', 250.00),
(5, 5, 2, '12:00:00', 500.00),
(6, 4, 3, '03:00:00', 350.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cinemas`
--
ALTER TABLE `cinemas`
  ADD PRIMARY KEY (`cinema_id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD PRIMARY KEY (`showtime_id`),
  ADD KEY `fk_showtimes_movie_id` (`movie_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cinemas`
--
ALTER TABLE `cinemas`
  MODIFY `cinema_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `showtimes`
--
ALTER TABLE `showtimes`
  MODIFY `showtime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `showtimes`
--
ALTER TABLE `showtimes`
  ADD CONSTRAINT `fk_showtimes_movie_id` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE accounts (
    email_address VARCHAR(45) PRIMARY KEY NOT NULL,
    account_name VARCHAR(45) NOT NULL,
    birth_date DATE NOT NULL,
    contact_number int(11),
    account_password VARCHAR(45) NOT NULL
);

-- Create the seats table
CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    cinema_id INT NOT NULL,
    seat_row CHAR(1) NOT NULL,
    seat_number INT NOT NULL,
    status ENUM('available', 'booked') DEFAULT 'available',
    FOREIGN KEY (cinema_id) REFERENCES cinemas(cinema_id),
    UNIQUE KEY unique_seat (cinema_id, seat_row, seat_number)
);

-- Create a booking_seats junction table to record which seats are in which bookings
CREATE TABLE booking_seats (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(50) NOT NULL,
    customer_email VARCHAR(45) NOT NULL,
    movie_id INT NOT NULL,
    showtime_id INT NOT NULL,
    seat_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    booking_timestamp DATETIME NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (seat_id) REFERENCES seats(seat_id),
    FOREIGN KEY (showtime_id) REFERENCES showtimes(showtime_id),
    FOREIGN KEY (customer_email) REFERENCES accounts(email_address),
    UNIQUE KEY unique_seat_showtime (seat_id, showtime_id, customer_email)

    -- ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create procedures to generate seats for each cinema type
DELIMITER //

-- Procedure to populate seats for Premiere Club (40 seats: 8 rows of 5 seats)
CREATE PROCEDURE PopulatePremiereClubSeats(IN cinema_id_param INT)
BEGIN
    DECLARE row_counter CHAR(1);
    DECLARE seat_counter INT;
    DECLARE row_ascii INT;
    
    SET row_ascii = ASCII('A');
    
    WHILE row_ascii <= ASCII('H') DO
        SET row_counter = CHAR(row_ascii);
        SET seat_counter = 1;
        
        WHILE seat_counter <= 5 DO
            INSERT INTO seats (cinema_id, seat_row, seat_number, status)
            VALUES (cinema_id_param, row_counter, seat_counter, 'available');
            
            SET seat_counter = seat_counter + 1;
        END WHILE;
        
        SET row_ascii = row_ascii + 1;
    END WHILE;
END //

-- Procedure to populate seats for Directors Club (20 seats: 4 rows of 5 seats)
CREATE PROCEDURE PopulateDirectorsClubSeats(IN cinema_id_param INT)
BEGIN
    DECLARE row_counter CHAR(1);
    DECLARE seat_counter INT;
    DECLARE row_ascii INT;
    
    SET row_ascii = ASCII('A');
    
    WHILE row_ascii <= ASCII('D') DO
        SET row_counter = CHAR(row_ascii);
        SET seat_counter = 1;
        
        WHILE seat_counter <= 5 DO
            INSERT INTO seats (cinema_id, seat_row, seat_number, status)
            VALUES (cinema_id_param, row_counter, seat_counter, 'available');
            
            SET seat_counter = seat_counter + 1;
        END WHILE;
        
        SET row_ascii = row_ascii + 1;
    END WHILE;
END //

-- Procedure to populate seats for IMAX (80 seats: 10 rows of 8 seats)
CREATE PROCEDURE PopulateIMAXSeats(IN cinema_id_param INT)
BEGIN
    DECLARE row_counter CHAR(1);
    DECLARE seat_counter INT;
    DECLARE row_ascii INT;
    
    SET row_ascii = ASCII('A');
    
    WHILE row_ascii <= ASCII('J') DO
        SET row_counter = CHAR(row_ascii);
        SET seat_counter = 1;
        
        WHILE seat_counter <= 8 DO
            INSERT INTO seats (cinema_id, seat_row, seat_number, status)
            VALUES (cinema_id_param, row_counter, seat_counter, 'available');
            
            SET seat_counter = seat_counter + 1;
        END WHILE;
        
        SET row_ascii = row_ascii + 1;
    END WHILE;
END //

-- Procedure to populate all cinemas based on their type
CREATE PROCEDURE PopulateAllCinemaSeats()
BEGIN
    DECLARE finished INT DEFAULT 0;
    DECLARE cinema_id_var INT;
    DECLARE cinema_name_var VARCHAR(255);
    
    -- Declare cursor to iterate through cinemas
    DECLARE cinema_cursor CURSOR FOR 
        SELECT cinema_id, name FROM cinemas;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;
    
    OPEN cinema_cursor;
    
    cinema_loop: LOOP
        FETCH cinema_cursor INTO cinema_id_var, cinema_name_var;
        
        IF finished = 1 THEN 
            LEAVE cinema_loop;
        END IF;
        
        -- Based on cinema name, call appropriate procedure
        IF cinema_name_var = 'Premiere Club' THEN
            CALL PopulatePremiereClubSeats(cinema_id_var);
        ELSEIF cinema_name_var = 'Directors Club' THEN
            CALL PopulateDirectorsClubSeats(cinema_id_var);
        ELSEIF cinema_name_var = 'IMAX' THEN
            CALL PopulateIMAXSeats(cinema_id_var);
        END IF;
        
    END LOOP;
    
    CLOSE cinema_cursor;
END //

DELIMITER ;

-- Execute the stored procedure to populate all cinema seats
CALL PopulateAllCinemaSeats();

-- Create a view to easily see available seats for a specific showtime
CREATE VIEW available_seats_view AS
SELECT 
    s.seat_id,
    s.cinema_id,
    c.name AS cinema_name,
    s.seat_row,
    s.seat_number,
    s.status,
    CONCAT(s.seat_row, s.seat_number) AS seat_label
FROM
    seats s
JOIN
    cinemas c ON s.cinema_id = c.cinema_id
WHERE
    s.status = 'available';

-- Create a stored procedure to check seat availability for a specific showtime
DELIMITER //

CREATE PROCEDURE GetAvailableSeatsForShowtime(IN showtime_id_param INT)
BEGIN
    SELECT 
        s.seat_id,
        s.cinema_id,
        c.name AS cinema_name,
        s.seat_row,
        s.seat_number,
        CONCAT(s.seat_row, s.seat_number) AS seat_label
    FROM
        seats s
    JOIN
        cinemas c ON s.cinema_id = c.cinema_id
    JOIN
        showtimes sh ON sh.cinema_id = s.cinema_id
    LEFT JOIN
        booking_seats bs ON bs.seat_id = s.seat_id AND bs.showtime_id = showtime_id_param
    WHERE
        sh.showtime_id = showtime_id_param
        AND bs.booking_id IS NULL
        AND s.status = 'available'
    ORDER BY
        s.seat_row, s.seat_number;
END //

DELIMITER ;

