CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE apartments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    apartment_id INT NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('reserved', 'canceled') DEFAULT 'reserved',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE
);


CREATE TABLE boarding_houses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    details TEXT NOT NULL
);


ALTER TABLE reservations CHANGE user_id id INT NOT NULL;
ALTER TABLE reservations DROP FOREIGN KEY fk_user_id;  
ALTER TABLE reservations ADD CONSTRAINT fk_user_id FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE;

-- Step 1: Rename the user_id column to id in the reservations table
ALTER TABLE reservations CHANGE user_id id INT NOT NULL;

-- Step 2: Drop the existing foreign key constraint (if necessary)
ALTER TABLE reservations DROP FOREIGN KEY fk_user_id;  -- Replace with the actual constraint name if applicable

-- Step 3: Add the new foreign key constraint, linking reservations.id to users.ID
ALTER TABLE reservations ADD CONSTRAINT fk_user_id FOREIGN KEY (id) REFERENCES users(ID) ON DELETE CASCADE;
