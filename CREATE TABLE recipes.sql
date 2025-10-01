CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,   -- Optional: link recipe to a user
    name VARCHAR(150) NOT NULL,
    description VARCHAR(250) NOT NULL,
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    image VARCHAR(255) NULL,  -- to save image filename/path
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

ALTER TABLE recipes ADD uploaded_by INT NOT NULL;