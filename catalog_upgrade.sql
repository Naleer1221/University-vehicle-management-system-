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

