CREATE TABLE tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_UUID VARCHAR(255) NOT NULL,
    token_name VARCHAR(55) NOT NULL,
    token_value TEXT NOT NULL,
    token_type VARCHAR(50) NOT NULL,
    expires_in INT NOT NULL,
    expiry_time DATETIME NOT NULL,
    is_token_expired BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_type_uuid UNIQUE (token_UUID)
);

CREATE TABLE stk_callback_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    merchant_request_id VARCHAR(255) NOT NULL,
    checkout_request_id VARCHAR(255) NOT NULL,
    result_code INT NOT NULL,
    result_desc VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) DEFAULT NULL,
    mpesa_receipt_number VARCHAR(50) DEFAULT NULL,
    balance VARCHAR(50) DEFAULT NULL,
    transaction_date DATETIME DEFAULT NULL,
    phone_number VARCHAR(15) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE stk_callback_cancelled_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    merchant_request_id VARCHAR(255) NOT NULL,
    checkout_request_id VARCHAR(255) NOT NULL,
    result_code INT NOT NULL,
    result_desc VARCHAR(255) NOT NULL,
    canceled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(15) NOT NULL,
    amount VARCHAR(50) NOT NULL,
    invoice_number VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    country_UUID CHAR(36) NOT NULL,
    country_name VARCHAR(255) NOT NULL,
    country_short_code VARCHAR(10) NOT NULL,
    country_call_code VARCHAR(55) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_country_uuid UNIQUE (country_UUID)
);

CREATE TABLE house_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(255) NOT NULL,
    type_UUID CHAR(36) NOT NULL,
    type_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_type_uuid UNIQUE (type_UUID)
);

CREATE TABLE quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_UUID CHAR(36) NOT NULL,
    full_names VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    country_id INT NOT NULL,
    province VARCHAR(255) DEFAULT NULL,
    city VARCHAR(255) NOT NULL,
    town VARCHAR(255) DEFAULT NULL,
    county VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    id_number VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    age INT NOT NULL,
    house_type_id INT NOT NULL,
    no_of_beds INT NOT NULL,
    no_of_baths INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_country FOREIGN KEY (country_id) REFERENCES countries(id),
    CONSTRAINT fk_house_type FOREIGN KEY (house_type_id) REFERENCES house_types(id),
    CONSTRAINT unique_type_uuid UNIQUE (quotation_UUID)
);

CREATE TABLE genders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gender_identity_UUID CHAR(36) NOT NULL,
    gender_identity VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_type_uuid UNIQUE (gender_identity_UUID)
);

CREATE TABLE relationship_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    relationship_status_UUID CHAR(36) NOT NULL,
    relationship_status VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_type_uuid UNIQUE (relationship_status_UUID)
);

CREATE TABLE payment_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_UUID CHAR(36) NOT NULL,
    plan_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_type_uuid UNIQUE (plan_UUID)
);

CREATE TABLE mortgage_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_UUID CHAR(36) NOT NULL,
    plan_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT unique_type_uuid UNIQUE (plan_UUID)
);

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_UUID CHAR(36) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    surname VARCHAR(100),
    id_number VARCHAR(55) NOT NULL,
    kra_pin VARCHAR(55) NOT NULL,
    country_id INT NOT NULL,
    province VARCHAR(255) DEFAULT NULL,
    city VARCHAR(255),
    town VARCHAR(255) DEFAULT NULL,
    occupation VARCHAR(255),
    date_of_birth DATE NOT NULL,
    age INT NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    organization_name VARCHAR(255),
    no_of_beds INT,
    no_of_baths INT,
    phone_number VARCHAR(20),
    pymnt_pln_id INT NOT NULL,
    mtge_pln_id INT NOT NULL,
    gender_id INT NOT NULL,
    rlshp_sts_id INT NOT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    zip_code VARCHAR(20) DEFAULT NULL,
    promotional_emails BOOLEAN DEFAULT 0,
    exclusive_emails BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cnty FOREIGN KEY (country_id) REFERENCES countries(id),
    CONSTRAINT fk_pymnt_pl FOREIGN KEY (pymnt_pln_id) REFERENCES payment_plans(id),
    CONSTRAINT fk_mrtge_pl FOREIGN KEY (mtge_pln_id) REFERENCES mortgage_plans(id),
    CONSTRAINT fk_gndr FOREIGN KEY (gender_id) REFERENCES genders(id),
    CONSTRAINT fk_rlshp_sts FOREIGN KEY (rlshp_sts_id) REFERENCES relationship_statuses(id),
    CONSTRAINT unique_type_uuid UNIQUE (registration_UUID)
);



