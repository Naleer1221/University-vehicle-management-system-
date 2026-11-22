-- DriveEasy Vehicle Booking System (XAMPP / MySQL / MariaDB)
-- Import once into a fresh local database using phpMyAdmin.
-- Passwords use PHP password_hash; see README.md for demonstration credentials.
CREATE DATABASE IF NOT EXISTS vehicle_booking_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vehicle_booking_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL DEFAULT '',
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  license_no VARCHAR(60) NOT NULL DEFAULT '',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  brand VARCHAR(120) NOT NULL,
  model VARCHAR(120) NOT NULL,
  year SMALLINT UNSIGNED NOT NULL,
  registration_no VARCHAR(50) NOT NULL UNIQUE,
  seats TINYINT UNSIGNED NOT NULL,
  transmission ENUM('Automatic','Manual') NOT NULL,
  fuel_type ENUM('Petrol','Diesel','Electric','Hybrid') NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  status ENUM('available','maintenance','unavailable') NOT NULL DEFAULT 'available',
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  description TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicle_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_price CHECK (price_per_day > 0),
  CONSTRAINT chk_seats CHECK (seats > 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  pickup_date DATE NOT NULL,
  return_date DATE NOT NULL,
  total_days INT NOT NULL,
  total_price DECIMAL(12,2) NOT NULL,
  status ENUM('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  notes VARCHAR(1000) NOT NULL DEFAULT '',
  cancellation_reason VARCHAR(1000) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_booking_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_booking_dates CHECK (return_date >= pickup_date),
  CONSTRAINT chk_booking_days CHECK (total_days >= 1),
  CONSTRAINT chk_booking_price CHECK (total_price >= 0),
  INDEX idx_vehicle_dates (vehicle_id, pickup_date, return_date, status),
  INDEX idx_customer_bookings (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Refund requests (approval does NOT send money; admin performs manual transfer).
CREATE TABLE IF NOT EXISTS refund_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL UNIQUE,
  user_id INT NOT NULL,
  reason VARCHAR(1000) NOT NULL,
  requested_amount DECIMAL(12,2) NOT NULL,
  approved_amount DECIMAL(12,2) DEFAULT NULL,
  status ENUM('pending','approved','rejected','refunded') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(1000) NOT NULL DEFAULT '',
  refund_reference VARCHAR(120) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at DATETIME DEFAULT NULL,
  refunded_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_refund_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_refund_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_refund_requested CHECK (requested_amount >= 0),
  CONSTRAINT chk_refund_approved CHECK (approved_amount IS NULL OR approved_amount >= 0),
  INDEX idx_refund_status (status, created_at)
) ENGINE=InnoDB;

-- Sample accounts (1 admin + 5 customers); all demo users have their own row.
INSERT IGNORE INTO users (id,full_name,email,phone,password_hash,role,license_no) VALUES
(1,'System Administrator','admin@vehicle.test','0770000000','$2y$12$aMENBkUpIIZXcq7rWXBjZ.WJl3dHynXSqHSnUZT5ztqtJ0s0i9a8y','admin',''),
(2,'Nimal Perera','customer1@vehicle.test','0771111111','$2y$12$J7iAoRol2T1QQRBrb5hyy.vrFUZOzfT.87ak6w/5gyY7UQH7yORe6','customer','B1234567'),
(3,'Kavindi Silva','customer2@vehicle.test','0772222222','$2y$12$J7iAoRol2T1QQRBrb5hyy.vrFUZOzfT.87ak6w/5gyY7UQH7yORe6','customer','B2345678'),
(4,'Arun Kumar','customer3@vehicle.test','0773333333','$2y$12$J7iAoRol2T1QQRBrb5hyy.vrFUZOzfT.87ak6w/5gyY7UQH7yORe6','customer','B3456789'),
(5,'Fathima Rahman','customer4@vehicle.test','0774444444','$2y$12$J7iAoRol2T1QQRBrb5hyy.vrFUZOzfT.87ak6w/5gyY7UQH7yORe6','customer','B4567890'),
(6,'Sahan Fernando','customer5@vehicle.test','0775555555','$2y$12$J7iAoRol2T1QQRBrb5hyy.vrFUZOzfT.87ak6w/5gyY7UQH7yORe6','customer','B5678901');

INSERT IGNORE INTO categories (id,name) VALUES
(1,'Car'),(2,'SUV'),(3,'Van'),(4,'Motorbike'),(5,'Pickup'),(6,'Luxury');

-- Images start as NULL: vehicles use a local SVG illustration until admin uploads photos.
INSERT IGNORE INTO vehicles (id,category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,status,description) VALUES
(1,1,'Toyota Aqua','Toyota','Aqua',2020,'ABC-1001',5,'Automatic','Hybrid',8500,'available','Compact hybrid car suitable for city trips.'),
(2,1,'Honda Fit','Honda','Fit',2019,'ABC-1002',5,'Automatic','Petrol',9000,'available','Reliable hatchback with easy parking.'),
(3,2,'Toyota RAV4','Toyota','RAV4',2021,'ABC-1003',5,'Automatic','Hybrid',16000,'available','Spacious SUV for longer journeys.'),
(4,3,'Toyota Hiace','Toyota','Hiace',2018,'ABC-1004',12,'Manual','Diesel',22000,'available','Comfortable van for group travel.'),
(5,4,'Honda Dio','Honda','Dio',2022,'ABC-1005',2,'Automatic','Petrol',3000,'available','Small and fuel-efficient scooter.'),
(6,5,'Mitsubishi L200','Mitsubishi','L200',2017,'ABC-1006',5,'Manual','Diesel',18000,'available','Pickup truck with extra luggage space.'),
(7,6,'BMW 320i','BMW','320i',2020,'ABC-1007',5,'Automatic','Petrol',35000,'available','Premium sedan for special occasions.'),
(8,1,'Suzuki Alto','Suzuki','Alto',2021,'ABC-1008',4,'Manual','Petrol',6500,'available','Budget-friendly small car.'),
(9,2,'Nissan X-Trail','Nissan','X-Trail',2019,'ABC-1009',7,'Automatic','Petrol',15500,'available','Seven-seater SUV.'),
(10,3,'Nissan Caravan','Nissan','Caravan',2018,'ABC-1010',10,'Manual','Diesel',20000,'available','Van for family or group trips.'),
(11,4,'Yamaha FZ','Yamaha','FZ',2020,'ABC-1011',2,'Manual','Petrol',4200,'maintenance','Temporarily unavailable for servicing.'),
(12,6,'Mercedes C200','Mercedes','C200',2022,'ABC-1012',5,'Automatic','Petrol',42000,'unavailable','Unavailable in the demonstration database.');

-- Dates depend on the day you import this file. Relative dates keep the demonstration usable.
-- Each insert below: user_id, vehicle_id, pickup, return, total_days, total_price, status.
INSERT IGNORE INTO bookings (id,user_id,vehicle_id,pickup_date,return_date,total_days,total_price,status,notes) VALUES
(1,2,1,DATE_ADD(CURDATE(), INTERVAL 3 DAY),DATE_ADD(CURDATE(), INTERVAL 5 DAY),3,25500,'pending','Airport pickup'),
(2,3,2,DATE_ADD(CURDATE(), INTERVAL 4 DAY),DATE_ADD(CURDATE(), INTERVAL 5 DAY),2,18000,'approved','Family visit'),
(3,4,3,DATE_SUB(CURDATE(), INTERVAL 12 DAY),DATE_SUB(CURDATE(), INTERVAL 10 DAY),3,48000,'completed','Trip completed'),
(4,5,4,DATE_ADD(CURDATE(), INTERVAL 8 DAY),DATE_ADD(CURDATE(), INTERVAL 10 DAY),3,66000,'pending','Group trip'),
(5,6,5,DATE_ADD(CURDATE(), INTERVAL 9 DAY),DATE_ADD(CURDATE(), INTERVAL 11 DAY),3,9000,'approved','Short rental'),
(6,2,6,DATE_ADD(CURDATE(), INTERVAL 12 DAY),DATE_ADD(CURDATE(), INTERVAL 13 DAY),2,36000,'cancelled','Cancelled demonstration'),
(7,3,7,DATE_ADD(CURDATE(), INTERVAL 16 DAY),DATE_ADD(CURDATE(), INTERVAL 17 DAY),2,70000,'rejected','Rejected demonstration'),
(8,4,8,DATE_SUB(CURDATE(), INTERVAL 7 DAY),DATE_SUB(CURDATE(), INTERVAL 6 DAY),2,13000,'completed','Previous trip'),
(9,5,9,DATE_ADD(CURDATE(), INTERVAL 20 DAY),DATE_ADD(CURDATE(), INTERVAL 22 DAY),3,46500,'pending','Weekend trip'),
(10,6,10,DATE_ADD(CURDATE(), INTERVAL 24 DAY),DATE_ADD(CURDATE(), INTERVAL 26 DAY),3,60000,'approved','Family vacation');


-- DriveEasy catalogue and local demo image upgrade.
-- Safe for existing databases: keeps customers, bookings and uploaded images.
-- Run this once after copying the updated website and image files.
-- INSERT IGNORE makes a second import harmless for demo registrations.
USE vehicle_booking_db;
INSERT IGNORE INTO categories (name) VALUES ('Three-wheeler'), ('Scooter');

UPDATE vehicles SET image='demo-white-crossover.webp' WHERE registration_no='ABC-1001' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-suv-forest.webp' WHERE registration_no='ABC-1002' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-suv-twilight.webp' WHERE registration_no='ABC-1003' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-van-white.webp' WHERE registration_no='ABC-1004' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-motorbike-scenic.webp' WHERE registration_no='ABC-1005' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-suv-mountain.webp' WHERE registration_no='ABC-1006' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-suv-forest.webp' WHERE registration_no='ABC-1007' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-white-crossover.webp' WHERE registration_no='ABC-1008' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-suv-mountain.webp' WHERE registration_no='ABC-1009' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-van-twilight.webp' WHERE registration_no='ABC-1010' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-sportbike-blue.webp' WHERE registration_no='ABC-1011' AND (image IS NULL OR image='');
UPDATE vehicles SET image='demo-white-crossover.webp' WHERE registration_no='ABC-1012' AND (image IS NULL OR image='');

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Bajaj RE Auto','Bajaj','RE',2022,'DEMO-2001',3,'Manual','Petrol',3500,'demo-auto-street.webp','available','Three-wheeler for short city journeys.' FROM categories WHERE name='Three-wheeler';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'TVS King Deluxe','TVS','King',2021,'DEMO-2002',3,'Manual','Petrol',3700,'demo-auto-yellow.webp','available','Compact three-wheel taxi-style vehicle.' FROM categories WHERE name='Three-wheeler';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Piaggio Ape Auto','Piaggio','Ape',2020,'DEMO-2003',3,'Manual','Petrol',3300,'demo-auto-twilight.webp','available','Small three-wheeler for local travel.' FROM categories WHERE name='Three-wheeler';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Yamaha R15 V4','Yamaha','R15 V4',2023,'DEMO-2004',2,'Manual','Petrol',5500,'demo-sportbike-blue.webp','available','Sporty motorbike with comfortable handling.' FROM categories WHERE name='Motorbike';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Honda CB Hornet','Honda','CB Hornet',2022,'DEMO-2005',2,'Manual','Petrol',4400,'demo-motorbike-scenic.webp','available','Practical road motorcycle for city and countryside.' FROM categories WHERE name='Motorbike';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Bajaj Pulsar 150','Bajaj','Pulsar 150',2021,'DEMO-2006',2,'Manual','Petrol',4100,'demo-motorbike-twilight.webp','available','Dependable manual motorbike for short rides.' FROM categories WHERE name='Motorbike';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Honda Activa','Honda','Activa',2022,'DEMO-2007',2,'Automatic','Petrol',3200,'demo-motorbike-scenic.webp','available','Easy-to-ride automatic scooter for errands.' FROM categories WHERE name='Scooter';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'TVS Ntorq 125','TVS','Ntorq 125',2023,'DEMO-2008',2,'Automatic','Petrol',3500,'demo-sportbike-blue.webp','available','Automatic city scooter for daily trips.' FROM categories WHERE name='Scooter';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Toyota Prius','Toyota','Prius',2019,'DEMO-2009',5,'Automatic','Hybrid',11000,'demo-white-crossover.webp','available','Efficient hybrid car for comfortable trips.' FROM categories WHERE name='Car';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Toyota Fortuner','Toyota','Fortuner',2022,'DEMO-2010',7,'Automatic','Diesel',28000,'demo-suv-mountain.webp','available','Large seven-seat SUV for family trips.' FROM categories WHERE name='SUV';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Kia Picanto','Kia','Picanto',2020,'DEMO-2011',5,'Automatic','Petrol',8700,'demo-suv-forest.webp','available','Compact car suited to urban travel.' FROM categories WHERE name='Car';

INSERT IGNORE INTO vehicles (category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description)
SELECT id,'Hyundai H1','Hyundai','H1',2019,'DEMO-2012',9,'Automatic','Diesel',21000,'demo-van-white.webp','available','Comfortable passenger van for groups.' FROM categories WHERE name='Van';

