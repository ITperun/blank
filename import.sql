CREATE TABLE IF NOT EXISTS kitsune_guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    message TEXT
);
INSERT INTO kitsune_guests (name, message) VALUES ('Kumi', 'Hello from Docker!');