-- rev/11-20-2024
CREATE TABLE MediaEntities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    release_date DATE,
    api_id VARCHAR(255), -- Null for manual data
    is_api_data BOOLEAN DEFAULT FALSE, -- True for API data
    user_id INT, -- Foreign key to Users table for manual entries
    CONSTRAINT UC_MediaEntity UNIQUE (api_id) -- Prevent duplicate API entries
);
